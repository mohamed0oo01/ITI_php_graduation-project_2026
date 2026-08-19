<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Support\GeminiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class JobRecommendationController extends Controller
{
    private const int MAX_JOBS = 50;

    public function index(Request $request): View
    {
        return view('recommendations', [
            'user' => $request->user(),
        ]);
    }

    public function recommend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->hasProfileData($user)) {
            return response()->json([
                'success' => true,
                'jobs' => [],
                'message' => 'Complete your profile (job title, skills and description) to get AI job recommendations.',
            ]);
        }

        $jobs = Job::orderBy('application_deadline')->limit(self::MAX_JOBS)->get();

        if ($jobs->isEmpty()) {
            return response()->json([
                'success' => true,
                'jobs' => [],
                'message' => 'No jobs are available right now. Please check back later.',
            ]);
        }

        $systemPrompt = $this->buildPrompt($user, $jobs);

        try {
            $response = GeminiClient::generate(
                'Recommend the most suitable jobs for this candidate based on their profile.',
                $systemPrompt,
            );

            if (! $response->successful() || ! GeminiClient::extractText($response)) {
                return response()->json([
                    'success' => false,
                    'message' => GeminiClient::errorMessage($response)
                        ?? 'AI recommendations failed (HTTP '.$response->status().').',
                ], 502);
            }

            return $this->parseRecommendations($user, GeminiClient::extractText($response));
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to reach the Gemini API.',
            ], 502);
        }
    }

    private function hasProfileData(User $user): bool
    {
        return filled($user->job_title) || filled($user->skills) || filled($user->profile_description);
    }

    private function buildPrompt(User $user, $jobs): string
    {
        $profile = [
            'Name: '.$user->name,
            'Job title: '.($user->job_title ?: 'Not set'),
            'Skills: '.($user->skills ?: 'Not set'),
            'Profile description: '.($user->profile_description ?: 'Not set'),
        ];

        $jobList = $jobs
            ->map(fn (Job $job) => '#'.$job->id.' - '.$job->title.
                " [{$job->category} | {$job->work_type} | {$job->location}]".
                " required skills: {$job->required_skills}".
                ($job->application_deadline ? ' (closes '.$job->application_deadline->format('Y-m-d').')' : ''))
            ->implode("\n");

        return "You are a job recommendation engine for an online job board. Based on the candidate profile, recommend the most suitable jobs currently available.\n\n"
            ."CANDIDATE PROFILE:\n".implode("\n", $profile)."\n\n"
            ."AVAILABLE JOBS (use these exact ids):\n".$jobList."\n\n"
            ."Respond with ONLY valid JSON (no markdown, no code fences, no extra text) in this exact shape:\n"
            .'{"jobs":[{"id":1,"reason":"one short sentence explaining why this job fits"}]}'."\n\n"
            ."Rules:\n"
            ."- Recommend at most 5 jobs, ranked from best match to least.\n"
            ."- Use ONLY job ids from AVAILABLE JOBS.\n"
            ."- Consider the candidate's skills match, job title and profile description.\n"
            ."- reason must be one short sentence (max ~150 characters).";
    }

    private function parseRecommendations(User $user, string $text): JsonResponse
    {
        $parsed = $this->parseJobsJson($text);

        if ($parsed === null) {
            return response()->json([
                'success' => true,
                'jobs' => [],
                'message' => $text,
            ]);
        }

        $ids = collect($parsed)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $jobsById = Job::whereIn('id', $ids)->get()->keyBy('id');
        $appliedIds = JobApplication::where('user_id', $user->id)
            ->whereIn('job_id', $ids)
            ->pluck('job_id')
            ->all();

        $recommendations = [];

        foreach ($parsed as $item) {
            $job = $jobsById->get((int) ($item['id'] ?? 0));

            if (! $job) {
                continue;
            }

            $recommendations[] = [
                'id' => $job->id,
                'title' => $job->title,
                'category' => $job->category,
                'work_type' => $job->work_type,
                'location' => $job->location,
                'salary' => $job->salary,
                'deadline' => $job->application_deadline?->format('Y-m-d'),
                'reason' => (string) ($item['reason'] ?? ''),
                'applied' => in_array($job->id, $appliedIds, true),
                'url' => route('jobs.show', $job),
            ];
        }

        if (empty($recommendations)) {
            return response()->json([
                'success' => true,
                'jobs' => [],
                'message' => 'No matching jobs were found for your profile right now.',
            ]);
        }

        return response()->json([
            'success' => true,
            'jobs' => $recommendations,
        ]);
    }

    private function parseJobsJson(string $text): ?array
    {
        $text = trim($text);

        if (preg_match('/```(?:json)?\s*(.*?)```/s', $text, $matches)) {
            $text = trim($matches[1]);
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $data = json_decode(substr($text, $start, $end - $start + 1), true);

        if (! is_array($data) || ! is_array(Arr::get($data, 'jobs'))) {
            return null;
        }

        return $data['jobs'];
    }
}
