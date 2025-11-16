<?php
/**
 * Plugin deactivation functions
 *
 * @package AIImageTagger
 */

namespace AIImageTagger;

/**
 * Deactivate the plugin
 */
function deactivate() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('ai_image_tagger_process_queue');
    wp_clear_scheduled_hook('ai_image_tagger_cleanup_queue');
    wp_clear_scheduled_hook('ai_image_tagger_cleanup_logs');

    // Flush rewrite rules
    flush_rewrite_rules();

    // Do NOT delete data here - save for uninstall
}
