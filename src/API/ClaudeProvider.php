<?php

namespace AIImageTagger\API;

use AIImageTagger\Models\AIMetadata;
use AIImageTagger\Exceptions\RateLimitException;

class ClaudeProvider extends AbstractProvider {

    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-sonnet-4-5';

    protected function getApiKey(): string {
        return $this->settings->getApiKey('claude');
    }

    public function analyzeImage(string $imagePath): AIMetadata {
        if (!$this->isConfigured()) {
            throw new \Exception('Claude provider not configured');
        }

        $optimizedPath = $this->optimizeImage($imagePath);
        $request = $this->buildRequest($optimizedPath);

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'anthropic-beta: structured-outputs-2025-11-13'
        ];

        try {
            $response = $this->makeRequest(self::API_URL, $request, $headers);
            $parsed = $this->parseResponse($response);

            return AIMetadata::fromArray($parsed);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'rate_limit')) {
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
            'model' => self::MODEL,
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $imageData
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'system' => 'You are an expert at analysing images.',
            'output_format' => [
                'type' => 'json_schema',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'alt_text' => ['type' => 'string'],
                        'caption' => ['type' => 'string'],
                        'tags' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ]
                    ],
                    'required' => ['title', 'description', 'alt_text', 'caption', 'tags'],
                    'additionalProperties' => false
                ]
            ]
        ];
    }

    protected function parseResponse(string $response): array {
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception(esc_html($data['error']['message']));
        }

        if (!isset($data['content'][0]['text'])) {
            throw new \Exception('Invalid response structure from Claude');
        }

        // With structured outputs, Claude returns valid JSON directly
        $content = $data['content'][0]['text'];
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
        return 'claude';
    }

    public function getRateLimit(): int {
        return 50; // 50 requests per minute
    }

    public function testConnection(?string $apiKey = null): bool {
        $keyToTest = $apiKey ?? $this->apiKey;

        if (empty($keyToTest)) {
            throw new \Exception('Claude API key not configured');
        }

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $keyToTest,
            'anthropic-version: 2023-06-01',
            'anthropic-beta: structured-outputs-2025-11-13'
        ];

        $request = [
            'model' => self::MODEL,
            'max_tokens' => 5,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'test'
                ]
            ]
        ];

        $response = $this->makeRequest(self::API_URL, $request, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception('Claude API error: ' . esc_html($data['error']['message']));
        }

        if (!isset($data['content'])) {
            throw new \Exception('Invalid response from Claude API');
        }

        return true;
    }

    private function getDefaultPrompt(): string {
        return 'Analyse this image and provide metadata in JSON format with exactly these fields:
- title: A concise and descriptive title
- description: A detailed description of the image
- alt_text: Alternative text for accessibility (concise, descriptive, max 125 characters)
- caption: A short caption suitable for display below the image
- tags: 5-10 relevant keywords (array)

Respond with only valid JSON, no additional text.';
    }
}
