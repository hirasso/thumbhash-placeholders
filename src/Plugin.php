<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\WPThumbhash;

use WP_CLI;
use WP_Query;

class Plugin
{
    private const META_KEY = '_thumbhash_placeholder';

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Hook for generating Thumbhash on upload
        add_action('add_attachment', [self::class, 'generateThumbhashOnUpload']);

        // Register WP-CLI command if CLI is defined
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('thumbhash generate', [self::class, 'generateThumbhashCommand']);
        }
    }

    /**
     * Generate a thumbhash on upload
     */
    public static function generateThumbhash(
        int $attachmentID
    ): bool {
        if (!wp_attachment_is_image($attachmentID)) {
            return false;
        }
        $thumbhash = Thumbhash::fromAttachment($attachmentID);
        if ($thumbhash) {
            update_post_meta($attachmentID, static::META_KEY, $thumbhash);
            return true;
        }
        return false;
    }

    /**
     * Generate thumbhash data for all images
     */
    public static function generateThumbhashCommand(
        array $positionals,
        array $flags
    ): void {
        $options = (object) wp_parse_args($flags, [
            'force' => false
        ]);

        $queryArgs = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => static::META_KEY,
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];

        if ($options->force) {
            unset($queryArgs['meta_query']);
        }

        $query = new WP_Query($queryArgs);

        ImageDownloader::cleanupOldImages();

        if (!$query->have_posts()) {
            WP_CLI::success('No images without Thumbhash placeholders found.');
            return;
        }

        foreach ($query->posts as $count => $id) {
            $thumbhash = static::generateThumbhash($id);
            if ($thumbhash) {
                WP_CLI::log("Generated thumbhash for attachment ID: $id");
            } else {
                WP_CLI::warning("Something went wrong while generating a thumbhash for attachment: $id");
            }
        }

        WP_CLI::success("Thumbhash placeholders generated for $count images.");
    }
}
