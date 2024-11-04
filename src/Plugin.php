<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\ThumbhashPlaceholders;

use Hirasso\ThumbhashPlaceholders\WPCLI\Commands\ClearCommand;
use Hirasso\ThumbhashPlaceholders\WPCLI\WPCLIApplication;
use Hirasso\ThumbhashPlaceholders\WPCLI\Commands\GenerateCommand;
use WP_Post;

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
     * Get the thumbhash value for an attachment
     */
    public static function getPlaceholder(int|WP_Post $post): Placeholder
    {
        return new Placeholder($post);
    }
}
