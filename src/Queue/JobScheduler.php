<?php

namespace AIImageTagger\Queue;

use AIImageTagger\Storage\SettingsRepository;

class JobScheduler {

    private QueueWorker $worker;
    private SettingsRepository $settings;

    public function __construct(
        QueueWorker $worker,
        SettingsRepository $settings
    ) {
        $this->worker = $worker;
        $this->settings = $settings;
    }

    public function processQueue(): void {
        $batchSize = (int) $this->settings->get('batch_size', 10);
        $this->worker->processQueue($batchSize);
    }
}
