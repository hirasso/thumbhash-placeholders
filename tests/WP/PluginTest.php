<?php

namespace Hirasso\WP\Placeholders\Tests\WP;

use Hirasso\WP\Placeholders\Plugin;

/**
 * Class Post_Duplicator.
 *
 * @coversDefaultClass \Hirasso\WP\ThumbhashPlaceholder\Plugin
 */
final class PluginTest extends WPTestCase
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
        $this->assertHasAction(
            'add_attachment',
            [Plugin::class, 'generateThumbhash']
        );

        $attachmentID = $this->factory()->attachment->create_upload_object(
            Plugin::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $placeholder = Plugin::getPlaceholder($attachmentID);

        $this->assertInstanceOf(
            'Hirasso\\WP\\Placeholders\\Placeholder',
            $placeholder
        );

        $this->assertEquals(FIXTURES_EXPECTED_HASH, $placeholder->hash);
        $this->assertEquals(FIXTURES_EXPECTED_DATA_URI, $placeholder->dataURI);
    }

    /**
     * Test whether a placeholder is being created from the attachment URL
     * if the attached file cannot be found
     *
     * @covers ::generateThumbhash
     */
    public function test_generateThumbhashWithRemoteImage()
    {
        $attachmentID = $this->factory()->attachment->create_upload_object(
            Plugin::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $expectedHash = Plugin::getPlaceholder($attachmentID)->hash;
        delete_post_meta($attachmentID, '_thumbhash');

        /** Filter the attached file name so that it can't be found */
        add_filter(
            'get_attached_file',
            fn ($file) => 'i-do-not-exist.jpg'
        );

        /** Required for internal remote_get calls in docker */
        add_filter(
            'wp_get_attachment_url',
            fn ($url) => str_replace('//localhost', '//host.docker.internal', $url)
        );

        Plugin::generateThumbhash($attachmentID);
        $placeholder = Plugin::getPlaceholder($attachmentID);

        $this->assertEquals(
            $expectedHash,
            $placeholder->hash
        );
    }

    /**
     * Test getPlaceholderImage
     *
     * @covers ::getPlaceholderImage
     */
    public function test_getPlaceholderImage()
    {
        $attachmentID = $this->factory()->attachment->create_upload_object(
            Plugin::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $image = Plugin::getPlaceholderImage($attachmentID);

        $this->assertEquals(FIXTURES_EXPECTED_PLACEHOLDER_IMAGE, $image);
    }
}
