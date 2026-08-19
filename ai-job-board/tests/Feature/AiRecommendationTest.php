<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/ai/recommendations')->assertStatus(401);
        $this->get('/recommendations')->assertRedirect('/login');
    }

    public function test_admins_cannot_access_candidate_recommendations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/recommendations')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->postJson('/api/ai/recommendations')->assertStatus(302);
    }

    public function test_renders_recommendations_page(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'job_title' => 'Backend Developer',
            'skills' => 'PHP, Laravel, MySQL',
        ]);

        $this->actingAs($candidate)
            ->get('/recommendations')
            ->assertOk()
            ->assertSee('AI Job Recommendations')
            ->assertSee('Get AI Recommendations');
    }

    public function test_recommends_jobs_from_database(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"jobs":[{"id":1,"reason":"Direct match for your Laravel skills."},{"id":2,"reason":"React roles suit your background."}]}']]]]],
            ], 200),
        ]);

        $candidate = User::factory()->create([
            'role' => 'candidate',
            'job_title' => 'Backend Developer',
            'skills' => 'PHP, Laravel, MySQL',
        ]);
        $job1 = Job::factory()->create(['title' => 'Senior Laravel Developer']);
        $job2 = Job::factory()->create(['title' => 'Frontend React Developer']);

        $this->actingAs($candidate)
            ->postJson('/api/ai/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'jobs')
            ->assertJsonPath('jobs.0.id', $job1->id)
            ->assertJsonPath('jobs.0.reason', 'Direct match for your Laravel skills.')
            ->assertJsonPath('jobs.1.id', $job2->id)
            ->assertJsonPath('jobs.0.url', route('jobs.show', $job1));

        Http::assertSent(function ($request) {
            $system = data_get($request->data(), 'systemInstruction.parts.0.text');
            $contents = data_get($request->data(), 'contents.0.parts.0.text');

            return str_contains($system, 'CANDIDATE PROFILE')
                && str_contains($system, 'PHP, Laravel, MySQL')
                && str_contains($system, 'Senior Laravel Developer')
                && str_contains($contents, 'Recommend the most suitable jobs');
        });
    }

    public function test_marks_already_applied_jobs(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"jobs":[{"id":1,"reason":"Good match."}]}']]]]],
            ], 200),
        ]);

        $candidate = User::factory()->create(['role' => 'candidate', 'skills' => 'PHP']);
        $job = Job::factory()->create();
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($candidate)
            ->postJson('/api/ai/recommendations')
            ->assertOk()
            ->assertJsonPath('jobs.0.applied', true);
    }

    public function test_falls_back_to_raw_text_when_json_is_invalid(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Try the Senior Laravel Developer role because it matches your PHP skills.']]]]],
            ], 200),
        ]);

        $candidate = User::factory()->create(['role' => 'candidate', 'skills' => 'PHP, Laravel']);
        Job::factory()->create();

        $this->actingAs($candidate)
            ->postJson('/api/ai/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'jobs')
            ->assertJsonPath('message', 'Try the Senior Laravel Developer role because it matches your PHP skills.');
    }

    public function test_prompts_to_complete_profile_when_empty(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'job_title' => null,
            'skills' => null,
            'profile_description' => null,
        ]);

        $this->actingAs($candidate)
            ->postJson('/api/ai/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('jobs', [])
            ->assertJsonStructure(['message']);
    }

    public function test_handles_gemini_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Insufficient quota']], 429),
        ]);

        $candidate = User::factory()->create(['role' => 'candidate', 'skills' => 'PHP']);
        Job::factory()->create();

        $this->actingAs($candidate)
            ->postJson('/api/ai/recommendations')
            ->assertStatus(502)
            ->assertJsonPath('message', 'Insufficient quota');
    }
}