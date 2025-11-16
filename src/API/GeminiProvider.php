<?php

namespace AIImageTagger\API;

use AIImageTagger\Models\AIMetadata;
use AIImageTagger\Exceptions\RateLimitException;

class GeminiProvider extends AbstractProvider {

    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    private const MODEL = 'gemini-2.5-flash';

    protected function getApiKey(): string {
        return $this->settings->getApiKey('gemini');
    }

    public function analyzeImage(string $imagePath): AIMetadata {
        if (!$this->isConfigured()) {
            throw new \Exception('Gemini provider not configured');
        }

        $optimizedPath = $this->optimizeImage($imagePath);
        $request = $this->buildRequest($optimizedPath);

        $url = self::API_URL . '?key=' . $this->apiKey;
        
        $headers = [
            'Content-Type: application/json'
        ];

        try {
            $response = $this->makeRequest($url, $request, $headers);
            $parsed = $this->parseResponse($response);

            return AIMetadata::fromArray($parsed);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'RESOURCE_EXHAUSTED')) {
                throw new RateLimitException(esc_html($e->getMessage()));
            }
            throw $e;
        }
    }

    protected function buildRequest(string $imagePath): array {
        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);
        $basePrompt = $this->settings->get('default_prompt') ?: $this->getDefaultPrompt();
        $languageInstruction = $this->getLanguageInstruction();
        $prompt = $languageInstruction . $basePrompt;

        return [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 2048,
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string'
                        ],
                        'description' => [
                            'type' => 'string'
                        ],
                        'alt_text' => [
                            'type' => 'string'
                        ],
                        'caption' => [
                            'type' => 'string'
                        ],
                        'tags' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string'
                            ]
                        ]
                    ],
                    'required' => ['title', 'description', 'alt_text', 'caption', 'tags']
                ]
            ]
        ];
    }

    protected function parseResponse(string $response): array {
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception(esc_html($data['error']['message']));
        }

        // Check for safety blocks
        if (isset($data['candidates'][0]['finishReason']) &&
            $data['candidates'][0]['finishReason'] === 'SAFETY') {
            throw new \Exception('Content blocked by safety filters');
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid response structure from Gemini');
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        $metadata = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON response: ' . esc_html(json_last_error_msg()));
        }

        return $metadata;
    }

    public function isConfigured(): bool {
        return !empty($this->apiKey);
    }

    public function getProviderName(): string {
        return 'gemini';
    }

    public function getRateLimit(): int {
        return 60; // 60 requests per minute
    }

    public function testConnection(?string $apiKey = null): bool {
        $keyToTest = $apiKey ?? $this->apiKey;

        if (empty($keyToTest)) {
            throw new \Exception('Gemini API key not configured');
        }

        $url = self::API_URL . '?key=' . $keyToTest;

        $headers = [
            'Content-Type: application/json'
        ];

        $request = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'test'
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 5
            ]
        ];

        $response = $this->makeRequest($url, $request, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception('Gemini API error: ' . esc_html($data['error']['message']));
        }

        if (!isset($data['candidates'])) {
            throw new \Exception('Invalid response from Gemini API');
        }

        return true;
    }

    private function getDefaultPrompt(): string {
        return 'Analyse this image and provide a JSON response with the following fields:
- title: A concise and descriptive title
- description: A detailed description of the image
- alt_text: Alternative text for accessibility (concise, descriptive, max 125 characters)
- caption: A short caption suitable for display below the image
- tags: 5-10 relevant keywords

Respond with valid JSON only.';
    }
}
