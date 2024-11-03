<?php

namespace Hirasso\WPThumbhash;

use WP_Post;

final readonly class WPThumbhashValue
{
    public ?string $url;

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

        $this->url = Thumbhash::decode($hash);
    }
}
