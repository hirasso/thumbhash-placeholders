<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WP\ThumbhashPlaceholders;

use Hirasso\WP\ThumbhashPlaceholders\WPCLI\Commands\ClearCommand;
use Hirasso\WP\ThumbhashPlaceholders\WPCLI\WPCLIApplication;
use Hirasso\WP\ThumbhashPlaceholders\WPCLI\Commands\GenerateCommand;
use WP_Post;
use WP_Error;

class Plugin
{
    public const META_KEY = '_thumbhash';
    public const TEXT_DOMAIN = 'thumbhash-placeholders';

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Hook for generating Thumbhash on upload
        add_action('add_attachment', [self::class, 'generateThumbhash']);
        add_action('plugins_loaded', [self::class, 'loadTextDomain']);

        new WPCLIApplication(
            'thumbhash',
            [
                GenerateCommand::class,
                ClearCommand::class,
            ]
        );

        Admin::init();
    }

    /**
     * Load the plugin text domain manually, so that it prevails over the folder name (it's "scoped" during tests)
     */
    public static function loadTextDomain(): void {
        load_plugin_textdomain('thumbhash-placeholders', false, static::getAssetPath('/languages'));
    }

    /**
     * Generate and attach a thumbhash for an image
     */
    public static function generateThumbhash(
        int $attachmentID
    ): bool|WP_Error {
        if (!wp_attachment_is_image($attachmentID)) {
            return new WP_Error('not_an_image', sprintf(
                /* translators: %s is a path to a file */
                __('File is not an image: %d', 'thumbhash-placeholders'),
                esc_html($attachmentID)
            ));
        }

        $mimeType = get_post_mime_type($attachmentID);
        $file = get_attached_file($attachmentID);

        /** @var ImageDownloader|null $downloader */
        $downloader = null;
        if (!file_exists($file)) {
            $downloader = new ImageDownloader($mimeType);
            $file = $downloader->download(wp_get_attachment_url($attachmentID));
        }

        if (is_wp_error($file)) {
            return $file;
        }

        $hash = Thumbhash::encode($file, $mimeType);

        $downloader?->destroy();

        if (is_wp_error($hash)) {
            return $hash;
        }

        update_post_meta($attachmentID, static::META_KEY, $hash);
        return true;
    }

    /**
     * Get the thumbhash value for an attachment
     */
    public static function getPlaceholder(int|WP_Post $post): ?Placeholder
    {
        $attachmentID = $post->ID ?? $post;

        if (!wp_attachment_is_image($attachmentID)) {
            return null;
        }

        $hash = get_post_meta($attachmentID, Plugin::META_KEY, true);
        if (!is_string($hash) || empty($hash)) {
            return null;
        }

        $uri = Thumbhash::getDataURI($hash);

        if (is_wp_error($uri)) {
            return null;
        }

        return new Placeholder(
            hash: $hash,
            dataURI: esc_url($uri, ['data'])
        );
    }

    /**
     * Get the path to a plugin file
     */
    public static function getAssetPath(string $path): string
    {
        return THUMBHASH_PLACEHOLDERS_PLUGIN_DIR . '/' . ltrim($path, '/');
    }

    /**
     * Helper function to get versioned asset urls
     */
    public static function getAssetURI(string $path): string
    {
        $uri = THUMBHASH_PLACEHOLDERS_PLUGIN_URI . '/' . ltrim($path, '/');
        $file = static::getAssetPath($path);

        if (file_exists($file)) {
            $version = filemtime($file);
            $uri .= "?v=$version";
        }

        return $uri;
    }
}
