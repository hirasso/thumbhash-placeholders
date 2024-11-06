<?php

namespace Hirasso\WP\ComposerActions;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Script\Event;

class ScopedPackage
{
    /**
     * Used to post process the scoped release package.
     * Currently, the project's composer.json is being copied
     * to the scoped folder and all dependencies are being removed
     * from it. This should help with better collision prevention 🤞
     */
    public static function postProcess(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        $io->write("Postprocessing scoped package...");

        $composerJSON = static::generateComposerJSON($io);
        $scopedJSONFile = new JsonFile('scoped/composer.json');
        $scopedJSONFile->write($composerJSON);

        $io->write("Postprocessing complete!");
    }

    private static function generateComposerJSON(IOInterface $io): array
    {

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
        ];

        foreach ($removeProperties as $key) {
            unset($composerJSON[$key]);
        }

        return $composerJSON;
    }
}
