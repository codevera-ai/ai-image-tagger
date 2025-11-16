<?php

namespace AIImageTagger\Controllers;

use AIImageTagger\Queue\QueueManager;
use AIImageTagger\Storage\QueueRepository;

class QueueController {

    private QueueManager $queueManager;
    private QueueRepository $queueRepo;

    public function __construct(
        QueueManager $queueManager,
        QueueRepository $queueRepo
    ) {
        $this->queueManager = $queueManager;
        $this->queueRepo = $queueRepo;

        $this->registerHooks();
    }

    private function registerHooks(): void {
        add_action('wp_ajax_ai_queue_status', [$this, 'getQueueStatus']);
        add_action('wp_ajax_ai_clear_queue', [$this, 'clearQueue']);
    }

    public function getQueueStatus(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $status = $this->queueManager->getQueueStatus();

        wp_send_json_success($status);
    }

    public function clearQueue(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        // Clear pending items
        global $wpdb;
        $table = $wpdb->prefix . 'ai_processing_queue';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE status = %s",
                'pending'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        wp_send_json_success([
            'message' => __('Queue cleared successfully', 'ai-image-tagger')
        ]);
    }
}
