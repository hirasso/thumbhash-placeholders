<?php

namespace Hirasso\WPThumbhash;

enum ImageDriver: string
{
    case IMAGICK = 'imagick';
    case GD = 'gd';
}
