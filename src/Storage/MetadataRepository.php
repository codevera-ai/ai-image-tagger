<?php

namespace AIImageTagger\Storage;

use AIImageTagger\Models\AIMetadata;

class MetadataRepository {

    private ?SettingsRepository $settings;

    public function __construct(?SettingsRepository $settings = null) {
        $this->settings = $settings;
    }

    public function save(int $attachmentId, AIMetadata $metadata, string $provider): bool {
        // Check which fields are enabled (default to true if settings not available)
        $enableTitle = $this->settings ? $this->settings->get('enable_title', true) : true;
        $enableDescription = $this->settings ? $this->settings->get('enable_description', true) : true;
        $enableCaption = $this->settings ? $this->settings->get('enable_caption', true) : true;

        // Prepare update data based on enabled fields
        $updateData = ['ID' => $attachmentId];

        if ($enableTitle) {
            $updateData['post_title'] = $metadata->getTitle();
        }

        if ($enableDescription) {
            $updateData['post_content'] = $metadata->getDescription();
        }

        if ($enableCaption) {
            $updateData['post_excerpt'] = $metadata->getCaption();
        }

        // Update post fields
        wp_update_post($updateData);

        // Save alt text to standard WordPress field
        update_post_meta($attachmentId, '_wp_attachment_image_alt', $metadata->getAltText());

        // Save post meta
        update_post_meta($attachmentId, '_ai_processed', true);
        update_post_meta($attachmentId, '_ai_provider', $provider);
        update_post_meta($attachmentId, '_ai_processed_date', current_time('mysql'));
        update_post_meta($attachmentId, '_ai_processing_status', 'completed');

        // Only save custom meta for enabled fields
        if ($enableTitle) {
            update_post_meta($attachmentId, '_ai_title', $metadata->getTitle());
        }

        if ($enableDescription) {
            update_post_meta($attachmentId, '_ai_description', $metadata->getDescription());
        }

        if ($enableCaption) {
            update_post_meta($attachmentId, '_ai_caption', $metadata->getCaption());
        }

        // Always save tags and alt text
        update_post_meta($attachmentId, '_ai_tags', $metadata->getTags());
        update_post_meta($attachmentId, '_ai_alt_text', $metadata->getAltText());
        update_post_meta($attachmentId, '_ai_raw_response', wp_json_encode($metadata->toArray()));

        if ($metadata->getConfidence() !== null) {
            update_post_meta($attachmentId, '_ai_confidence', $metadata->getConfidence());
        }

        // Assign taxonomy terms
        wp_set_object_terms($attachmentId, $metadata->getTags(), 'ai_image_tag');

        return true;
    }

    public function get(int $attachmentId): ?AIMetadata {
        $processed = get_post_meta($attachmentId, '_ai_processed', true);

        if (!$processed) {
            return null;
        }

        $title = get_post_meta($attachmentId, '_ai_title', true);
        $description = get_post_meta($attachmentId, '_ai_description', true);
        $tags = get_post_meta($attachmentId, '_ai_tags', true);
        $altText = get_post_meta($attachmentId, '_ai_alt_text', true);
        $caption = get_post_meta($attachmentId, '_ai_caption', true);
        $confidence = get_post_meta($attachmentId, '_ai_confidence', true);

        return new AIMetadata($title, $description, $tags, $altText, $caption, $confidence ?: null);
    }

    public function isProcessed(int $attachmentId): bool {
        return (bool) get_post_meta($attachmentId, '_ai_processed', true);
    }

    public function getProvider(int $attachmentId): ?string {
        return get_post_meta($attachmentId, '_ai_provider', true) ?: null;
    }

    public function delete(int $attachmentId): bool {
        $metaKeys = [
            '_ai_processed',
            '_ai_provider',
            '_ai_processed_date',
            '_ai_processing_status',
            '_ai_title',
            '_ai_description',
            '_ai_tags',
            '_ai_alt_text',
            '_ai_caption',
            '_ai_raw_response',
            '_ai_confidence',
        ];

        foreach ($metaKeys as $key) {
            delete_post_meta($attachmentId, $key);
        }

        wp_delete_object_term_relationships($attachmentId, 'ai_image_tag');

        return true;
    }
}
