<?php

/**
 * Plugin Name
 *
 * @package           ThumbhashPlaceholders
 * @author            Rasso Hilber
 * @copyright         2024 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: ThumbHash Placeholders
 * Description: Generate image placeholders for smoother lazyloading 🎨
 * Version: 0.0.1
 * Requires PHP: 8.2
 * Requires at least: 5.8
 * Tested up to: 6.6
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: thumbhash-placeholders
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * GitHub Plugin URI: hirasso/thumbhash-placeholders
 */

use Hirasso\ThumbhashPlaceholders\Placeholder;
use Hirasso\ThumbhashPlaceholders\Plugin;

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
if (!function_exists('thumbhash')) {
    function get_thumbhash_placeholder(int|WP_Post $post): Placeholder
    {
        return Plugin::getPlaceholder($post);
    }
}
