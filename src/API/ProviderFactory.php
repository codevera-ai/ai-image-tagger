<?php

namespace AIImageTagger\API;

use AIImageTagger\Services\EncryptionService;
use AIImageTagger\Storage\SettingsRepository;

class ProviderFactory {

    private SettingsRepository $settings;
    private EncryptionService $encryption;

    public function __construct(
        SettingsRepository $settings,
        EncryptionService $encryption
    ) {
        $this->settings = $settings;
        $this->encryption = $encryption;
    }

    public function create(?string $provider = null): ProviderInterface {
        $providerName = $provider ?? $this->settings->get('default_provider', 'openai');

        return match($providerName) {
            'openai' => new OpenAIProvider($this->settings, $this->encryption),
            'claude' => new ClaudeProvider($this->settings, $this->encryption),
            'gemini' => new GeminiProvider($this->settings, $this->encryption),
            default => throw new \Exception("Unknown provider: " . esc_html($providerName))
        };
    }

    public function getAvailableProviders(): array {
        return ['openai', 'claude', 'gemini'];
    }
}
