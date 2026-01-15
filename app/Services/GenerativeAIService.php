<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerativeAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
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
            Log::warning('OpenAI API Key is missing.');
            return null;
        }

        try {
            // Log::info("OpenAI API Request Prompt: " . $prompt);

            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->baseUrl, [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that outputs JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // Log::info("OpenAI API Success");

                if (isset($data['choices'][0]['message']['content'])) {
                    return $data['choices'][0]['message']['content'];
                }
            }

            Log::warning('OpenAI API Error: ' . $response->status() . ' - ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Generative AI Service Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate an image based on a prompt using DALL-E.
     * Returns the URL of the generated image or null on failure.
     * 
     * @param string $prompt
     * @return string|null
     */
    public function generateImage(string $prompt, string $size = '1024x1024'): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API Key is missing for Image Generation.');
            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60) // Images take longer
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => $size,
                    'quality' => 'standard',
                    'response_format' => 'url',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['url'])) {
                    return $data['data'][0]['url'];
                }
            }

            Log::warning('OpenAI Image API Error: ' . $response->status() . ' - ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Generative AI Image Exception: ' . $e->getMessage());
            return null;
        }
    }
}
