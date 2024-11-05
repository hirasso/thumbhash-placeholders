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
     * Instance of the Post_Duplicator class.
     *
     * @var Post_Duplicator
     */
    private $instance;

    /**
     * Setting up the instance of Post_Duplicator.
     *
     * @return void
     */
    public function set_up()
    {
        parent::set_up();

        // $this->instance = new Post_Duplicator();
    }

    /**
     * Test whether the admin page is generated correctly.
     *
     * @covers ::init
     */
    public function test_init(): void
    {
        $this->assertNotFalse(
            has_action('add_attachment', [Plugin::class, 'generateThumbhash']),
            'Does not have expected generateThumbhash action'
        );
        // dd(did_action('init'));
        // $post = $this->factory->post->create_and_get();
        // $this->assertInstanceOf('WP_Post', $post);
        // dd($post);
        // $id = $this->instance->create_duplicate( $post, [ 'copy_date' => true ] );

        // $this->assertIsInt( $id );
    }
}
