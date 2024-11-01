<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

use Hirasso\WPThumbhash\WPCLI\Commands\ClearCommand;
use Hirasso\WPThumbhash\WPCLI\WPCLIApplication;
use Hirasso\WPThumbhash\WPCLI\Commands\GenerateCommand;
use WP_Post;

class Plugin
{
    public const META_KEY = '_wp_thumbhash';

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Hook for generating Thumbhash on upload
        add_action('add_attachment', [self::class, 'generateThumbhashOnUpload']);

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
}
