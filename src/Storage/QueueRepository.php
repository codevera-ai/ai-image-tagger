<?php

namespace AIImageTagger\Storage;

use AIImageTagger\Models\QueueItem;

class QueueRepository {

    private string $tableName;

    public function __construct() {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ai_processing_queue';
    }

    public function enqueue(QueueItem $item): int {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table operation for queue management
        $wpdb->insert(
            $this->tableName,
            [
                'attachment_id' => $item->getAttachmentId(),
                'status' => $item->getStatus(),
                'provider' => $item->getProvider(),
                'attempts' => $item->getAttempts(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    public function update(QueueItem $item): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table operation for queue management, caching not appropriate for real-time queue data
        return $wpdb->update(
            $this->tableName,
            [
                'status' => $item->getStatus(),
                'attempts' => $item->getAttempts(),
                'error_message' => $item->getErrorMessage(),
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $item->getId()],
            ['%s', '%d', '%s', '%s'],
            ['%d']
        ) !== false;
    }

    public function getNextPending(int $limit = 10): array {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->tableName}
                 WHERE status = 'pending'
                 ORDER BY created_at ASC
                 LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return array_map([$this, 'hydrate'], $results);
    }

    public function find(int $id): ?QueueItem {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $result ? $this->hydrate($result) : null;
    }

    public function delete(int $id): bool {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table operation for queue management, caching not appropriate for real-time queue data
        return (bool) $wpdb->delete(
            $this->tableName,
            ['id' => $id],
            ['%d']
        );
    }

    public function getQueueCount(): int {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE status = %s",
                'pending'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    private function hydrate(array $data): QueueItem {
        $item = new QueueItem(
            (int) $data['attachment_id'],
            $data['provider'],
            $data['status'],
            (int) $data['id']
        );

        return $item;
    }
}
