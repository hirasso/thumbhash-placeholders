<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\ThumbhashPlaceholders;

use Hirasso\ThumbhashPlaceholders\Enums\AdminContext;
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
        wp_enqueue_style('thumbhash-placeholders-admin', self::assetUri('/admin/thumbhash-placeholders.css'), [], null);
        wp_enqueue_script('thumbhash-placeholders-admin', self::assetUri('/admin/thumbhash-placeholders.js'), ['jquery'], null, true);
        wp_localize_script('thumbhash-placeholders-admin', 'wpThumbhash', [
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

        $fields['thumbhash-placeholders-field'] = [
            'label' => __('Placeholder', 'thumbhash-placeholders'),
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
        $thumbhashURL = Plugin::getPlaceholder($id)->url;
        $buttonLabel = $thumbhashURL ? __('Regenerate', 'thumbhash-placeholders') : __('Generate', 'thumbhash-placeholders');

        ob_start() ?>

        <thumbhash-placeholders-field data-id="<?= esc_attr($id) ?>">
            <?php if ($thumbhashURL): ?>
                <img class="thumbhash-placeholders_image" src="<?= esc_attr($thumbhashURL) ?>" alt="<?= _e('Thumbhash placeholder') ?>">
            <?php endif; ?>

            <button data-thumbhash-placeholders-generate type="button" class="button button-small"><?= $buttonLabel ?></button>

            <?php if ($context === AdminContext::REGENERATE): ?>
                <i aria-hidden="true" data-thumbhash-placeholders-regenerated></i>
            <?php endif; ?>

        </thumbhash-placeholders-field>

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
