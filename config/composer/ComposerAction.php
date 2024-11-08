<?php

namespace Hirasso\WP\ComposerActions;

use Composer\Json\JsonFile;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Exception;
use Symfony\Component\VarDumper\VarDumper;

/**
 * The base class for all composer actions.
 * Provides various utility functions to be used in the actions
 */
class ComposerAction
{
    /**
     * Dump
     */
    protected static function dump(...$vars)
    {
        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
    }

    /**
     * Dump and die
     */
    protected static function dd(...$vars)
    {
        static::dump(...$vars);
        exit(1);
    }

    /**
     * Get the root dir name, e.g. "my-plugin"
     */
    protected static function getRootDir(Event $event)
    {
        $composer = $event->getComposer();
        $rootDir = $composer->getConfig()->get('vendor-dir');
        return dirname($rootDir);
    }

    /**
     * Get the contents of a composer.json
     */
    protected static function getComposerJSON(string $dir)
    {
        $filePath = "$dir/composer.json";

        if (!file_exists($filePath)) {
            throw new Exception('composer.json not found at root directory');
        }

        // Load the composer.json
        $jsonFile = new JsonFile($filePath);
        return $jsonFile->read();
    }

    /**
     * Rename a folder
     */
    public static function renameFolder(string $oldName, string $newName)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($newName);
        $filesystem->rename($oldName, $newName);
    }

    /**
     * Remove one or multiple folders or files
     */
    public static function remove(...$args): bool
    {
        $filesystem = new Filesystem();
        foreach ($args as $arg) {
            if (!$filesystem->remove($arg)) {
                return false;
            }
        }
        return true;
    }
}
