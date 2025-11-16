<?php

namespace AIImageTagger\Controllers;

use AIImageTagger\Queue\QueueManager;
use AIImageTagger\Services\ProcessingService;
use AIImageTagger\Storage\SettingsRepository;

class MediaController {

    private QueueManager $queueManager;
    private ProcessingService $processor;
    private SettingsRepository $settings;

    public function __construct(
        QueueManager $queueManager,
        ProcessingService $processor,
        SettingsRepository $settings
    ) {
        $this->queueManager = $queueManager;
        $this->processor = $processor;
        $this->settings = $settings;

        $this->registerHooks();
    }

    private function registerHooks(): void {
        add_action('add_attachment', [$this, 'onUpload']);
        add_action('wp_ajax_ai_generate_metadata', [$this, 'generateMetadata']);
        add_action('wp_ajax_ai_regenerate_metadata', [$this, 'regenerateMetadata']);
    }

    public function onUpload(int $attachmentId): void {
        if (!$this->settings->get('auto_process_uploads')) {
            return;
        }

        // Verify it's an image
        if (!wp_attachment_is_image($attachmentId)) {
            return;
        }

        $this->queueManager->enqueue($attachmentId);
    }

    public function generateMetadata(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $attachmentId = isset($_POST['attachment_id']) ? (int) $_POST['attachment_id'] : 0;

        if (!$attachmentId) {
            wp_send_json_error(['message' => 'Invalid attachment ID'], 400);
        }

        $result = $this->processor->processAttachment($attachmentId);

        if ($result->isSuccess()) {
            wp_send_json_success([
                'message' => 'Metadata generated successfully',
                'metadata' => $result->getMetadata()->toArray(),
            ]);
        } else {
            wp_send_json_error([
                'message' => $result->getErrorMessage()
            ], 500);
        }
    }

    public function regenerateMetadata(): void {
        check_ajax_referer('ai_image_tagger_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        }

        $attachmentId = isset($_POST['attachment_id']) ? (int) $_POST['attachment_id'] : 0;

        if (!$attachmentId) {
            wp_send_json_error(['message' => 'Invalid attachment ID'], 400);
        }

        $result = $this->processor->reprocessAttachment($attachmentId);

        if ($result->isSuccess()) {
            wp_send_json_success([
                'message' => 'Metadata regenerated successfully',
                'metadata' => $result->getMetadata()->toArray(),
            ]);
        } else {
            wp_send_json_error([
                'message' => $result->getErrorMessage()
            ], 500);
        }
    }
}
