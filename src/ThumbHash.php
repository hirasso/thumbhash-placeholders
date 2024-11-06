<?php

namespace Hirasso\WP\ThumbhashPlaceholders;

use Exception;
use Hirasso\WP\ThumbhashPlaceholders\Enums\ImageDriver;
use RuntimeException;
use WP_Image_Editor;
use WP_Error;
use Thumbhash\Thumbhash as ThumbhashLib;

use function Thumbhash\extract_size_and_pixels_with_gd;
use function Thumbhash\extract_size_and_pixels_with_imagick;

class ThumbHash
{
    /**
     * Generate a thumbhash from a file
     */
    public static function encode(
        string $file,
        string $mimeType
    ): string {
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf('File not found: %s', esc_html($file)));
        }

        /** @var WP_Image_Editor|WP_Error */
        $editor = wp_get_image_editor($file, [
            'mime_type' => $mimeType,
        ]);

        if (is_wp_error($editor)) {
            return $editor;
        }

        [$width, $height, $pixels] = static::extractSizeAndPixels(
            driver: static::getImageDriver($editor),
            image: static::getDownsizedImage($editor, get_post_mime_type($file, $mimeType))
        );

        $hash = ThumbhashLib::RGBAToHash($width, $height, $pixels);
        return ThumbhashLib::convertHashToString($hash);
    }

    /**
     * Decode a stored hash
     */
    public static function getDataURI(
        string $hashString
    ): string|null {
        if (empty($hashString)) {
            return null;
        }

        try {

            $hashArray = ThumbhashLib::convertStringToHash($hashString);
            return ThumbhashLib::toDataURL($hashArray);
        } catch (Exception $e) {

            throw new RuntimeException(sprintf('Error decoding thumbhash: %s', esc_html($e->getMessage())));
        }
    }

    /**
     * Get a resized version of an image
     */
    private static function getDownsizedImage(
        WP_Image_Editor $editor,
        string $mimeType
    ): string {
        $editor->resize(32, 32, false);

        // Save the image to a temporary location
        $tempFile = wp_tempnam();

        if (is_wp_error($saved = $editor->save($tempFile, $mimeType))) {
            throw new RuntimeException('Failed to save resized image.');
        }

        $file = $saved['path'];

        $fs = Utils::getFilesystem();

        // Check if the file exists and is readable
        if (!$fs->exists($file) || !$fs->is_readable($file)) {
            throw new RuntimeException('Temporary image file is not accessible.');
        }

        // Get the raw image data
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents --- this file is always local
        $imageData = $fs->get_contents($file);

        // Clean up the temporary file
        wp_delete_file($tempFile);

        return $imageData;
    }

    /**
     * Extract the size and pixels from an image
     */
    private static function extractSizeAndPixels(
        ImageDriver $driver,
        string $image
    ): array {
        return match ($driver) {
            ImageDriver::IMAGICK => extract_size_and_pixels_with_imagick($image),
            ImageDriver::GD => extract_size_and_pixels_with_gd($image),
            default => throw new RuntimeException("Couldn't generate thumbhash data")
        };
    }

    /**
     * Get the current image driver
     */
    private static function getImageDriver(
        WP_Image_Editor $editor
    ): ImageDriver {
        return match ($editor::class) {
            'WP_Image_Editor_Imagick' => ImageDriver::IMAGICK,
            'WP_Image_Editor_GD' => ImageDriver::GD,
            default => throw new RuntimeException("Unsupported image driver")
        };
    }
}
