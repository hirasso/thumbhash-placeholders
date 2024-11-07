<?php

namespace Hirasso\WP\Placeholders\Tests\WP;

use Hirasso\WP\Placeholders\CLI\Commands\GenerateCommand;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

/**
 * Class Post_Duplicator.
 *
 * @coversDefaultClass \Hirasso\WP\ThumbhashPlaceholder\CLI\Commands\GenerateCommand
 */
final class CLITest extends WPTestCase
{
    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();
    }

    /**
     * @covers ::execute
     */
    public function test_generate_command()
    {
        $tester = new CommandTester(new GenerateCommand());

        // $tester->run(['placeholders', 'generate']);

        // $tester->assertCommandIsSuccessful();

        // $tester->assertStatusCode(0);

        // $tester->seeInStdout('Generating Placeholders');
        // $tester->seeInStdout('[OK]');
    }

}
