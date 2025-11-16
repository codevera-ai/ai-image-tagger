<?php

namespace AIImageTagger\Models;

class Attachment {

    private int $id;
    private string $filePath;
    private string $mimeType;
    private string $fileName;

    public function __construct(int $attachmentId) {
        $this->id = $attachmentId;
        $this->filePath = get_attached_file($attachmentId);
        $this->mimeType = get_post_mime_type($attachmentId);
        $this->fileName = basename($this->filePath);
    }

    public function getId(): int {
        return $this->id;
    }

    public function getFilePath(): string {
        return $this->filePath;
    }

    public function getMimeType(): string {
        return $this->mimeType;
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function isImage(): bool {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isSupported(): bool {
        $supportedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf'
        ];

        return in_array($this->mimeType, $supportedTypes);
    }

    public function exists(): bool {
        return file_exists($this->filePath);
    }

    public function getFileSize(): int {
        return filesize($this->filePath);
    }
}
