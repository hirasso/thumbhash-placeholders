<?php

namespace Hirasso\WP\ThumbhashPlaceholders\Tests\WP;

use Hirasso\WP\ThumbhashPlaceholders\Plugin;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Class Post_Duplicator.
 *
 * @coversDefaultClass \Hirasso\WP\ThumbhashPlaceholder\Plugin
 */
final class PluginTest extends TestCase
{
    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();
    }

    /**
     * Test whether a placeholder is being created on upload
     *
     * @covers ::init
     * @covers ::generateThumbhash
     * @covers ::getPlaceholder
     */
    public function test_generate_placeholder_on_upload(): void
    {
        $this->assertNotFalse(
            has_action('add_attachment', [Plugin::class, 'generateThumbhash']),
            'Does not have expected generateThumbhash action'
        );

        $attachmentID = $this->factory()->attachment->create_upload_object(
            Plugin::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $placeholder = Plugin::getPlaceholder($attachmentID);

        $this->assertInstanceOf(
            'Hirasso\\WP\\ThumbhashPlaceholders\\Placeholder',
            $placeholder
        );
    }
}
