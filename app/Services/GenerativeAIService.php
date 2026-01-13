<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerativeAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Generate text content based on a prompt.
     * Returns null if API key is missing or request fails.
     *
     * @param string $prompt
     * @return string|null
     */
    public function generateText(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            return null; // Fallback to templates
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}?key={$this->apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                // Extract text from Gemini response structure
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            Log::warning('Gemini API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Generative AI Service Exception: ' . $e->getMessage());
            return null;
        }
    }
}
