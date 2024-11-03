<?php

/**
 * Plugin Name: WP Thumbhash
 * Description: Enhance your images with thumbhash placeholders
 * Version: 0.0.1
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Requires PHP: 8.2
 * License: GPL-3.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * GitHub Plugin URI: hirasso/wp-thumbhash
 */

use Hirasso\WPThumbhash\Plugin;
use Hirasso\WPThumbhash\WPThumbhashValue;

if (!defined('ABSPATH')) {
    exit;
}

define('WP_THUMBHASH_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
define('WP_THUMBHASH_PLUGIN_DIR', untrailingslashit(__DIR__));

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

Plugin::init();

/**
 * API functions
 */
if (!function_exists('wp_thumbhash')) {
    function wp_thumbhash(int|WP_Post $post): WPThumbhashValue
    {
        return Plugin::getThumbhashValue($post);
    }
}
