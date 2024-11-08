<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

// You can do your own things here, e.g. collecting symbols to expose dynamically
// or files to exclude.
// However beware that this file is executed by PHP-Scoper, hence if you are using
// the PHAR it will be loaded by the PHAR. So it is highly recommended to avoid
// to auto-load any code here: it can result in a conflict or even corrupt
// the PHP-Scoper analysis.

// Example of collecting files to include in the scoped build but to not scope
// leveraging the isolated finder.
// $excludedFiles = array_map(
//     static fn (SplFileInfo $fileInfo) => $fileInfo->getPathName(),
//     iterator_to_array(
//         Finder::create()->files()->in(__DIR__),
//         false,
//     ),
// );
$excludedFiles = [];
$copyFiles = [
    'placeholders.php',
    'readme.txt',
    'admin/placeholders.js',
    'admin/placeholders.css',
    'composer.json',
    'composer.lock',
];
/**
 * @see https://github.com/humbug/php-scoper/blob/main/docs/further-reading.md#wordpress-support
 */
function getWpExcludedSymbols(string $fileName): array
{
    $filePath = __DIR__ . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated/' . $fileName;

    return json_decode(
        file_get_contents($filePath),
        true,
    );
}

$wpClasses   = getWpExcludedSymbols('exclude-wordpress-classes.json');
$wpFunctions = getWpExcludedSymbols('exclude-wordpress-functions.json');
$wpConstants = getWpExcludedSymbols('exclude-wordpress-constants.json');


return [
    // The prefix configuration. If a non-null value is used, a random prefix
    // will be generated instead.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#prefix
    'prefix' => 'HirassoScoped',

    // The base output directory for the prefixed files.
    // This will be overridden by the 'output-dir' command line option if present.
    // 'output-dir' => 'scoped',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // This configuration entry is completely ignored when using Box.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#finders-and-paths
    'finders' => [
        // Finder::create()->files()->in('./src'),
        // Finder::create()->files()->in('./vendor')->name('autoload.php'),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->name(['*.php', '*.md', 'LICENSE', '*.json'])
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('vendor'),
            // ->append(['composer.json', 'composer.lock']),
    ],

    // List of excluded files, i.e. files for which the content will be left untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'exclude-files' => [
        // 'src/an-excluded-file.php',
        ...$excludedFiles,
        ...$copyFiles,
    ],

    // PHP version (e.g. `'7.2'`) in which the PHP parser and printer will be configured into. This will affect what
    // level of code it will understand and how the code will be printed.
    // If none (or `null`) is configured, then the host version will be used.
    'php-version' => '8.2',

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'patchers' => [
        static function (string $filePath, string $prefix, string $contents): string {
            // Change the contents here.
            // var_dump(compact('filePath', 'prefix'));

            return $contents;
        },
    ],

    // List of symbols to consider internal i.e. to leave untouched.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#excluded-symbols
    'exclude-namespaces' => [
        'Hirasso\\WP\\Placeholders', // The Acme\Foo namespace (and sub-namespaces),
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'exclude-classes' => [
        ...$wpClasses, 'WP_CLI',
    ],
    'exclude-functions' => [
        ...$wpFunctions,
    ],
    'exclude-constants' => [
        ...$wpConstants, 'true', 'false',
    ],

    // List of symbols to expose.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols
    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
    'expose-namespaces' => [
        'Hirasso\\WP\\Placeholders', // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
