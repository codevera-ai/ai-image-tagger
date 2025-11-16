<?php
if (!defined('ABSPATH')) {
    exit;
}

// Header variables
$page_title = __('Settings', 'ai-image-tagger');
$page_subtitle = __('Configure AI providers and processing options', 'ai-image-tagger');
$badge_text = 'v' . AI_IMAGE_TAGGER_VERSION;
?>
<div class="wrap">
    <?php include AI_IMAGE_TAGGER_PLUGIN_DIR . 'templates/admin/header.php'; ?>

    <?php settings_errors('ai_image_tagger_messages'); ?>

    <div class="ait-disclosure-box">
        <button type="button" class="ait-disclosure-header" aria-expanded="false">
            <span class="dashicons dashicons-info"></span>
            <strong><?php esc_html_e('External Service Disclosure', 'ai-image-tagger'); ?></strong>
            <span class="dashicons dashicons-arrow-down-alt2 ait-disclosure-toggle"></span>
        </button>
        <div class="ait-disclosure-content" style="display: none;">
            <p><?php esc_html_e('This plugin sends your images to external AI services for analysis. By configuring an API key and enabling image processing, you consent to transmitting your images to the selected AI provider (OpenAI, Anthropic, or Google). Please review the privacy policies:', 'ai-image-tagger'); ?></p>
            <ul>
                <li><a href="https://openai.com/policies/privacy-policy" target="_blank"><?php esc_html_e('OpenAI Privacy Policy', 'ai-image-tagger'); ?></a></li>
                <li><a href="https://www.anthropic.com/legal/privacy" target="_blank"><?php esc_html_e('Anthropic Privacy Policy', 'ai-image-tagger'); ?></a></li>
                <li><a href="https://policies.google.com/privacy" target="_blank"><?php esc_html_e('Google Privacy Policy', 'ai-image-tagger'); ?></a></li>
            </ul>
        </div>
    </div>

    <form action="options.php" method="post">
        <?php
        settings_fields('ai_image_tagger_settings');
        ?>

        <div class="ait-form-section">
            <h2><?php esc_html_e('AI Provider Configuration', 'ai-image-tagger'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="default_provider"><?php esc_html_e('Default AI provider', 'ai-image-tagger'); ?></label>
                </th>
                <td>
                    <select name="ai_image_tagger_settings[default_provider]" id="default_provider">
                        <option value="openai" <?php selected($settings['default_provider'] ?? 'openai', 'openai'); ?>>
                            OpenAI (GPT-5)
                        </option>
                        <option value="claude" <?php selected($settings['default_provider'] ?? 'openai', 'claude'); ?>>
                            Anthropic (Claude Sonnet 4.5)
                        </option>
                        <option value="gemini" <?php selected($settings['default_provider'] ?? 'openai', 'gemini'); ?>>
                            Google (Gemini 2.5 Flash)
                        </option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Select which AI provider to use for image analysis', 'ai-image-tagger'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('API keys', 'ai-image-tagger'); ?></h2>

        <table class="form-table">
            <tr class="api-key-row" data-provider="openai">
                <th scope="row">
                    <label for="ai_image_tagger_api_key_openai"><?php esc_html_e('OpenAI API key', 'ai-image-tagger'); ?></label>
                </th>
                <td>
                    <?php if (!empty($apiKeyDisplays['openai'])): ?>
                        <div class="ai-key-status-configured" style="margin-bottom: 8px;">
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                            <strong><?php esc_html_e('API key configured:', 'ai-image-tagger'); ?></strong>
                            <code><?php echo esc_html($apiKeyDisplays['openai']); ?></code>
                        </div>
                    <?php endif; ?>
                    <input type="password"
                           name="ai_image_tagger_api_key_openai"
                           id="ai_image_tagger_api_key_openai"
                           value=""
                           class="regular-text"
                           autocomplete="off"
                           placeholder="<?php echo !empty($apiKeyDisplays['openai']) ? esc_attr__('Enter new API key (leave empty to keep current)', 'ai-image-tagger') : esc_attr__('Enter your OpenAI API key', 'ai-image-tagger'); ?>">
                    <button type="button"
                            class="button ait-button-secondary ai-test-connection"
                            data-provider="openai">
                        <?php esc_html_e('Test connection', 'ai-image-tagger'); ?>
                    </button>
                    <?php if (!empty($apiKeyDisplays['openai'])): ?>
                        <button type="button"
                                class="button ait-button-danger ai-delete-api-key"
                                data-provider="openai">
                            <?php esc_html_e('Delete API key', 'ai-image-tagger'); ?>
                        </button>
                    <?php endif; ?>
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: HTML link to the OpenAI API keys page */
                            esc_html__('Get your API key from %s', 'ai-image-tagger'),
                            '<a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a>'
                        ); ?>
                    </p>
                    <div class="ai-connection-status" data-provider="openai"></div>
                </td>
            </tr>

            <tr class="api-key-row" data-provider="claude">
                <th scope="row">
                    <label for="ai_image_tagger_api_key_claude"><?php esc_html_e('Claude API key', 'ai-image-tagger'); ?></label>
                </th>
                <td>
                    <?php if (!empty($apiKeyDisplays['claude'])): ?>
                        <div class="ai-key-status-configured" style="margin-bottom: 8px;">
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                            <strong><?php esc_html_e('API key configured:', 'ai-image-tagger'); ?></strong>
                            <code><?php echo esc_html($apiKeyDisplays['claude']); ?></code>
                        </div>
                    <?php endif; ?>
                    <input type="password"
                           name="ai_image_tagger_api_key_claude"
                           id="ai_image_tagger_api_key_claude"
                           value=""
                           class="regular-text"
                           autocomplete="off"
                           placeholder="<?php echo !empty($apiKeyDisplays['claude']) ? esc_attr__('Enter new API key (leave empty to keep current)', 'ai-image-tagger') : esc_attr__('Enter your Claude API key', 'ai-image-tagger'); ?>">
                    <button type="button"
                            class="button ait-button-secondary ai-test-connection"
                            data-provider="claude">
                        <?php esc_html_e('Test connection', 'ai-image-tagger'); ?>
                    </button>
                    <?php if (!empty($apiKeyDisplays['claude'])): ?>
                        <button type="button"
                                class="button ait-button-danger ai-delete-api-key"
                                data-provider="claude">
                            <?php esc_html_e('Delete API key', 'ai-image-tagger'); ?>
                        </button>
                    <?php endif; ?>
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: HTML link to the Anthropic API keys page */
                            esc_html__('Get your API key from %s', 'ai-image-tagger'),
                            '<a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic</a>'
                        ); ?>
                    </p>
                    <div class="ai-connection-status" data-provider="claude"></div>
                </td>
            </tr>

            <tr class="api-key-row" data-provider="gemini">
                <th scope="row">
                    <label for="ai_image_tagger_api_key_gemini"><?php esc_html_e('Gemini API key', 'ai-image-tagger'); ?></label>
                </th>
                <td>
                    <?php if (!empty($apiKeyDisplays['gemini'])): ?>
                        <div class="ai-key-status-configured" style="margin-bottom: 8px;">
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                            <strong><?php esc_html_e('API key configured:', 'ai-image-tagger'); ?></strong>
                            <code><?php echo esc_html($apiKeyDisplays['gemini']); ?></code>
                        </div>
                    <?php endif; ?>
                    <input type="password"
                           name="ai_image_tagger_api_key_gemini"
                           id="ai_image_tagger_api_key_gemini"
                           value=""
                           class="regular-text"
                           autocomplete="off"
                           placeholder="<?php echo !empty($apiKeyDisplays['gemini']) ? esc_attr__('Enter new API key (leave empty to keep current)', 'ai-image-tagger') : esc_attr__('Enter your Gemini API key', 'ai-image-tagger'); ?>">
                    <button type="button"
                            class="button ait-button-secondary ai-test-connection"
                            data-provider="gemini">
                        <?php esc_html_e('Test connection', 'ai-image-tagger'); ?>
                    </button>
                    <?php if (!empty($apiKeyDisplays['gemini'])): ?>
                        <button type="button"
                                class="button ait-button-danger ai-delete-api-key"
                                data-provider="gemini">
                            <?php esc_html_e('Delete API key', 'ai-image-tagger'); ?>
                        </button>
                    <?php endif; ?>
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: HTML link to the Google Gemini API keys page */
                            esc_html__('Get your API key from %s', 'ai-image-tagger'),
                            '<a href="https://makersuite.google.com/app/apikey" target="_blank">Google</a>'
                        ); ?>
                    </p>
                    <div class="ai-connection-status" data-provider="gemini"></div>
                </td>
            </tr>
        </table>
        </div>

        <div class="ait-form-section">
            <h2><?php esc_html_e('Processing Options', 'ai-image-tagger'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php esc_html_e('Automatic processing', 'ai-image-tagger'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="ai_image_tagger_settings[auto_process_uploads]"
                               value="1"
                               <?php checked($settings['auto_process_uploads'] ?? false, 1); ?>>
                        <?php esc_html_e('Automatically process images on upload', 'ai-image-tagger'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('When enabled, images will be queued for AI processing as soon as they are uploaded. Images are not processed instantly. Processing happens via WordPress cron, which typically runs every 5 minutes when your site receives traffic.', 'ai-image-tagger'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="batch_size"><?php esc_html_e('Batch size', 'ai-image-tagger'); ?></label>
                </th>
                <td>
                    <input type="number"
                           name="ai_image_tagger_settings[batch_size]"
                           id="batch_size"
                           value="<?php echo esc_attr($settings['batch_size'] ?? 10); ?>"
                           min="1"
                           max="100"
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Number of images to process in each queue batch', 'ai-image-tagger'); ?>
                    </p>
                </td>
            </tr>
        </table>
        </div>

        <?php submit_button(__('Save Settings', 'ai-image-tagger'), 'primary ait-button-primary'); ?>
    </form>
</div>
