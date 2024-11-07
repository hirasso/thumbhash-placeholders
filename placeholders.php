<?php

/**
 * Plugin Name
 *
 * @package           Placeholders
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
 * Text Domain: placeholders
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */

use Hirasso\WP\Placeholders\Placeholder;
use Hirasso\WP\Placeholders\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

if (! defined('PLACEHOLDERS_PLUGIN_URI')) {
    define('PLACEHOLDERS_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
}
if (! defined('PLACEHOLDERS_PLUGIN_DIR')) {
    define('PLACEHOLDERS_PLUGIN_DIR', untrailingslashit(__DIR__));
}

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

Plugin::init();

/**
 * API functions
 */
if (!function_exists('get_placeholder')) {
    function get_placeholder(int|WP_Post $post): ?Placeholder
    {
        return Plugin::getPlaceholder($post);
    }
}
