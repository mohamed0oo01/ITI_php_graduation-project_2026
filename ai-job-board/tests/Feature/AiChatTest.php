<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_requires_authentication(): void
    {
        $this->postJson('/api/ai/chat', ['message' => 'hello'])->assertStatus(401);
    }

    public function test_validates_message(): void
    {
        $user = User::factory()->create(['role' => 'candidate']);

        $this->actingAs($user)->postJson('/api/ai/chat', [])->assertStatus(422);
    }

    public function test_candidate_chat_sends_profile_and_jobs_context(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Try the Laravel Developer role.']]]]],
            ], 200),
        ]);

        $candidate = User::factory()->create([
            'role' => 'candidate',
            'job_title' => 'Backend Developer',
            'skills' => 'PHP, Laravel, MySQL',
        ]);
        Job::factory()->create(['title' => 'Laravel Developer', 'required_skills' => 'PHP, Laravel, MySQL']);

        $response = $this->actingAs($candidate)
            ->postJson('/api/ai/chat', ['message' => 'What are the best jobs for me?']);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Try the Laravel Developer role.',
        ]);

        Http::assertSent(function ($request) {
            $system = data_get($request->data(), 'systemInstruction.parts.0.text');
            $contents = data_get($request->data(), 'contents.0.parts.0.text');

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/'.env('GEMINI_MODEL').':generateContent'
                && $request->header('X-goog-api-key') === [env('GEMINI_API_KEY')]
                && str_contains($system, 'CANDIDATE PROFILE')
                && str_contains($system, 'PHP, Laravel, MySQL')
                && str_contains($system, 'Laravel Developer')
                && $contents === 'What are the best jobs for me?';
        });
    }

    public function test_admin_chat_sends_stats_and_jobs_context(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'There are 3 candidates registered.']]]]],
            ], 200),
        ]);

        User::factory()->count(2)->create(['role' => 'candidate']);
        $admin = User::factory()->create(['role' => 'admin']);
        $candidate = User::factory()->create(['role' => 'candidate']);
        $job = Job::factory()->create(['title' => 'Data Analyst', 'category' => 'Programming']);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $response = $this->actingAs($admin)
            ->postJson('/api/ai/chat', ['message' => 'How many candidates are registered?']);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'There are 3 candidates registered.',
        ]);

        Http::assertSent(function ($request) {
            $system = data_get($request->data(), 'systemInstruction.parts.0.text');

            return str_contains($system, 'Registered candidates: 3')
                && str_contains($system, 'Data Analyst')
                && str_contains($system, 'STATS');
        });
    }

    public function test_uses_gemini_key_from_env(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'ok']]]]],
            ], 200),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson('/api/ai/chat', ['message' => 'hi']);

        Http::assertSent(function ($request) {
            return $request->header('X-goog-api-key') === [env('GEMINI_API_KEY')];
        });
    }

    public function test_handles_gemini_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Insufficient quota']], 429),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson('/api/ai/chat', ['message' => 'hi'])
            ->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient quota',
            ]);
    }

    public function test_handles_connection_failure(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => fn () => throw new ConnectionException('Connection refused'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson('/api/ai/chat', ['message' => 'hi'])
            ->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to reach the Gemini API.',
            ]);
    }

    public function test_chat_creates_conversation_titled_by_first_question(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'A reply.']]]]],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'admin']);
        $longQuestion = str_repeat('Which jobs are the best for me? ', 5);

        $response = $this->actingAs($user)
            ->postJson('/api/ai/chat', ['message' => $longQuestion]);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'A reply.',
        ]);

        $conversation = AiConversation::where('user_id', $user->id)->first();
        $this->assertNotNull($conversation);
        $this->assertSame(\Illuminate\Support\Str::limit($longQuestion, 60), $conversation->title);
        $this->assertSame($conversation->id, $response->json('conversation_id'));
        $this->assertSame(2, $conversation->messages()->count());
    }

    public function test_chat_continues_existing_conversation(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Following up.']]]]],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'admin']);
        $conversation = AiConversation::create(['user_id' => $user->id, 'title' => 'First question']);
        $conversation->messages()->create(['role' => 'user', 'content' => 'First question']);
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'First answer']);

        $this->actingAs($user)
            ->postJson('/api/ai/chat', ['message' => 'Second question', 'conversation_id' => $conversation->id])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'conversation_id' => $conversation->id,
            ]);

        $this->assertSame(4, $conversation->messages()->count());
    }

    public function test_lists_only_own_conversations(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);

        AiConversation::create(['user_id' => $user->id, 'title' => 'Mine one']);
        AiConversation::create(['user_id' => $user->id, 'title' => 'Mine two']);
        AiConversation::create(['user_id' => $other->id, 'title' => 'Not mine']);

        $this->actingAs($user)
            ->getJson('/api/ai/conversations')
            ->assertOk()
            ->assertJsonCount(2, 'conversations');
    }

    public function test_shows_conversation_with_messages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $conversation = AiConversation::create(['user_id' => $user->id, 'title' => 'How many candidates?']);
        $conversation->messages()->create(['role' => 'user', 'content' => 'How many candidates?']);
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'There are 3 candidates.']);

        $this->actingAs($user)
            ->getJson("/api/ai/conversations/{$conversation->id}")
            ->assertOk()
            ->assertJsonPath('conversation.title', 'How many candidates?')
            ->assertJsonPath('conversation.messages.0.role', 'user')
            ->assertJsonPath('conversation.messages.1.content', 'There are 3 candidates.');
    }

    public function test_deletes_conversation_and_its_messages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $conversation = AiConversation::create(['user_id' => $user->id, 'title' => 'To delete']);
        $conversation->messages()->create(['role' => 'user', 'content' => 'hi']);
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'hello']);

        $this->actingAs($user)
            ->deleteJson("/api/ai/chat/{$conversation->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('ai_messages', ['conversation_id' => $conversation->id]);
    }

    public function test_cannot_access_another_users_conversation(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $intruder = User::factory()->create(['role' => 'admin']);
        $conversation = AiConversation::create(['user_id' => $owner->id, 'title' => 'Private']);

        $this->actingAs($intruder)
            ->getJson("/api/ai/conversations/{$conversation->id}")
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->deleteJson("/api/ai/chat/{$conversation->id}")
            ->assertStatus(403);
    }
}