<?php

namespace Hirasso\WP\Placeholders\Enums;

enum ImageDriver: string
{
    case IMAGICK = 'imagick';
    case GD = 'gd';
}
