<?php

namespace Hirasso\WP\ComposerActions;

use Composer\Json\JsonFile;
use Composer\Script\Event;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ScopedPackage extends ComposerAction
{
    /**
     * Post process the scoped release package.
     * Currently, the project's composer.json is being copied
     * to the scoped folder and all dependencies are being removed
     * from it. This should help with better collision prevention 🤞
     */
    public static function postProcess(Event $event)
    {
        $io = $event->getIO();

        if (!is_dir('build')) {
            $io->error("The build folder doesn't exist");
        }

        $io->write("<info>Postprocessing scoped package...</info>");

        $rootDir = static::getRootDir($event);
        $composerJSON = static::getComposerJSON($rootDir);

        /**
         * Cleanup stuff not required in the release
         */
        $cleanedUp = static::remove(
            "$rootDir/build/composer.json",
            "$rootDir/build/composer.lock",
            "$rootDir/build/vendor/sniccowp/php-scoper-wordpress-excludes"
        );

        $io->write("<info>✔︎ Cleaned up scoped folder</info>");

        /**
         * Generate a scoped composer.json, without dependencies
         * This could come in handy for a scoped release to packagist
         */
        static::createScopedComposerJSON($composerJSON, "$rootDir/build/composer.json");
        $io->write("<info>✔︎ Created scoped composer.json</info>");

        /**
         * Create prefixed src folder
         */
        exec('rm -rf build/src');
        exec("cp -rf src build/src");

        foreach (static::patchDirectory($composerJSON, "$rootDir/build/src") as $file) {
            $io->write("<info>✔︎ patched $file</info>");
        }


        /**
         * Finally, rename scoped to whatever the root dir is called.
         * `my-plugin/scoped` will become `my-plugin/my-plugin`
         */
        $newName = basename($rootDir);
        static::renameFolder("$rootDir/build", $newName);
        $io->write("<info>✔︎ Renamed scoped folder from 'build' top '$newName'</info>");
    }

    /**
     * Create a new scoped JSON file and save it at the provided location
     */
    private static function createScopedComposerJSON(
        mixed $composerJSON,
        string $destination
    ): array {

        $removeProperties = [
            'scripts',
            'require-dev',
            'require',
            'config',
            'autoload-dev',
            'autoload',
            'post-install-cmd',
            'scripts-descriptions',
        ];

        foreach ($removeProperties as $key) {
            unset($composerJSON[$key]);
        }

        $jsonFile = new JsonFile($destination);
        $jsonFile->write($composerJSON);

        return $composerJSON;
    }

    /**
     * Patches the src in the scoped folder.
     * this is necessary as php-scoper breaks it.
     */
    protected static function patchDirectory(
        array $composerJSON,
        string $dir
    ): array {
        $prefixedFiles = [];
        $config = $composerJSON['extra']['patch-scoped'] ?? null;

        if (!$config) {
            return $prefixedFiles;
        }
        /** @var string $prefix */
        $prefix = $config['prefix'] ?? null;
        /** @var array $namespaces */
        $namespaces = $config['namespaces'] ?? null;

        if (!$prefix || empty($namespaces)) {
            return null;
        }

        $namespacesPattern = implode('|', $namespaces);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $contents = file_get_contents($filePath);


                // Prefix all lines that start with "use ExternalNamespace..."
                $updatedContents = preg_replace(
                    "/^use\s+($namespacesPattern)\\\\/m",
                    "use $prefix\\\\$1\\\\",
                    $contents
                );

                if ($updatedContents !== $contents) {
                    file_put_contents($filePath, $updatedContents);
                    $prefixedFiles[] = $filePath;
                }
            }
        }
        return $prefixedFiles;
    }
}
