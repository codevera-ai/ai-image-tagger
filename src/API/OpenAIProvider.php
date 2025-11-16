<?php

namespace AIImageTagger\API;

use AIImageTagger\Models\AIMetadata;
use AIImageTagger\Exceptions\RateLimitException;
use AIImageTagger\Exceptions\ValidationException;

class OpenAIProvider extends AbstractProvider {

    private const CHAT_COMPLETIONS_URL = 'https://api.openai.com/v1/chat/completions';
    private const RESPONSES_URL = 'https://api.openai.com/v1/responses';
    private const MODEL = 'gpt-5';

    protected function getApiKey(): string {
        return $this->settings->getApiKey('openai');
    }

    /**
     * Check if model requires Responses API (GPT-5 and newer)
     */
    private function usesResponsesApi(): bool {
        return strpos(self::MODEL, 'gpt-5') === 0 || strpos(self::MODEL, 'gpt-6') === 0;
    }

    /**
     * Get the appropriate API URL based on model
     */
    private function getApiUrl(): string {
        return $this->usesResponsesApi() ? self::RESPONSES_URL : self::CHAT_COMPLETIONS_URL;
    }

    public function analyzeImage(string $imagePath): AIMetadata {
        if (!$this->isConfigured()) {
            throw new \Exception('OpenAI provider not configured');
        }

        $optimizedPath = $this->optimizeImage($imagePath);
        $request = $this->buildRequest($optimizedPath);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        try {
            $response = $this->makeRequest($this->getApiUrl(), $request, $headers, 300); // GPT-5 needs longer timeout
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

        $schema = [
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
        ];

        if ($this->usesResponsesApi()) {
            // Responses API format (GPT-5 and newer) - uses image_url format
            return [
                'model' => self::MODEL,
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'You are an expert image analyst. Respond with valid JSON only.'
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'input_image',
                                'image_url' => "data:{$mimeType};base64,{$imageData}"
                            ]
                        ]
                    ]
                ],
                'max_output_tokens' => 16000,
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'image_metadata',
                        'strict' => true,
                        'schema' => $schema
                    ]
                ]
            ];
        } else {
            // Chat Completions API format (GPT-4o and older)
            return [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert image analyst. Respond with valid JSON only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageData}"
                                ]
                            ]
                        ]
                    ]
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'image_metadata',
                        'strict' => true,
                        'schema' => $schema
                    ]
                ],
                'max_tokens' => 500
            ];
        }
    }

    protected function parseResponse(string $response): array {
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception(esc_html($data['error']['message']));
        }

        $content = null;

        if ($this->usesResponsesApi()) {
            // GPT-5 Responses API format
            if (!isset($data['output']) || !is_array($data['output'])) {
                throw new \Exception('Invalid response structure from OpenAI Responses API');
            }

            // Find the message item in the output array
            foreach ($data['output'] as $item) {
                if (isset($item['type']) && $item['type'] === 'message' && isset($item['content'])) {
                    // Content is an array of content items, find the output_text
                    if (is_array($item['content'])) {
                        foreach ($item['content'] as $contentItem) {
                            if (isset($contentItem['type']) && $contentItem['type'] === 'output_text' && isset($contentItem['text'])) {
                                $content = $contentItem['text'];
                                break 2; // Break out of both loops
                            }
                        }
                    }
                }
            }

            if ($content === null) {
                throw new \Exception('No output_text found in OpenAI response');
            }
        } else {
            // Chat Completions API format (GPT-4o and older)
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response structure from OpenAI');
            }

            $content = $data['choices'][0]['message']['content'];
        }

        // For Responses API with json_schema, the content is already the structured data
        // For Chat Completions API, it's a JSON string
        if (is_array($content)) {
            $metadata = $content;
        } else {
            $metadata = json_decode($content, true);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON response: ' . esc_html(json_last_error_msg()));
        }

        return $metadata;
    }

    public function isConfigured(): bool {
        return !empty($this->apiKey);
    }

    public function getProviderName(): string {
        return 'openai';
    }

    public function getRateLimit(): int {
        return 500; // 500 requests per minute
    }

    public function testConnection(?string $apiKey = null): bool {
        $keyToTest = $apiKey ?? $this->apiKey;

        if (empty($keyToTest)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $keyToTest
        ];

        if ($this->usesResponsesApi()) {
            // Responses API format - uses input_text type
            $request = [
                'model' => self::MODEL,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'test'
                            ]
                        ]
                    ]
                ],
                'max_output_tokens' => 10
            ];
        } else {
            // Chat Completions API format
            $request = [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'test'
                    ]
                ],
                'max_tokens' => 5
            ];
        }

        $response = $this->makeRequest($this->getApiUrl(), $request, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new \Exception('OpenAI API error: ' . esc_html($data['error']['message']));
        }

        if ($this->usesResponsesApi()) {
            if (!isset($data['output'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }
        } else {
            if (!isset($data['choices'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }
        }

        return true;
    }

    private function getDefaultPrompt(): string {
        return 'Analyse this image and provide metadata in JSON format with the following fields:
- title: A concise and descriptive title
- description: A detailed description of the image
- alt_text: Alternative text for accessibility (concise, descriptive, max 125 characters)
- caption: A short caption suitable for display below the image
- tags: 5-10 relevant keywords or tags';
    }
}
