<?php

namespace Hirasso\ThumbhashPlaceholders;

/**
 * The normalized Placeholder object, for use in the frontend
 */
final readonly class Placeholder
{
    public function __construct(
        public string $hash,
        public string $dataURI
    ) {
    }
}
