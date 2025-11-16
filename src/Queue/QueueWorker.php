<?php

namespace AIImageTagger\Queue;

use AIImageTagger\Storage\QueueRepository;
use AIImageTagger\Services\ProcessingService;

class QueueWorker {

    private QueueRepository $queueRepo;
    private ProcessingService $processor;

    public function __construct(
        QueueRepository $queueRepo,
        ProcessingService $processor
    ) {
        $this->queueRepo = $queueRepo;
        $this->processor = $processor;
    }

    public function processQueue(int $batchSize = 10): void {
        $items = $this->queueRepo->getNextPending($batchSize);

        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    private function processItem(\AIImageTagger\Models\QueueItem $item): void {
        // Mark as processing
        $item->setStatus('processing');
        $this->queueRepo->update($item);

        try {
            // Process attachment
            $result = $this->processor->processAttachment(
                $item->getAttachmentId(),
                $item->getProvider()
            );

            if ($result->isSuccess()) {
                // Delete completed items immediately instead of marking as completed
                $this->queueRepo->delete($item->getId());
            } else {
                $item->incrementAttempts();

                if ($item->hasReachedMaxAttempts()) {
                    $item->markFailed($result->getErrorMessage() ?? 'Unknown error');
                } else {
                    $item->setStatus('pending');
                }

                $this->queueRepo->update($item);
            }

        } catch (\Exception $e) {
            $item->incrementAttempts();
            $item->setErrorMessage($e->getMessage());

            if ($item->hasReachedMaxAttempts()) {
                $item->markFailed($e->getMessage());
            } else {
                $item->setStatus('pending');
            }

            $this->queueRepo->update($item);
        }
    }
}
