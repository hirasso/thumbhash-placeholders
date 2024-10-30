<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

use WP_CLI;
use WP_Query;
use WP_Post;

class Plugin
{
    private const META_KEY = '_wp_thumbhash';

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
            \WP_CLI::add_command('thumbhash cleanup', [self::class, 'cleanupThumbhashCommand']);
        }

        Admin::init();
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
        $thumbhash = Thumbhash::encode($attachmentID);
        if ($thumbhash) {
            update_post_meta($attachmentID, static::META_KEY, $thumbhash);
            return true;
        }
        return false;
    }

    /**
     * Get the thumbhash URL for an attachment
     */
    public static function getThumbhashURL(int|WP_Post $post): ?string
    {
        $attachmentID = $post->ID ?? $post;
        if (!wp_attachment_is_image($attachmentID)) {
            return null;
        }
        $hash = get_post_meta($attachmentID, static::META_KEY, true);
        return Thumbhash::decode($hash);
    }

    /**
     * Generate thumbhash data for all images
     */
    public static function generateThumbhashCommand(
        array $positionals,
        array $flags
    ): void {
        $options = (object) wp_parse_args($flags, [
            'force' => false,
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
                    'compare' => 'NOT EXISTS',
                ],
            ],
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

        $count = 0;
        foreach ($query->posts as $id) {
            $thumbhash = static::generateThumbhash($id);
            if ($thumbhash) {
                WP_CLI::log("Generated thumbhash for attachment ID: $id");
                $count++;
            } else {
                WP_CLI::warning("Something went wrong while generating a thumbhash for attachment: $id");
            }
        }

        WP_CLI::success("Thumbhash placeholders generated for $count images.");
    }

    public static function cleanupThumbhashCommand(
        array $positionals,
        array $flags
    ): void {
        $options = (object) wp_parse_args($flags, [
            'ids' => '',
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
                    'compare' => 'EXISTS',
                ],
            ],
        ];
        if (!empty($options->ids)) {
            $queryArgs['post__in'] = array_map('absint', array_map('trim', explode(',', $options->ids)));
        }

        $query = new WP_Query($queryArgs);

        if (!$query->have_posts()) {
            WP_CLI::success('No images with Thumbhash placeholders found.');
            return;
        }
        $count = 0;
        foreach ($query->posts as $id) {
            delete_post_meta($id, static::META_KEY);
            WP_CLI::log("Removed thumbhash for attachment ID: $id");
            $count++;
        }

        WP_CLI::success("Thumbhash placeholders removed for $count images.");
    }
}
