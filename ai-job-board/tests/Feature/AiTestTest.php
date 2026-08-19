<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiTestTest extends TestCase
{
    public function test_validates_message_is_required(): void
    {
        $this->postJson('/api/ai/test', [])->assertStatus(422);

        $this->postJson('/api/ai/test', ['message' => 123])->assertStatus(422);
    }

    public function test_returns_gemini_response(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Hello! Nice to meet you.']]]]],
            ], 200),
        ]);

        $response = $this->postJson('/api/ai/test', ['message' => 'Say hello in one short sentence.']);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Hello! Nice to meet you.',
        ]);
    }

    public function test_sends_message_with_key_from_env_to_gemini(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'ok']]]]],
            ], 200),
        ]);

        $this->postJson('/api/ai/test', ['message' => 'Say hello in one short sentence.']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/'.env('GEMINI_MODEL').':generateContent'
                && $request->header('X-goog-api-key') === [env('GEMINI_API_KEY')]
                && data_get($request->data(), 'contents.0.parts.0.text') === 'Say hello in one short sentence.';
        });
    }

    public function test_gemini_api_key_is_loaded_from_env(): void
    {
        $this->assertNotEmpty(env('GEMINI_API_KEY'));
    }

    public function test_success_response_does_not_expose_api_key(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Hello!']]]]],
            ], 200),
        ]);

        $response = $this->postJson('/api/ai/test', ['message' => 'Say hi.']);

        $this->assertStringNotContainsString(env('GEMINI_API_KEY'), $response->getContent());
    }

    public function test_handles_gemini_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Invalid API key']], 400),
        ]);

        $this->postJson('/api/ai/test', ['message' => 'Say hi.'])
            ->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid API key',
            ]);
    }

    public function test_handles_connection_failure(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => fn () => throw new ConnectionException('Connection refused'),
        ]);

        $this->postJson('/api/ai/test', ['message' => 'Say hi.'])
            ->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to reach the Gemini API.',
            ]);
    }
}