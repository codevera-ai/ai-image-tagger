<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="ai-image-tagger-metabox">
    <?php if ($is_processed): ?>
        <div class="ait-metabox-processed">
            <div class="ait-status-bar">
                <div class="ait-status-indicator">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="ait-status-content">
                    <div class="ait-status-title">Processed</div>
                    <div class="ait-status-meta">
                        <span class="ait-provider"><?php echo esc_html(ucfirst($provider)); ?></span>
                        <span class="ait-separator">•</span>
                        <span class="ait-date"><?php echo esc_html(human_time_diff(strtotime($processed_date), current_time('timestamp')) . ' ago'); ?></span>
                    </div>
                </div>
            </div>

            <button type="button"
                    class="ait-metabox-button ait-button-regenerate ai-regenerate-metadata"
                    data-attachment-id="<?php echo esc_attr($post->ID); ?>">
                <span class="dashicons dashicons-update"></span>
                <span><?php esc_html_e('Regenerate', 'ai-image-tagger'); ?></span>
            </button>
        </div>
    <?php else: ?>
        <div class="ait-metabox-unprocessed">
            <div class="ait-status-bar ait-status-pending">
                <div class="ait-status-indicator ait-indicator-pending">
                    <span class="dashicons dashicons-minus"></span>
                </div>
                <div class="ait-status-content">
                    <div class="ait-status-title">Not processed</div>
                    <div class="ait-status-meta">Generate AI metadata for this image</div>
                </div>
            </div>

            <button type="button"
                    class="ait-metabox-button ait-button-generate ai-generate-metadata"
                    data-attachment-id="<?php echo esc_attr($post->ID); ?>">
                <span class="dashicons dashicons-star-filled"></span>
                <span><?php esc_html_e('Generate metadata', 'ai-image-tagger'); ?></span>
            </button>
        </div>
    <?php endif; ?>

    <div class="ai-processing-status ait-status-bar ait-status-processing" style="display: none;">
        <div class="ait-status-indicator ait-indicator-processing">
            <span class="dashicons dashicons-update ait-rotating"></span>
        </div>
        <div class="ait-status-content">
            <div class="ait-status-title ai-status-text">Processing...</div>
            <div class="ait-status-meta">This may take a few moments</div>
        </div>
    </div>

    <div class="ai-error-message ait-error-notice" style="display: none;">
        <div class="ait-error-icon">
            <span class="dashicons dashicons-warning"></span>
        </div>
        <div class="ait-error-text"></div>
    </div>
</div>

<style>
.ai-image-tagger-metabox {
    margin: -6px -12px -12px;
    padding: 0;
}

.ait-status-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f6f7f7;
    border-radius: 4px;
    margin-bottom: 10px;
}

.ait-status-indicator {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #00a32a;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ait-status-indicator .dashicons {
    color: #fff;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ait-indicator-pending {
    background: #dba617;
}

.ait-indicator-processing {
    background: #2271b1;
}

.ait-indicator-processing .dashicons {
    color: #fff;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

@keyframes ait-rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.ait-rotating {
    animation: ait-rotate 1s linear infinite;
    display: inline-block;
}

.ait-status-content {
    flex: 1;
    min-width: 0;
}

.ait-status-title {
    font-size: 13px;
    font-weight: 600;
    color: #1d2327;
    line-height: 1.4;
    margin: 0 0 2px 0;
}

.ait-status-meta {
    font-size: 12px;
    color: #646970;
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.ait-provider {
    font-weight: 500;
}

.ait-separator {
    color: #c3c4c7;
}

.ait-metabox-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #2271b1;
    background: #2271b1;
    color: #fff;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: none;
}

.ait-metabox-button:hover {
    background: #135e96;
    border-color: #135e96;
    color: #fff;
}

.ait-metabox-button:active {
    transform: translateY(1px);
}

.ait-metabox-button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ait-button-regenerate {
    background: #fff;
    border-color: #c3c4c7;
    color: #2c3338;
}

.ait-button-regenerate:hover {
    background: #f6f7f7;
    border-color: #8c8f94;
    color: #2c3338;
}

.ait-error-notice {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px;
    background: #fcf0f1;
    border-left: 4px solid #d63638;
    border-radius: 0 4px 4px 0;
    margin-top: 10px;
}

.ait-error-icon {
    flex-shrink: 0;
    color: #d63638;
}

.ait-error-icon .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.ait-error-notice .ait-error-text {
    flex: 1;
    font-size: 12px;
    color: #50575e;
    margin: 0;
    line-height: 1.5;
}
</style>
