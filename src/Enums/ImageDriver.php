<?php

namespace Hirasso\ThumbhashPlaceholders\Enums;

enum ImageDriver: string
{
    case IMAGICK = 'imagick';
    case GD = 'gd';
}
