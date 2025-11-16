<?php
/**
 * Admin Header Template
 * Displays branded header on all plugin admin pages
 *
 * @var string $page_title The main page title
 * @var string $page_subtitle Optional subtitle
 * @var string $badge_text Optional badge text
 */

if (!defined('ABSPATH')) {
    exit;
}

// Set defaults
$page_title = $page_title ?? __('AI Image Tagger', 'ai-image-tagger');
$page_subtitle = $page_subtitle ?? '';
$badge_text = $badge_text ?? '';
?>

<div class="ait-header">
    <div class="ait-header-content">
        <div class="ait-header-title">
            <div class="ait-header-icon">
                <span class="dashicons dashicons-images-alt2" style="font-size: 24px;"></span>
            </div>
            <div>
                <h1><?php echo esc_html($page_title); ?></h1>
                <?php if ($page_subtitle): ?>
                    <p class="ait-header-subtitle"><?php echo esc_html($page_subtitle); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($badge_text): ?>
            <div class="ait-header-badge">
                <?php echo esc_html($badge_text); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
