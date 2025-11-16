<?php
/**
 * PSR-4 Autoloader
 *
 * Automatically loads plugin classes based on namespace.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // Base namespace for the plugin
    $namespace = 'AIImageTagger\\';

    // Base directory for namespace
    $baseDir = AI_IMAGE_TAGGER_PLUGIN_DIR . 'src/';

    // Check if the class uses the namespace prefix
    $len = strlen($namespace);
    if (strncmp($namespace, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separators with directory separators
    // and append .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
