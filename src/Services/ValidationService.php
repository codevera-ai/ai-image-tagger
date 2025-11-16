<?php

namespace AIImageTagger\Services;

use AIImageTagger\Models\AIMetadata;

class ValidationService {

    public function validate(AIMetadata $metadata): bool {
        return $metadata->validate();
    }

    public function sanitize(AIMetadata $metadata): AIMetadata {
        return new AIMetadata(
            $this->sanitizeTitle($metadata->getTitle()),
            $this->sanitizeDescription($metadata->getDescription()),
            $this->sanitizeTags($metadata->getTags()),
            $this->sanitizeAltText($metadata->getAltText()),
            $this->sanitizeCaption($metadata->getCaption()),
            $metadata->getConfidence()
        );
    }

    private function sanitizeTitle(string $title): string {
        $title = wp_strip_all_tags($title);
        $title = substr($title, 0, 200);
        return sanitize_text_field($title);
    }

    private function sanitizeDescription(string $description): string {
        $description = wp_strip_all_tags($description);
        $description = substr($description, 0, 1000);
        return sanitize_textarea_field($description);
    }

    private function sanitizeAltText(string $altText): string {
        $altText = wp_strip_all_tags($altText);
        $altText = substr($altText, 0, 200);
        return sanitize_text_field($altText);
    }

    private function sanitizeCaption(string $caption): string {
        $caption = wp_strip_all_tags($caption);
        $caption = substr($caption, 0, 500);
        return sanitize_text_field($caption);
    }

    private function sanitizeTags(array $tags): array {
        $tags = array_map('sanitize_text_field', $tags);
        $tags = array_filter($tags);
        return array_slice($tags, 0, 15);
    }
}
