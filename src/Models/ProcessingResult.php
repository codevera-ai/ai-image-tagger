<?php

namespace AIImageTagger\Models;

class ProcessingResult {

    private bool $success;
    private ?AIMetadata $metadata;
    private ?string $errorMessage;
    private int $tokensUsed;
    private string $provider;
    private float $processingTime;

    public function __construct(
        bool $success,
        ?AIMetadata $metadata = null,
        ?string $errorMessage = null,
        int $tokensUsed = 0,
        string $provider = '',
        float $processingTime = 0.0
    ) {
        $this->success = $success;
        $this->metadata = $metadata;
        $this->errorMessage = $errorMessage;
        $this->tokensUsed = $tokensUsed;
        $this->provider = $provider;
        $this->processingTime = $processingTime;
    }

    public function isSuccess(): bool {
        return $this->success;
    }

    public function getMetadata(): ?AIMetadata {
        return $this->metadata;
    }

    public function getErrorMessage(): ?string {
        return $this->errorMessage;
    }

    public function getTokensUsed(): int {
        return $this->tokensUsed;
    }

    public function getProvider(): string {
        return $this->provider;
    }

    public function getProcessingTime(): float {
        return $this->processingTime;
    }

    public static function success(
        AIMetadata $metadata,
        int $tokensUsed,
        string $provider,
        float $processingTime
    ): self {
        return new self(
            true,
            $metadata,
            null,
            $tokensUsed,
            $provider,
            $processingTime
        );
    }

    public static function failure(string $errorMessage): self {
        return new self(false, null, $errorMessage);
    }
}
