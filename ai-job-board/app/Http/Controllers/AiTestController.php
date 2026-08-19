<?php

namespace App\Http\Controllers;

use App\Support\GeminiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTestController extends Controller
{
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        try {
            $response = GeminiClient::generate($validated['message']);

            if ($response->successful() && GeminiClient::extractText($response)) {
                return response()->json([
                    'success' => true,
                    'message' => GeminiClient::extractText($response),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => GeminiClient::errorMessage($response)
                    ?? 'Gemini request failed (HTTP '.$response->status().').',
            ], 502);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to reach the Gemini API.',
            ], 502);
        }
    }
}