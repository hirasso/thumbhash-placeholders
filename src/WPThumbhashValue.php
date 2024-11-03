<?php

namespace Hirasso\WPThumbhash;

use WP_Post;

final readonly class WPThumbhashValue
{
    /** The escaped data URL of the thumbhash placeholder */
    public ?string $url;
    /** The decoded value of the placeholder */
    public ?string $decoded;

    public function __construct(int|WP_Post $post)
    {
        $attachmentID = $post->ID ?? $post;
        if (!wp_attachment_is_image($attachmentID)) {
            $this->url = null;
            return;
        }

        $hash = get_post_meta($attachmentID, Plugin::META_KEY, true);
        if (empty($hash)) {
            $this->url = null;
            return;
        }

        $this->decoded = Thumbhash::decode($hash);
        $this->url = esc_attr($this->decoded);
    }
}
