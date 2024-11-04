<?php

namespace Hirasso\ThumbhashPlaceholders\WPCLI;

use Snicco\Component\BetterWPCLI\WPCLIApplication as SniccoWPCLIApplication;
use Snicco\Component\BetterWPCLI\CommandLoader\ArrayCommandLoader;

class WPCLIApplication
{
    public function __construct(
        private string $namespace,
        array $command_classes
    ) {

        if (!defined('WP_CLI')) {
            return;
        }

        $command_loader = new ArrayCommandLoader($command_classes, fn (string $class) => new $class());
        $application = new SniccoWPCLIApplication($this->namespace, $command_loader);
        $application->registerCommands();
    }
}
