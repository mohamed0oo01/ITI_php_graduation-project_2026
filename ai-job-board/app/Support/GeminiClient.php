<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiClient
{
    public static function generate(string $message, ?string $systemPrompt = null, array $history = [], int $maxOutputTokens = 2048): Response
    {
        $contents = array_merge(
            $history,
            [['role' => 'user', 'parts' => [['text' => $message]]]],
        );

        $payload = [
            'contents' => $contents,
            'generationConfig' => ['maxOutputTokens' => $maxOutputTokens],
        ];

        if ($systemPrompt !== null) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        $url = self::endpoint();
        $response = null;

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $response = Http::withHeaders(['X-goog-api-key' => env('GEMINI_API_KEY')])
                    ->timeout(30)
                    ->post($url, $payload);

                if ($response->successful() || ! in_array($response->status(), [429, 500, 503], true)) {
                    return $response;
                }
            } catch (ConnectionException $e) {
                if ($attempt === 1) {
                    throw $e;
                }
            }

            usleep(1200000);
        }

        return $response;
    }

    public static function extractText(Response $response): ?string
    {
        return data_get($response->json(), 'candidates.0.content.parts.0.text');
    }

    public static function errorMessage(Response $response): ?string
    {
        return data_get($response->json(), 'error.message');
    }

    private static function endpoint(): string
    {
        $model = env('GEMINI_MODEL', 'gemini-3.6-flash');

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }
}