<?php

namespace Hirasso\WP\ThumbhashPlaceholders\Enums;

enum ImageDriver: string
{
    case IMAGICK = 'imagick';
    case GD = 'gd';
}
