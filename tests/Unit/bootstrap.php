<?php

/**
 * PHPUnit Unit test bootstrap file
 */

namespace Hirasso\WP\Placeholders\Tests\Unit;

\define('OBJECT', 'OBJECT');
\define('ARRAY_A', 'ARRAY_A');
\define('ARRAY_N', 'ARRAY_N');

\define('PLACEHOLDERS_PLUGIN_URI', '/wp-content/plugins/placeholders');
\define('PLACEHOLDERS_PLUGIN_DIR', '/var/www/html/wp-content/plugins/placeholders');

if (\file_exists(\dirname(__DIR__, 2) . '/vendor/autoload.php') === false) {
    echo \PHP_EOL, 'ERROR: Run `composer install` to generate the autoload files before running the unit tests.', \PHP_EOL;
    exit(1);
}

require_once dirname(__DIR__, 2) . '/vendor/yoast/wp-test-utils/src/BrainMonkey/bootstrap.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
