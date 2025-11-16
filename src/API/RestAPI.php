<?php

namespace AIImageTagger\API;

use AIImageTagger\Services\ProcessingService;
use AIImageTagger\Storage\MetadataRepository;

class RestAPI {

    private ProcessingService $processor;
    private MetadataRepository $metadataRepo;

    public function __construct(
        ProcessingService $processor,
        MetadataRepository $metadataRepo
    ) {
        $this->processor = $processor;
        $this->metadataRepo = $metadataRepo;

        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        add_action('rest_api_init', function() {
            // Process image endpoint
            register_rest_route('ai-image-tagger/v1', '/process/(?P<id>\d+)', [
                'methods' => 'POST',
                'callback' => [$this, 'processImage'],
                'permission_callback' => function() {
                    return current_user_can('upload_files');
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
            ]);

            // Get metadata endpoint
            register_rest_route('ai-image-tagger/v1', '/metadata/(?P<id>\d+)', [
                'methods' => 'GET',
                'callback' => [$this, 'getMetadata'],
                'permission_callback' => function() {
                    return current_user_can('upload_files');
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
            ]);

            // Update metadata endpoint
            register_rest_route('ai-image-tagger/v1', '/metadata/(?P<id>\d+)', [
                'methods' => 'PUT',
                'callback' => [$this, 'updateMetadata'],
                'permission_callback' => function() {
                    return current_user_can('upload_files');
                },
            ]);
        });
    }

    public function processImage(\WP_REST_Request $request): \WP_REST_Response {
        $attachmentId = (int) $request['id'];
        $provider = $request->get_param('provider');

        $result = $this->processor->processAttachment($attachmentId, $provider);

        if ($result->isSuccess()) {
            return new \WP_REST_Response([
                'success' => true,
                'metadata' => $result->getMetadata()->toArray(),
                'cost' => $result->getCost(),
                'provider' => $result->getProvider(),
            ], 200);
        }

        return new \WP_REST_Response([
            'success' => false,
            'error' => $result->getErrorMessage(),
        ], 500);
    }

    public function getMetadata(\WP_REST_Request $request): \WP_REST_Response {
        $attachmentId = (int) $request['id'];

        $metadata = $this->metadataRepo->get($attachmentId);

        if (!$metadata) {
            return new \WP_REST_Response([
                'error' => 'Metadata not found',
            ], 404);
        }

        return new \WP_REST_Response([
            'metadata' => $metadata->toArray(),
            'provider' => $this->metadataRepo->getProvider($attachmentId),
        ], 200);
    }

    public function updateMetadata(\WP_REST_Request $request): \WP_REST_Response {
        $attachmentId = (int) $request['id'];
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $tags = $request->get_param('tags');

        if (!$title || !$description || !$tags) {
            return new \WP_REST_Response([
                'error' => 'Missing required fields',
            ], 400);
        }

        wp_update_post([
            'ID' => $attachmentId,
            'post_title' => $title,
            'post_content' => $description,
        ]);

        update_post_meta($attachmentId, '_ai_title', $title);
        update_post_meta($attachmentId, '_ai_description', $description);
        update_post_meta($attachmentId, '_ai_tags', $tags);
        wp_set_object_terms($attachmentId, $tags, 'ai_image_tag');

        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Metadata updated successfully',
        ], 200);
    }
}
