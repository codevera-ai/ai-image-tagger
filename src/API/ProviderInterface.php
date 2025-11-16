<?php

namespace AIImageTagger\API;

use AIImageTagger\Models\AIMetadata;

interface ProviderInterface {

    /**
     * Analyse an image and return metadata
     *
     * @param string $imagePath Absolute path to image file
     * @return AIMetadata
     * @throws \Exception
     */
    public function analyzeImage(string $imagePath): AIMetadata;

    /**
     * Check if provider is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get rate limit (requests per minute)
     *
     * @return int
     */
    public function getRateLimit(): int;

    /**
     * Test connection to provider API
     *
     * @param string|null $apiKey Optional API key to test (if not provided, uses stored key)
     * @return bool
     * @throws \Exception
     */
    public function testConnection(?string $apiKey = null): bool;
}
