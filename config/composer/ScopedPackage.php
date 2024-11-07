<?php

namespace Hirasso\WP\ComposerActions;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Script\Event;
use Exception;

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

        /**
         * Cleanup stuff not required in the release
         */
        $cleanedUp = static::remove(
            "$rootDir/build/composer.json",
            "$rootDir/build/composer.lock",
            "$rootDir/build/vendor/sniccowp/php-scoper-wordpress-excludes"
        );
        if (!$cleanedUp) {
            throw new Exception("Couldn't clean build folder");
        }

        $io->write("<info>✔︎ Cleaned up scoped folder</info>");

        /**
         * Rename scoped to whatever the root dir is called.
         * `my-plugin/scoped` will become `my-plugin/my-plugin`
         */
        $newName = basename($rootDir);
        static::renameFolder("$rootDir/build", $newName);

        $io->write("<info>✔︎ Renamed scoped folder from 'build' top '$newName'</info>");

        /**
         * Generate a scoped composer.json, without dependencies
         * This could come in handy for a scoped release to packagist
         */
        $composerJSON = static::generateScopedComposerJSON($io);
        $jsonFile = new JsonFile("$rootDir/$newName/composer.json");
        $jsonFile->write($composerJSON);

        $io->write("<info>✔︎ Created scoped composer.json</info>");
    }

    private static function generateScopedComposerJSON(IOInterface $io): array
    {
        if (!file_exists('composer.json')) {
            throw new Exception('composer.json not found at root directory');
        }

        // Load the composer.json
        $jsonFile = new JsonFile('composer.json');
        $composerJSON = $jsonFile->read();

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

        return $composerJSON;
    }
}
