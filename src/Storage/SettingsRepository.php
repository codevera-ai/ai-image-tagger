<?php

namespace AIImageTagger\Storage;

use AIImageTagger\Services\EncryptionService;

class SettingsRepository {

    private const OPTION_KEY = 'ai_image_tagger_settings';

    private array $settings;
    private EncryptionService $encryption;

    public function __construct(EncryptionService $encryption) {
        $this->encryption = $encryption;
        $this->settings = get_option(self::OPTION_KEY, $this->getDefaults());
    }

    public function get(string $key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, $value): void {
        $this->settings[$key] = $value;
    }

    public function save(): bool {
        return update_option(self::OPTION_KEY, $this->settings);
    }

    public function getAll(): array {
        return $this->settings;
    }

    public function setApiKey(string $provider, string $apiKey): void {
        if (empty($apiKey)) {
            return;
        }

        $encrypted = $this->encryption->encrypt($apiKey);

        // Store each API key as a separate WordPress option for better update detection
        $option_name = 'ai_image_tagger_api_key_' . sanitize_key($provider);
        update_option($option_name, $encrypted);
    }

    public function getApiKey(string $provider): string {
        // Get from separate option, not from main settings array
        $option_name = 'ai_image_tagger_api_key_' . sanitize_key($provider);
        $encrypted = get_option($option_name, '');
        return $encrypted ? $this->encryption->decrypt($encrypted) : '';
    }

    public function deleteApiKey(string $provider): bool {
        $option_name = 'ai_image_tagger_api_key_' . sanitize_key($provider);
        return delete_option($option_name);
    }

    private function getDefaults(): array {
        return [
            'default_provider' => 'openai',
            'auto_process_uploads' => false,
            // API keys are now stored as separate options, not in this array
            'default_prompt' => '',
            'queue_enabled' => true,
            'batch_size' => 10,
            'rate_limit_per_minute' => 10,
            'image_max_dimension' => 2048,
            'image_quality' => 85,
            'retry_attempts' => 3,
            'retry_delay' => 300,
            'enable_logging' => true,
            'log_retention_days' => 30,
            // Field enable/disable settings
            'enable_title' => true,
            'enable_description' => true,
            'enable_caption' => true,
            // Word length settings
            'title_word_length' => 10,
            'description_word_length' => 50,
            'caption_word_length' => 20,
        ];
    }
}
