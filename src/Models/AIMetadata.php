<?php

namespace AIImageTagger\Models;

class AIMetadata {

    private string $title;
    private string $description;
    private array $tags;
    private string $altText;
    private string $caption;
    private ?float $confidence;

    public function __construct(
        string $title,
        string $description,
        array $tags,
        string $altText = '',
        string $caption = '',
        ?float $confidence = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->altText = $altText;
        $this->caption = $caption;
        $this->confidence = $confidence;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getTags(): array {
        return $this->tags;
    }

    public function getAltText(): string {
        return $this->altText;
    }

    public function getCaption(): string {
        return $this->caption;
    }

    public function getConfidence(): ?float {
        return $this->confidence;
    }

    public function toArray(): array {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags,
            'alt_text' => $this->altText,
            'caption' => $this->caption,
            'confidence' => $this->confidence,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['tags'] ?? [],
            $data['alt_text'] ?? '',
            $data['caption'] ?? '',
            $data['confidence'] ?? null
        );
    }

    public function validate(): bool {
        if (empty($this->title) || strlen($this->title) > 200) {
            return false;
        }

        if (empty($this->description) || strlen($this->description) > 1000) {
            return false;
        }

        if (empty($this->tags) || count($this->tags) > 15) {
            return false;
        }

        if (!empty($this->altText) && strlen($this->altText) > 200) {
            return false;
        }

        if (!empty($this->caption) && strlen($this->caption) > 500) {
            return false;
        }

        return true;
    }
}
