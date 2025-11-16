<?php

namespace AIImageTagger\Services;

use AIImageTagger\Models\Attachment;
use AIImageTagger\Models\ProcessingResult;
use AIImageTagger\API\ProviderFactory;
use AIImageTagger\Storage\MetadataRepository;
use AIImageTagger\Exceptions\ValidationException;

class ProcessingService {

    private ProviderFactory $providerFactory;
    private MetadataRepository $metadataRepo;
    private ValidationService $validator;

    public function __construct(
        ProviderFactory $providerFactory,
        MetadataRepository $metadataRepo,
        ValidationService $validator
    ) {
        $this->providerFactory = $providerFactory;
        $this->metadataRepo = $metadataRepo;
        $this->validator = $validator;
    }

    public function processAttachment(int $attachmentId, ?string $provider = null): ProcessingResult {
        $startTime = microtime(true);

        try {
            // Create attachment model
            $attachment = new Attachment($attachmentId);

            // Validate attachment
            if (!$attachment->exists()) {
                return ProcessingResult::failure('Attachment file not found');
            }

            if (!$attachment->isSupported()) {
                return ProcessingResult::failure('Unsupported file type: ' . $attachment->getMimeType());
            }

            // Get provider
            $aiProvider = $this->providerFactory->create($provider);

            if (!$aiProvider->isConfigured()) {
                return ProcessingResult::failure('AI provider not configured');
            }

            // Process image
            $metadata = $aiProvider->analyzeImage($attachment->getFilePath());

            // Validate metadata
            if (!$this->validator->validate($metadata)) {
                throw new ValidationException('Invalid metadata returned from AI provider');
            }

            // Sanitize metadata
            $metadata = $this->validator->sanitize($metadata);

            // Save metadata
            $this->metadataRepo->save($attachmentId, $metadata, $aiProvider->getProviderName());

            // Calculate processing time
            $processingTime = microtime(true) - $startTime;

            // Return success result
            return ProcessingResult::success(
                $metadata,
                0, // tokens - to be implemented by providers
                $aiProvider->getProviderName(),
                $processingTime
            );

        } catch (\Exception $e) {
            return ProcessingResult::failure($e->getMessage());
        }
    }

    public function reprocessAttachment(int $attachmentId, ?string $provider = null): ProcessingResult {
        // Delete existing metadata
        $this->metadataRepo->delete($attachmentId);

        // Process again
        return $this->processAttachment($attachmentId, $provider);
    }
}
