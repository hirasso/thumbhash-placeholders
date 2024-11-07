<?php

namespace Hirasso\WP\Placeholders\Tests\WP;

use Hirasso\WP\Placeholders\CLI\Commands\GenerateCommand;
use Hirasso\WP\Placeholders\Plugin;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

/**
 * Class Post_Duplicator.
 *
 * @coversDefaultClass \Hirasso\WP\ThumbhashPlaceholder\CLI\Commands\GenerateCommand
 */
final class GenerateCommandTest extends WPTestCase
{
    private int $attachmentID;

    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();

        $this->attachmentID = $this->factory()->attachment->create_upload_object(
            Plugin::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($this->attachmentID);
    }

    /**
     * @covers ::synopsis
     */
    public function test_synopsis()
    {
        $synopsis = GenerateCommand::synopsis();
        $this->assertTrue($synopsis->hasRepeatingPositionalArgument());

        [$ids, $force] = $synopsis->toArray();

        $this->assertEquals('ids', $ids['name']);
        $this->assertTrue($ids['repeating']);
        $this->assertTrue($ids['optional']);

        $this->assertEquals('force', $force['name']);
        $this->assertTrue($force['optional']);

        $placeholder = Plugin::getPlaceholder($this->attachmentID);

        $this->assertInstanceOf(
            'Hirasso\\WP\\Placeholders\\Placeholder',
            $placeholder
        );
    }

    /**
     * @covers ::execute
     */
    public function test_execute()
    {
        $tester = new CommandTester(new GenerateCommand());

        $tester->run([], ['force' => true]);

        $tester->assertCommandIsSuccessful();
        $tester->assertStatusCode(0);

        $tester->seeInStderr('Generating Placeholders (force: true)');
        $tester->seeInStderr('[OK]');
    }
}
