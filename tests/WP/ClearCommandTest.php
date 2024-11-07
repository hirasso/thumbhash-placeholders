<?php

namespace Hirasso\WP\Placeholders\Tests\WP;

use Hirasso\WP\Placeholders\CLI\Commands\ClearCommand;
use Hirasso\WP\Placeholders\Plugin;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

/**
 * Class Post_Duplicator.
 *
 * @coversDefaultClass \Hirasso\WP\ThumbhashPlaceholder\CLI\Commands\ClearCommand
 */
final class ClearCommandTest extends WPTestCase
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
        $synopsis = ClearCommand::synopsis();
        $this->assertTrue($synopsis->hasRepeatingPositionalArgument());

        [$ids] = $synopsis->toArray();

        $this->assertEquals('ids', $ids['name']);
        $this->assertTrue($ids['repeating']);
        $this->assertTrue($ids['optional']);
    }

    /**
     * @covers ::execute
     */
    public function test_execute()
    {
        $tester = new CommandTester(new ClearCommand());

        $tester->run(["$this->attachmentID"]);

        $tester->assertCommandIsSuccessful();
        $tester->assertStatusCode(0);

        $tester->seeInStderr('[OK] 1 placeholder cleared');

        $placeholder = Plugin::getPlaceholder($this->attachmentID);

        $this->assertNull($placeholder);
    }
}
