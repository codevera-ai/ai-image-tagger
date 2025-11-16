<?php
/**
 * Plugin Name: AI Image Tagger
 * Plugin URI: https://codevera.ai/ai-image-tagger
 * Description: Automatically generate titles, descriptions, and tags for media library assets using AI (OpenAI, Claude, Gemini)
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Codevera
 * Author URI: https://codevera.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-image-tagger
 * Domain Path: /languages
 */

namespace AIImageTagger;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('AI_IMAGE_TAGGER_VERSION', '1.0.0');
define('AI_IMAGE_TAGGER_PLUGIN_FILE', __FILE__);
define('AI_IMAGE_TAGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_IMAGE_TAGGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_IMAGE_TAGGER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require custom autoloader
require_once AI_IMAGE_TAGGER_PLUGIN_DIR . 'includes/autoloader.php';

// Initialize plugin
function ai_image_tagger_init() {
    Plugin::getInstance();
}
add_action('plugins_loaded', 'AIImageTagger\ai_image_tagger_init');

// Register custom cron schedules
add_filter('cron_schedules', function($schedules) {
    $schedules['every_five_minutes'] = [
        'interval' => 300,
        'display' => __('Every 5 minutes', 'ai-image-tagger')
    ];

    $schedules['every_fifteen_minutes'] = [
        'interval' => 900,
        'display' => __('Every 15 minutes', 'ai-image-tagger')
    ];

    return $schedules;
});

// Activation hook
register_activation_hook(__FILE__, function() {
    require_once AI_IMAGE_TAGGER_PLUGIN_DIR . 'includes/activation.php';
    activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    require_once AI_IMAGE_TAGGER_PLUGIN_DIR . 'includes/deactivation.php';
    deactivate();
});
