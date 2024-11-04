<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\ThumbhashPlaceholders;

use Exception;

class ImageDownloader
{
    private const DIR_NAME = 'thumbhash-placeholders';

    /**
     * Get the custom dir in /wp-content/uploads/
     */
    private static function getDir(): string
    {
        $uploadDir = wp_upload_dir();
        $dir = $uploadDir['basedir'] . '/' . static::DIR_NAME;
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    /**
     * Download a remote image and save it to the custom directory.
     */
    public static function downloadImage(string $url): string
    {
        $dir = static::getDir();
        $response = wp_remote_get($url, ['timeout' => 300]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return throw new Exception("Failed to download image: $url");
        }

        $filename = uniqid() . '-' . basename($url);
        $file = "$dir/$filename";
        $fileContents = wp_remote_retrieve_body($response);

        if (file_put_contents($file, $fileContents) === false) {
            throw new Exception("Failed to write file to directory: $dir");
        }

        return $file;
    }

    /**
     * Cleans up (deletes) images in the custom directory that are older than one hour.
     */
    public static function cleanupOldImages(int $before = MINUTE_IN_SECONDS): void
    {
        $dir = static::getDir();
        $images = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $oneHourAgo = time() - $before;

        foreach ($images as $image) {
            if (filemtime($image) < $oneHourAgo) {
                unlink($image);
            }
        }
    }
}
