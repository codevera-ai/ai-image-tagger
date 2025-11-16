<?php

namespace AIImageTagger\Admin;

class SettingsPage {

    public function __construct() {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addSettingsPage(): void {
        add_submenu_page(
            'options-general.php',
            __('AI Image Tagger', 'ai-image-tagger'),
            __('AI Image Tagger', 'ai-image-tagger'),
            'manage_options',
            'ai-image-tagger',
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings(): void {
        register_setting(
            'ai_image_tagger_settings',
            'ai_image_tagger_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeSettings'],
            ]
        );

        // Register API keys as separate options with encryption filters
        $providers = ['openai', 'claude', 'gemini'];
        foreach ($providers as $provider) {
            $option_name = 'ai_image_tagger_api_key_' . $provider;

            register_setting(
                'ai_image_tagger_settings',
                $option_name,
                [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => '',
                ]
            );

            // Add encryption filters
            add_filter('pre_update_option_' . $option_name, [$this, 'encryptApiKeyOnSave'], 10, 2);
            add_filter('pre_add_option_' . $option_name, [$this, 'encryptApiKeyOnAdd'], 10, 2);
        }

        // Provider Section
        add_settings_section(
            'ai_image_tagger_provider',
            __('AI Provider configuration', 'ai-image-tagger'),
            null,
            'ai-image-tagger'
        );

        // Processing Section
        add_settings_section(
            'ai_image_tagger_processing',
            __('Processing options', 'ai-image-tagger'),
            null,
            'ai-image-tagger'
        );
    }

    public function renderSettingsPage(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = get_option('ai_image_tagger_settings', []);

        // Create display versions of API keys showing first/last characters
        $apiKeyDisplays = [];
        $providers = ['openai', 'claude', 'gemini'];
        $encryption = new \AIImageTagger\Services\EncryptionService();

        foreach ($providers as $provider) {
            // Get from separate option
            $option_name = 'ai_image_tagger_api_key_' . sanitize_key($provider);
            $encrypted = get_option($option_name, '');

            if (!empty($encrypted)) {
                try {
                    $decrypted = $encryption->decrypt($encrypted);
                    if (!empty($decrypted)) {
                        $length = strlen($decrypted);
                        if ($length > 8) {
                            $first = substr($decrypted, 0, 4);
                            $last = substr($decrypted, -4);
                            $apiKeyDisplays[$provider] = $first . '••••••' . $last;
                        } else {
                            $apiKeyDisplays[$provider] = '••••••••';
                        }
                    }
                } catch (\Exception $e) {
                    $apiKeyDisplays[$provider] = '••••••••';
                }
            } else {
                $apiKeyDisplays[$provider] = '';
            }
        }

        include AI_IMAGE_TAGGER_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }

    public function encryptApiKeyOnSave(string $value, string $old_value): string {
        // If empty, keep the old value (don't delete the key)
        if (empty($value)) {
            return $old_value;
        }

        // Check if already encrypted (long base64 string)
        if (strlen($value) > 250 && preg_match('/^[A-Za-z0-9+\/=]+$/', $value)) {
            return $value;
        }

        // Encrypt the new key
        $encryption = new \AIImageTagger\Services\EncryptionService();
        $encrypted = $encryption->encrypt($value);

        if (!$encrypted) {
            return $old_value; // Keep old value on encryption failure
        }

        return $encrypted;
    }

    public function encryptApiKeyOnAdd(string $value, string $option_name): string {
        // If empty, don't save anything
        if (empty($value)) {
            return '';
        }

        // Check if already encrypted
        if (strlen($value) > 250 && preg_match('/^[A-Za-z0-9+\/=]+$/', $value)) {
            return $value;
        }

        // Encrypt the new key
        $encryption = new \AIImageTagger\Services\EncryptionService();
        $encrypted = $encryption->encrypt($value);

        if (!$encrypted) {
            return ''; // Don't save if encryption fails
        }

        return $encrypted;
    }

    public function sanitizeSettings(array $input): array {
        $sanitized = [];

        if (isset($input['default_provider'])) {
            $sanitized['default_provider'] = sanitize_text_field($input['default_provider']);
        }

        // Checkboxes need special handling - they're not present when unchecked
        $sanitized['auto_process_uploads'] = isset($input['auto_process_uploads']) && $input['auto_process_uploads'] === '1';

        if (isset($input['batch_size'])) {
            $sanitized['batch_size'] = absint($input['batch_size']);
        }

        // Field enable/disable checkboxes
        $sanitized['enable_title'] = isset($input['enable_title']) && $input['enable_title'] === '1';
        $sanitized['enable_description'] = isset($input['enable_description']) && $input['enable_description'] === '1';
        $sanitized['enable_caption'] = isset($input['enable_caption']) && $input['enable_caption'] === '1';

        // Word length settings
        if (isset($input['title_word_length'])) {
            $sanitized['title_word_length'] = max(1, absint($input['title_word_length']));
        }
        if (isset($input['description_word_length'])) {
            $sanitized['description_word_length'] = max(1, absint($input['description_word_length']));
        }
        if (isset($input['caption_word_length'])) {
            $sanitized['caption_word_length'] = max(1, absint($input['caption_word_length']));
        }

        // Merge with existing settings
        $existing = get_option('ai_image_tagger_settings', []);
        return array_merge($existing, $sanitized);
    }
}
