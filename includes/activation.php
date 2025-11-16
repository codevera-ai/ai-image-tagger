<?php
/**
 * Plugin activation functions
 *
 * @package AIImageTagger
 */

namespace AIImageTagger;

/**
 * Activate the plugin
 */
function activate() {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Create database tables
    create_queue_table();
    create_log_table();

    // Register taxonomy
    register_ai_taxonomy();
    flush_rewrite_rules();

    // Set default options
    set_default_options();

    // Schedule cron jobs
    schedule_cron_jobs();

    // Store database version
    update_option('ai_image_tagger_db_version', '1.0.0');
}

/**
 * Create processing queue table
 */
function create_queue_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_processing_queue';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        attachment_id bigint(20) UNSIGNED NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        provider varchar(50) NOT NULL,
        attempts tinyint(3) UNSIGNED DEFAULT 0,
        max_attempts tinyint(3) UNSIGNED DEFAULT 3,
        error_message text,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        processed_at datetime,
        PRIMARY KEY  (id),
        KEY idx_status (status),
        KEY idx_attachment (attachment_id),
        KEY idx_created (created_at)
    ) $charset_collate;";

    dbDelta($sql);
}

/**
 * Create processing log table
 */
function create_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_processing_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        attachment_id bigint(20) UNSIGNED NOT NULL,
        provider varchar(50) NOT NULL,
        status varchar(20) NOT NULL,
        request_data longtext,
        response_data longtext,
        error_message text,
        processing_time decimal(10,3),
        tokens_used int(10) UNSIGNED,
        cost decimal(10,4),
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY idx_attachment (attachment_id),
        KEY idx_created (created_at),
        KEY idx_status (status)
    ) $charset_collate;";

    dbDelta($sql);
}

/**
 * Register AI taxonomy
 */
function register_ai_taxonomy() {
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

/**
 * Set default options
 */
function set_default_options() {
    $default_options = [
        'default_provider' => 'openai',
        'auto_process_uploads' => false,
        'openai_api_key' => '',
        'claude_api_key' => '',
        'gemini_api_key' => '',
        'default_prompt' => '',
        'queue_enabled' => true,
        'batch_size' => 10,
        'rate_limit_per_minute' => 10,
        'image_max_dimension' => 2048,
        'image_quality' => 85,
        'retry_attempts' => 3,
        'retry_delay' => 300,
        'enable_logging' => true,
        'log_retention_days' => 30,
    ];

    add_option('ai_image_tagger_settings', $default_options);
}

/**
 * Schedule cron jobs
 */
function schedule_cron_jobs() {
    if (!wp_next_scheduled('ai_image_tagger_process_queue')) {
        wp_schedule_event(time(), 'every_five_minutes', 'ai_image_tagger_process_queue');
    }

    if (!wp_next_scheduled('ai_image_tagger_cleanup_queue')) {
        wp_schedule_event(time(), 'daily', 'ai_image_tagger_cleanup_queue');
    }

    if (!wp_next_scheduled('ai_image_tagger_cleanup_logs')) {
        wp_schedule_event(time(), 'daily', 'ai_image_tagger_cleanup_logs');
    }
}
