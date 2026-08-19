<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Support\GeminiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return response()->json(['conversations' => $conversations]);
    }

    public function show(Request $request, AiConversation $conversation): JsonResponse
    {
        if ($request->user()->id !== $conversation->user_id) {
            return response()->json(['success' => false, 'message' => 'You do not own this conversation.'], 403);
        }

        $conversation->load('messages');

        return response()->json(['conversation' => $conversation]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();

        $conversation = null;
        $history = [];

        if (isset($validated['conversation_id'])) {
            $conversation = AiConversation::where('user_id', $user->id)
                ->find($validated['conversation_id']);

            if (! $conversation) {
                return response()->json(['success' => false, 'message' => 'Conversation not found.'], 403);
            }

            $history = $conversation->messages->map(fn ($message) => [
                'role' => $message->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message->content]],
            ])->all();
        }

        try {
            $response = GeminiClient::generate(
                $validated['message'],
                $this->buildSystemPrompt($user),
                $history,
            );

            if ($response->successful() && GeminiClient::extractText($response)) {
                $text = GeminiClient::extractText($response);

                if (! $conversation) {
                    $conversation = AiConversation::create([
                        'user_id' => $user->id,
                        'title' => Str::limit($validated['message'], 60),
                    ]);
                }

                $conversation->messages()->create(['role' => 'user', 'content' => $validated['message']]);
                $conversation->messages()->create(['role' => 'assistant', 'content' => $text]);

                return response()->json([
                    'success' => true,
                    'message' => $text,
                    'conversation_id' => $conversation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => GeminiClient::errorMessage($response)
                    ?? 'AI assistant request failed (HTTP '.$response->status().').',
            ], 502);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to reach the Gemini API.',
            ], 502);
        }
    }

    public function destroy(Request $request, AiConversation $conversation): JsonResponse
    {
        if ($request->user()->id !== $conversation->user_id) {
            return response()->json(['success' => false, 'message' => 'You do not own this conversation.'], 403);
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json(['success' => true]);
    }

    private function buildSystemPrompt(?User $user): string
    {
        if (! $user) {
            return 'You are the AI assistant for this job board. Keep your answers short and friendly.';
        }

        return $user->role === 'candidate'
            ? $this->candidatePrompt($user)
            : $this->adminPrompt();
    }

    private function candidatePrompt(User $user): string
    {
        $profile = [
            "Name: {$user->name}",
            'Job title: '.($user->job_title ?: 'Not set'),
            'Skills: '.($user->skills ?: 'Not set'),
            'Profile description: '.($user->profile_description ?: 'Not set'),
        ];

        $jobs = Job::orderByDesc('application_deadline')
            ->limit(20)
            ->get()
            ->map(fn (Job $job) => "- {$job->title} [category: {$job->category} | {$job->work_type} | {$job->location}]".
                " required skills: {$job->required_skills} — closes {$job->application_deadline->format('Y-m-d')}")
            ->implode("\n");

        return "You are the AI job assistant for this job board. Respond in short, friendly sentences using ONLY the data below.\n\n"
            ."CANDIDATE PROFILE:\n".implode("\n", $profile)."\n\n"
            ."AVAILABLE JOBS:\n".($jobs ?: '(no jobs available)')."\n\n"
            ."Helpful answers you should give for questions like:\n"
            ."- \"What are the best jobs for me?\" → recommend jobs whose required skills best match the candidate's skills.\n"
            ."- \"Which jobs match my skills?\" → list the jobs that match.\n"
            ."- \"What skills should I learn?\" → skills required by jobs that the candidate does not yet have.\n"
            ."If the data is not enough to answer, say so politely.";
    }

    private function adminPrompt(): string
    {
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalJobs = Job::count();
        $totalApplications = JobApplication::count();
        $mostApplied = Job::withCount('applications')->orderByDesc('applications_count')->first();

        $jobs = Job::withCount('applications')
            ->orderBy('title')
            ->get()
            ->map(fn (Job $job) => "- {$job->title} [category: {$job->category} | {$job->work_type} | {$job->location}]".
                " applications: {$job->applications_count} — closes {$job->application_deadline->format('Y-m-d')}")
            ->implode("\n");

        return "You are the AI admin assistant for this job board. Respond in short, friendly sentences using ONLY the data below.\n\n"
            ."STATS:\n"
            ."- Registered candidates: {$totalCandidates}\n"
            ."- Total jobs: {$totalJobs}\n"
            ."- Total applications: {$totalApplications}\n"
            .'- Job with most applications: '.($mostApplied?->title ?: 'none')."\n\n"
            ."ALL JOBS:\n".($jobs ?: '(no jobs available)')."\n\n"
            ."Answer questions like these accurately:\n"
            ."- \"How many candidates are registered?\"\n"
            ."- \"Which job has the most applications?\"\n"
            ."- \"List all available jobs.\"\n"
            ."- \"Show jobs in the Programming category.\"";
    }
}