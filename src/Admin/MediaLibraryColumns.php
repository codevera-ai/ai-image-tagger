<?php

namespace AIImageTagger\Admin;

class MediaLibraryColumns {

    public function __construct() {
        add_filter('manage_media_columns', [$this, 'addColumn']);
        add_action('manage_media_custom_column', [$this, 'renderColumn'], 10, 2);
        add_filter('manage_upload_sortable_columns', [$this, 'makeSortable']);
    }

    public function addColumn(array $columns): array {
        $columns['ai_status'] = __('AI status', 'ai-image-tagger');
        return $columns;
    }

    public function renderColumn(string $columnName, int $postId): void {
        if ($columnName !== 'ai_status') {
            return;
        }

        $processed = get_post_meta($postId, '_ai_processed', true);

        if ($processed) {
            $provider = get_post_meta($postId, '_ai_provider', true);
            echo '<span class="ai-status-badge ai-status-completed" title="' . esc_attr__('Processed', 'ai-image-tagger') . '">';
            echo '<span class="dashicons dashicons-yes-alt"></span> ';
            echo esc_html(ucfirst($provider));
            echo '</span>';
        } else {
            echo '<span class="ai-status-badge ai-status-pending" title="' . esc_attr__('Not processed', 'ai-image-tagger') . '">';
            echo '<span class="dashicons dashicons-minus"></span> ';
            echo esc_html__('Not processed', 'ai-image-tagger');
            echo '</span>';
        }
    }

    public function makeSortable(array $columns): array {
        $columns['ai_status'] = 'ai_status';
        return $columns;
    }
}
