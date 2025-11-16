<?php

namespace AIImageTagger;

use AIImageTagger\Admin\SettingsPage;
use AIImageTagger\Admin\MediaMetabox;
use AIImageTagger\Admin\MediaLibraryColumns;
use AIImageTagger\Admin\BulkActions;
use AIImageTagger\Controllers\MediaController;
use AIImageTagger\Controllers\SettingsController;
use AIImageTagger\Controllers\QueueController;
use AIImageTagger\Queue\JobScheduler;
use AIImageTagger\Services\SearchService;
use AIImageTagger\Services\EncryptionService;
use AIImageTagger\Storage\SettingsRepository;
use AIImageTagger\Storage\QueueRepository;
use AIImageTagger\Storage\MetadataRepository;
use AIImageTagger\Services\ValidationService;
use AIImageTagger\Services\ProcessingService;
use AIImageTagger\API\ProviderFactory;
use AIImageTagger\API\RestAPI;
use AIImageTagger\Queue\QueueManager;
use AIImageTagger\Queue\QueueWorker;

class Plugin {

    private static ?Plugin $instance = null;

    private MediaController $mediaController;
    private SettingsController $settingsController;
    private QueueController $queueController;
    private JobScheduler $jobScheduler;
    private SearchService $searchService;

    private function __construct() {
        $this->loadDependencies();
        $this->initializeComponents();
        $this->registerHooks();
    }

    public static function getInstance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadDependencies(): void {
        // Load required files - autoloaded via Composer
    }

    private function initializeComponents(): void {
        // Initialize encryption and settings first
        $encryption = new EncryptionService();
        $settingsRepo = new SettingsRepository($encryption);
        $metadataRepo = new MetadataRepository($settingsRepo);
        $queueRepo = new QueueRepository();
        
        // Initialize provider factory
        $providerFactory = new ProviderFactory($settingsRepo, $encryption);
        
        // Initialize services
        $validator = new ValidationService();
        $processor = new ProcessingService($providerFactory, $metadataRepo, $validator);
        
        // Initialize queue
        $queueManager = new QueueManager($queueRepo, $settingsRepo);
        $queueWorker = new QueueWorker($queueRepo, $processor);
        
        // Initialize controllers
        $this->mediaController = new MediaController($queueManager, $processor, $settingsRepo);
        $this->settingsController = new SettingsController($settingsRepo, $providerFactory);
        $this->queueController = new QueueController($queueManager, $queueRepo);
        $this->jobScheduler = new JobScheduler($queueWorker, $settingsRepo);
        $this->searchService = new SearchService();

        // Initialize REST API
        new RestAPI($processor, $metadataRepo);
    }

    private function registerHooks(): void {
        // Admin hooks
        if (is_admin()) {
            new SettingsPage();
            new MediaMetabox();
            new MediaLibraryColumns();
            new BulkActions();
        }

        // Search enhancement
        add_filter('posts_search', [$this->searchService, 'enhanceSearch'], 10, 2);

        // Register taxonomy
        add_action('init', [$this, 'registerTaxonomy']);

        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Register custom cron schedules
        add_filter('cron_schedules', [$this, 'customCronSchedules']);
        
        // Register cron hooks
        add_action('ai_image_tagger_process_queue', [$this->jobScheduler, 'processQueue']);
        add_action('ai_image_tagger_cleanup_queue', [$this, 'cleanupQueue']);
        add_action('ai_image_tagger_cleanup_logs', [$this, 'cleanupLogs']);
    }

    public function registerTaxonomy(): void {
        register_taxonomy('ai_image_tag', 'attachment', [
            'labels' => [
                'name' => __('AI Image Tags', 'ai-image-tagger'),
                'singular_name' => __('AI Image Tag', 'ai-image-tagger'),
                'search_items' => __('Search AI Tags', 'ai-image-tagger'),
                'all_items' => __('All AI Tags', 'ai-image-tagger'),
                'edit_item' => __('Edit AI Tag', 'ai-image-tagger'),
                'update_item' => __('Update AI Tag', 'ai-image-tagger'),
                'add_new_item' => __('Add New AI Tag', 'ai-image-tagger'),
                'new_item_name' => __('New AI Tag Name', 'ai-image-tagger'),
                'menu_name' => __('AI Tags', 'ai-image-tagger'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'ai-image-tag'],
        ]);
    }

    public function enqueueAdminAssets(string $hook): void {
        // Plugin admin pages
        $plugin_pages = [
            'upload.php',
            'post.php',
            'settings_page_ai-image-tagger'
        ];

        // Only load on relevant pages
        if (!in_array($hook, $plugin_pages)) {
            return;
        }

        // Enqueue brand CSS (always load first)
        wp_enqueue_style(
            'ai-image-tagger-brand',
            AI_IMAGE_TAGGER_PLUGIN_URL . 'assets/css/brand.css',
            [],
            AI_IMAGE_TAGGER_VERSION
        );

        // Enqueue admin CSS
        wp_enqueue_style(
            'ai-image-tagger-admin',
            AI_IMAGE_TAGGER_PLUGIN_URL . 'assets/css/admin.css',
            ['ai-image-tagger-brand'],
            AI_IMAGE_TAGGER_VERSION
        );

        // Enqueue appropriate JavaScript based on page
        if ($hook === 'post.php' && get_post_type() === 'attachment') {
            // Media edit page - load media-edit.js
            wp_enqueue_script(
                'ai-image-tagger-media-edit',
                AI_IMAGE_TAGGER_PLUGIN_URL . 'assets/js/media-edit.js',
                ['jquery'],
                AI_IMAGE_TAGGER_VERSION,
                true
            );

            wp_localize_script('ai-image-tagger-media-edit', 'aiImageTagger', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_image_tagger_nonce'),
                'strings' => [
                    'processing' => __('Processing...', 'ai-image-tagger'),
                    'success' => __('Metadata generated successfully', 'ai-image-tagger'),
                    'error' => __('Failed to generate metadata', 'ai-image-tagger'),
                    'confirmRegenerate' => __('Are you sure you want to regenerate the metadata?', 'ai-image-tagger'),
                ]
            ]);
        } elseif ($hook === 'upload.php') {
            // Media library page - load media-library.js
            wp_enqueue_script(
                'ai-image-tagger-media-library',
                AI_IMAGE_TAGGER_PLUGIN_URL . 'assets/js/media-library.js',
                ['jquery'],
                AI_IMAGE_TAGGER_VERSION,
                true
            );

            wp_localize_script('ai-image-tagger-media-library', 'aiImageTagger', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_image_tagger_nonce'),
                'strings' => [
                    'processing' => __('Processing...', 'ai-image-tagger'),
                    'success' => __('Metadata generated successfully', 'ai-image-tagger'),
                    'error' => __('Failed to generate metadata', 'ai-image-tagger'),
                ]
            ]);
        } else {
            // Settings and dashboard pages - load admin-settings.js
            wp_enqueue_script(
                'ai-image-tagger-admin',
                AI_IMAGE_TAGGER_PLUGIN_URL . 'assets/js/admin-settings.js',
                ['jquery'],
                AI_IMAGE_TAGGER_VERSION,
                true
            );

            wp_localize_script('ai-image-tagger-admin', 'aiImageTagger', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_image_tagger_nonce'),
                'strings' => [
                    'processing' => __('Processing...', 'ai-image-tagger'),
                    'success' => __('Metadata generated successfully', 'ai-image-tagger'),
                    'error' => __('Failed to generate metadata', 'ai-image-tagger'),
                    'confirmRegenerate' => __('Are you sure you want to regenerate the metadata?', 'ai-image-tagger'),
                    'testing' => __('Testing...', 'ai-image-tagger'),
                    'testConnection' => __('Test connection', 'ai-image-tagger'),
                    'confirmDelete' => __('Are you sure you want to delete this API key?', 'ai-image-tagger'),
                ]
            ]);
        }
    }

    public function customCronSchedules(array $schedules): array {
        $schedules['every_five_minutes'] = [
            'interval' => 300,
            'display' => __('Every 5 minutes', 'ai-image-tagger')
        ];

        $schedules['every_fifteen_minutes'] = [
            'interval' => 900,
            'display' => __('Every 15 minutes', 'ai-image-tagger')
        ];

        return $schedules;
    }

    public function cleanupQueue(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_processing_queue';

        // Delete completed items older than 7 days
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table}
                 WHERE status = %s
                 AND processed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)",
                'completed'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // Delete failed items older than 30 days
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table}
                 WHERE status = %s
                 AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)",
                'failed'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    public function cleanupLogs(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_processing_log';

        $encryption = new EncryptionService();
        $settingsRepo = new SettingsRepository($encryption);
        $retention_days = $settingsRepo->get('log_retention_days', 30);

        // Delete logs older than retention period
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table}
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $retention_days
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }
}
