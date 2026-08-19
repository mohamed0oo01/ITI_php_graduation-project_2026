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

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $lastAttempt = $attempt === 3;

            try {
                $response = Http::withHeaders(['X-goog-api-key' => env('GEMINI_API_KEY')])
                    ->timeout(30)
                    ->post($url, $payload);

                if ($response->successful() || ! in_array($response->status(), [429, 500, 503], true)) {
                    return $response;
                }
            } catch (ConnectionException $e) {
                if ($lastAttempt) {
                    throw $e;
                }
            }

            if (! app()->environment('testing')) {
                $delay = self::retryDelayMs($response, $attempt);

                if ($delay > 15000) {
                    return $response;
                }

                usleep($delay);
            }
        }

        return $response;
    }

    private static function retryDelayMs(?Response $response, int $attempt): int
    {
        $seconds = (int) (intval($response?->header('Retry-After') ?? 0));

        if ($seconds <= 0 && $response !== null) {
            $message = (string) data_get($response->json(), 'error.message', '');

            if (preg_match('/(?:retry in|retry after)\s+([\d.]+)\s*s/i', $message, $matches)) {
                $seconds = (int) ceil((float) $matches[1]);
            }
        }

        return $seconds > 0 ? $seconds * 1000 : (1000 * (2 ** $attempt));
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