<?php
/**
 * Uninstall script for AI Image Tagger
 *
 * Runs when plugin is deleted via WordPress admin
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete custom tables
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Required for plugin uninstall
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_processing_queue");
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Required for plugin uninstall
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_processing_log");

// Delete all plugin options
delete_option('ai_image_tagger_settings');
delete_option('ai_image_tagger_db_version');

// Delete all post meta created by plugin
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for plugin uninstall cleanup
$wpdb->query("DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_ai_%'");

// Delete taxonomy and terms
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for plugin uninstall cleanup
$term_ids = $wpdb->get_col(
    "SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'ai_image_tag'"
);

foreach ($term_ids as $term_id) {
    wp_delete_term($term_id, 'ai_image_tag');
}

// Clear any cached data
wp_cache_flush();
