<?php

namespace AIImageTagger\Admin;

use AIImageTagger\Storage\MetadataRepository;

class MediaMetabox {

    private MetadataRepository $metadataRepo;

    public function __construct() {
        $this->metadataRepo = new MetadataRepository();
        add_action('add_meta_boxes', [$this, 'addMetabox']);
    }

    public function addMetabox(): void {
        add_meta_box(
            'ai-image-tagger-meta',
            __('AI-generated metadata', 'ai-image-tagger'),
            [$this, 'renderMetabox'],
            'attachment',
            'side',
            'default'
        );
    }

    public function renderMetabox(\WP_Post $post): void {
        $is_processed = $this->metadataRepo->isProcessed($post->ID);
        $provider = '';
        $processed_date = '';
        $metadata = null;

        if ($is_processed) {
            $metadata = $this->metadataRepo->get($post->ID);
            $provider = $this->metadataRepo->getProvider($post->ID);
            $processed_date = get_post_meta($post->ID, '_ai_processed_date', true);
        }

        include AI_IMAGE_TAGGER_PLUGIN_DIR . 'templates/admin/media-metabox.php';
    }
}
