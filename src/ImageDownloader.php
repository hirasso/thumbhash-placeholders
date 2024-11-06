<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WP\ThumbhashPlaceholders;

use RuntimeException;
use WP_Filesystem_Direct;

class ImageDownloader
{
    private ?string $file = null;

    /**
     * Get the custom dir in /wp-content/uploads/
     */
    private static function getDir(): string
    {
        $uploadDir = wp_upload_dir();
        $dir = $uploadDir['basedir'] . '/' . 'thumbhash-placeholders';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    /**
     * Download a remote image and save it to the custom directory.
     */
    public function download(string $url): string
    {
        $response = wp_remote_get($url, ['timeout' => 300]);

        if (is_wp_error($response)) {
            throw new RuntimeException($response->get_error_message());
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            throw new RuntimeException(sprintf(
                __('Failed to download image. Response Code: %s'),
                esc_html($responseCode)
            ));
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

        WP_Filesystem();
        $filesystem = new WP_Filesystem_Direct(true);

        $filename = uniqid() . '-' . basename($url);
        $file = static::getDir() . "/$filename";
        $fileContents = wp_remote_retrieve_body($response);

        if ($filesystem->put_contents($file, $fileContents, FS_CHMOD_FILE) === false) {
            throw new RuntimeException('Failed to write file to uploads directory');
        }

        $this->file = $file;

        return $file;
    }

    public function destroy()
    {
        if ($this->file) {
            wp_delete_file($this->file);
        }
    }

    /**
     * Cleans up (deletes) images in the custom directory that are older than one hour.
     */
    public static function cleanupOldImages(int $age = MINUTE_IN_SECONDS): void
    {
        $images = glob(static::getDir() . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $before = time() - $age;

        foreach ($images as $image) {
            if (filemtime($image) < $before) {
                wp_delete_file($image);
            }
        }
    }
}
