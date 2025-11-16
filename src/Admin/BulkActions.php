<?php

namespace AIImageTagger\Admin;

use AIImageTagger\Queue\QueueManager;
use AIImageTagger\Storage\QueueRepository;
use AIImageTagger\Storage\SettingsRepository;
use AIImageTagger\Services\EncryptionService;

class BulkActions {

    public function __construct() {
        add_filter('bulk_actions-upload', [$this, 'registerBulkAction']);
        add_filter('handle_bulk_actions-upload', [$this, 'handleBulkAction'], 10, 3);
        add_action('admin_notices', [$this, 'showNotice']);
    }

    public function registerBulkAction(array $actions): array {
        $actions['ai_process'] = __('Generate AI metadata', 'ai-image-tagger');
        return $actions;
    }

    public function handleBulkAction(string $redirect, string $action, array $postIds): string {
        if ($action !== 'ai_process') {
            return $redirect;
        }

        $encryption = new EncryptionService();
        $settingsRepo = new SettingsRepository($encryption);
        $queueRepo = new QueueRepository();
        $queueManager = new QueueManager($queueRepo, $settingsRepo);

        $processed = 0;
        foreach ($postIds as $postId) {
            if (wp_attachment_is_image($postId)) {
                $queueManager->enqueue($postId);
                $processed++;
            }
        }

        $redirect = add_query_arg('ai_processed', $processed, $redirect);
        return $redirect;
    }

    public function showNotice(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is displaying a notice from a redirect parameter set by WordPress bulk actions
        if (empty($_GET['ai_processed'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is displaying a notice from a redirect parameter set by WordPress bulk actions
        $count = (int) $_GET['ai_processed'];

        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html(
                sprintf(
                    /* translators: %d: number of images queued for processing */
                    _n(
                        '%d image queued for AI processing.',
                        '%d images queued for AI processing.',
                        $count,
                        'ai-image-tagger'
                    ),
                    $count
                )
            )
        );
    }
}
