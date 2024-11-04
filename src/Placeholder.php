<?php

namespace Hirasso\ThumbhashPlaceholders;

use WP_Post;

/**
 * The normalized Placeholder object, for use in the frontend
 */
final readonly class Placeholder
{
    /**
     * The escaped data URL of the thumbhash placeholder
     */
    public ?string $dataURI;

    /**
     * The raw hash. Could be used for client-side decoding
     */
    public ?string $hash;

    public function __construct(int|WP_Post $post)
    {
        $attachmentID = $post->ID ?? $post;

        $this->hash = $this->getHash($attachmentID);

        $uri = Thumbhash::getDataURI($this->hash);
        $this->dataURI = esc_url($uri, ['data']);
    }

    /**
     * Get the hash from post_meta
     */
    private function getHash(int $attachmentID): ?string
    {
        if (!wp_attachment_is_image($attachmentID)) {
            return null;
        }

        $hash = get_post_meta($attachmentID, Plugin::META_KEY, true);

        return !is_string($hash) || empty($hash) ? null : $hash;
    }
}
