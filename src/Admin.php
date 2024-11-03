<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\WPThumbhash;

use Hirasso\WPThumbhash\Enums\AdminContext;
use WP_Post;

class Admin
{
    protected static $ajaxAction = 'generate_thumbhash';

    public static function init()
    {
        add_filter('attachment_fields_to_edit', [static::class, 'attachmentFieldsToEdit'], 10, 2);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAssets']);
        add_action('wp_ajax_' . static::$ajaxAction, [static::class, 'wpAjaxGenerateThumbhash']);
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets(): void
    {
        wp_enqueue_style('wp-thumbhash-admin', self::assetUri('/admin/wp-thumbhash.css'), [], null);
        wp_enqueue_script('wp-thumbhash-admin', self::assetUri('/admin/wp-thumbhash.js'), ['jquery'], null, true);
        wp_localize_script('wp-thumbhash-admin', 'wpThumbhash', [
            'ajax' => [
                'url' => admin_url('admin-ajax.php'),
                'action' => static::$ajaxAction,
                'nonce' => wp_create_nonce(static::$ajaxAction),
            ],
        ]);
    }

    /**
     * Helper function to get versioned asset urls
     */
    private static function assetUri(string $path): string
    {
        $uri = WP_THUMBHASH_PLUGIN_URI . '/' . ltrim($path, '/');
        $file = WP_THUMBHASH_PLUGIN_DIR . '/' . ltrim($path, '/');

        if (file_exists($file)) {
            $version = filemtime($file);
            $uri .= "?v=$version";
        }
        return $uri;
    }

    /**
     * Render the placeholder field
     * Uses a custom element for simple self-initialization
     */
    public static function attachmentFieldsToEdit(
        array $fields,
        WP_Post $attachment
    ): array {
        if (!wp_attachment_is_image($attachment)) {
            return $fields;
        }

        $fields['wp-thumbhash-field'] = [
            'label' => __('Placeholder'),
            'input'  => 'html',
            'html' => static::renderAttachmentField($attachment->ID, AdminContext::INITIAL),
        ];

        return $fields;
    }

    /**
     * Render the attachment field
     */
    private static function renderAttachmentField(int $id, AdminContext $context): string
    {
        $thumbhashURL = Plugin::getThumbhashValue($id)->url;
        $buttonLabel = $thumbhashURL ? __('Regenerate') : __('Generate');

        ob_start() ?>

        <wp-thumbhash-field data-id="<?= esc_attr($id) ?>">
            <?php if ($thumbhashURL): ?>
                <img class="wp-thumbhash_image" src="<?= esc_attr($thumbhashURL) ?>" alt="<?= _e('Thumbhash placeholder') ?>">
            <?php endif; ?>

            <button data-wp-thumbhash-generate type="button" class="button button-small"><?= $buttonLabel ?></button>

            <?php if ($context === AdminContext::REGENERATE): ?>
                <i aria-hidden="true" data-wp-thumbhash-regenerated></i>
            <?php endif; ?>

        </wp-thumbhash-field>

<?php return ob_get_clean();
    }

    /**
     * (Re-)generate the thumbhash via AJAX.
     * Return the updated attachment field on success
     */
    public static function wpAjaxGenerateThumbhash(): void
    {
        check_ajax_referer(static::$ajaxAction, 'security');

        $id = $_POST['id'] ?? null;

        if (empty($id) || !is_numeric($id)) {
            wp_send_json_error([
                'message' => 'Invalid id provided',
            ]);
        }

        Plugin::generateThumbhash($id);

        wp_send_json_success([
            'html' => static::renderAttachmentField($id, AdminContext::REGENERATE),
        ]);
    }
}
