<?php

namespace Hirasso\WPThumbhash;

use Exception;
use Thumbhash\Thumbhash as ThumbhashLib;
use WP_Image_Editor;
use WP_Error;

use function Thumbhash\extract_size_and_pixels_with_gd;
use function Thumbhash\extract_size_and_pixels_with_imagick;

class Thumbhash
{
    /**
     * Generate a thumbhash from an attachment
     */
    public static function fromAttachment(int $id): string
    {
        $file = static::getImage($id);

        if (!wp_attachment_is_image($id)) {
            throw new Exception("File is not an image: $id");
        }

        /** @var WP_Image_Editor|WP_Error */
        $editor = wp_get_image_editor($file);

        if (is_wp_error($editor)) {
            throw new Exception($editor->get_error_message());
        }

        $thumb = static::getThumb($editor, get_post_mime_type($id));

        [$width, $height, $pixels] = match (static::getImageDriver($editor)) {
            ImageDriver::IMAGICK => extract_size_and_pixels_with_imagick($thumb),
            ImageDriver::GD => extract_size_and_pixels_with_gd($thumb),
            default => throw new Exception("Couldn't generate thumbhash data")
        };

        $hash = ThumbhashLib::RGBAToHash($width, $height, $pixels);
        return ThumbhashLib::convertHashToString($hash);
    }

    /**
     * Get an image. Attempt to download it if it doesn't exist locally
     */
    private static function getImage(int $id): string
    {
        $file = get_attached_file($id);

        if (file_exists($file)) {
            return $file;
        }

        $file = ImageDownloader::downloadImage(wp_get_attachment_url($id));

        if (!file_exists($file)) {
            throw new Exception("File doesn't exist: $id");
        }

        return $file;
    }

    /**
     * Get a resized version of an image
     */
    private static function getThumb(WP_Image_Editor $editor, string $mimeType): string
    {
        $editor->resize(32, 32, false);
        ob_start();
        $editor->stream($mimeType);
        return ob_get_clean();
    }

    /**
     * Get the current image driver
     */
    private static function getImageDriver(WP_Image_Editor $editor): ImageDriver
    {
        $className = $editor::class;
        return match ($className) {
            'WP_Image_Editor_Imagick' => ImageDriver::IMAGICK,
            'WP_Image_Editor_GD' => ImageDriver::GD,
            default => throw new Exception("Unsupported image driver: {$className}")
        };
    }
}
