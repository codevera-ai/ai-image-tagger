<?php

namespace AIImageTagger\Models;

class QueueItem {

    private ?int $id;
    private int $attachmentId;
    private string $status;
    private string $provider;
    private int $attempts;
    private int $maxAttempts;
    private ?string $errorMessage;
    private string $createdAt;
    private string $updatedAt;
    private ?string $processedAt;

    public function __construct(
        int $attachmentId,
        string $provider,
        string $status = 'pending',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->attachmentId = $attachmentId;
        $this->status = $status;
        $this->provider = $provider;
        $this->attempts = 0;
        $this->maxAttempts = 3;
        $this->errorMessage = null;
        $this->createdAt = current_time('mysql');
        $this->updatedAt = current_time('mysql');
        $this->processedAt = null;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getAttachmentId(): int {
        return $this->attachmentId;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
        $this->updatedAt = current_time('mysql');
    }

    public function getProvider(): string {
        return $this->provider;
    }

    public function incrementAttempts(): void {
        $this->attempts++;
        $this->updatedAt = current_time('mysql');
    }

    public function getAttempts(): int {
        return $this->attempts;
    }

    public function hasReachedMaxAttempts(): bool {
        return $this->attempts >= $this->maxAttempts;
    }

    public function setErrorMessage(string $message): void {
        $this->errorMessage = $message;
        $this->updatedAt = current_time('mysql');
    }

    public function getErrorMessage(): ?string {
        return $this->errorMessage;
    }

    public function markCompleted(): void {
        $this->status = 'completed';
        $this->processedAt = current_time('mysql');
        $this->updatedAt = current_time('mysql');
    }

    public function markFailed(string $errorMessage): void {
        $this->status = 'failed';
        $this->errorMessage = $errorMessage;
        $this->updatedAt = current_time('mysql');
    }
}
