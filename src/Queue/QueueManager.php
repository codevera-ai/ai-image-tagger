<?php

namespace AIImageTagger\Queue;

use AIImageTagger\Models\QueueItem;
use AIImageTagger\Storage\QueueRepository;
use AIImageTagger\Storage\SettingsRepository;

class QueueManager {

    private QueueRepository $queueRepo;
    private SettingsRepository $settings;

    public function __construct(
        QueueRepository $queueRepo,
        SettingsRepository $settings
    ) {
        $this->queueRepo = $queueRepo;
        $this->settings = $settings;
    }

    public function enqueue(int $attachmentId, ?string $provider = null): int {
        $provider = $provider ?? $this->settings->get('default_provider');

        $item = new QueueItem($attachmentId, $provider);
        return $this->queueRepo->enqueue($item);
    }

    public function enqueueMany(array $attachmentIds, ?string $provider = null): array {
        $ids = [];
        foreach ($attachmentIds as $attachmentId) {
            $ids[] = $this->enqueue($attachmentId, $provider);
        }
        return $ids;
    }

    public function getQueueStatus(): array {
        return [
            'pending' => $this->queueRepo->getQueueCount(),
            'total' => $this->queueRepo->getQueueCount(),
        ];
    }
}
