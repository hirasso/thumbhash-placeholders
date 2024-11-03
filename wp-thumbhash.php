<?php

/**
 * Plugin Name
 *
 * @package           WPThumbhash
 * @author            Rasso Hilber
 * @copyright         2024 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: WP Thumbhash
 * Description: Enhance your lazy-loaded images with thumbhash placeholders
 * Version: 0.0.1
 * Requires PHP: 8.2
 * Requires at least: 5.8
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: wp-thumbhash
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
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
