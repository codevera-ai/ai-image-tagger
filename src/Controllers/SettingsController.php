<?php

namespace AIImageTagger\Controllers;

use AIImageTagger\Storage\SettingsRepository;
use AIImageTagger\API\ProviderFactory;

class SettingsController {

    private SettingsRepository $settings;
    private ProviderFactory $providerFactory;

    public function __construct(
        SettingsRepository $settings,
        ProviderFactory $providerFactory
    ) {
        $this->settings = $settings;
        $this->providerFactory = $providerFactory;

        $this->registerHooks();
    }

    private function registerHooks(): void {
        add_action('wp_ajax_ai_test_connection', [$this, 'testConnection']);
        add_action('wp_ajax_ai_save_api_key', [$this, 'saveApiKey']);
        add_action('wp_ajax_ai_delete_api_key', [$this, 'deleteApiKey']);
    }

    public function testConnection(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
        $apiKey = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : null;

        if (!in_array($provider, ['openai', 'claude', 'gemini'])) {
            wp_send_json_error(['message' => 'Invalid provider'], 400);
        }

        try {
            $aiProvider = $this->providerFactory->create($provider);

            // If no API key provided, use stored key (returns null which triggers provider to use stored key)
            if ($aiProvider->testConnection($apiKey)) {
                wp_send_json_success([
                    'message' => __('Connection successful', 'ai-image-tagger')
                ]);
            } else {
                wp_send_json_error([
                    'message' => __('Connection failed', 'ai-image-tagger')
                ], 500);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function saveApiKey(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
        $apiKey = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

        if (!in_array($provider, ['openai', 'claude', 'gemini'])) {
            wp_send_json_error(['message' => 'Invalid provider'], 400);
        }

        if (empty($apiKey)) {
            wp_send_json_error(['message' => 'API key cannot be empty'], 400);
        }

        // setApiKey now handles saving to its own option
        $this->settings->setApiKey($provider, $apiKey);

        wp_send_json_success([
            'message' => __('API key saved successfully', 'ai-image-tagger')
        ]);
    }

    public function deleteApiKey(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';

        if (!in_array($provider, ['openai', 'claude', 'gemini'])) {
            wp_send_json_error(['message' => 'Invalid provider'], 400);
        }

        $result = $this->settings->deleteApiKey($provider);

        if ($result) {
            wp_send_json_success([
                'message' => __('API key deleted successfully', 'ai-image-tagger')
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Failed to delete API key', 'ai-image-tagger')
            ], 500);
        }
    }
}
