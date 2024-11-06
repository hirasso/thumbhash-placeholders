<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\WP\ThumbhashPlaceholders;

use Hirasso\WP\ThumbhashPlaceholders\Enums\AdminContext;
use WP_Post;

class Admin
{
    public static $assetHandle = 'thumbhash-placeholders';
    public static $ajaxAction = 'generate_thumbhash';

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
        // phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the version is derived from the filemtime
        wp_enqueue_style(static::$assetHandle, Plugin::getAssetURI('/admin/thumbhash-placeholders.css'), [], null);
        wp_enqueue_script(static::$assetHandle, Plugin::getAssetURI('/admin/thumbhash-placeholders.js'), ['jquery'], null, true);
        // phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_localize_script(static::$assetHandle, 'wpThumbhash', [
            'ajax' => [
                'url' => admin_url('admin-ajax.php'),
                'action' => static::$ajaxAction,
                'nonce' => wp_create_nonce(static::$ajaxAction),
            ],
        ]);
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
        $thumbhashURL = Plugin::getPlaceholder($id)?->dataURI ?: '';
        $buttonLabel = $thumbhashURL ? __('Regenerate', 'thumbhash-placeholders') : __('Generate', 'thumbhash-placeholders');

        ob_start() ?>

        <thumbhash-placeholders-field data-id="<?= esc_attr($id) ?>">
            <?php if ($thumbhashURL): ?>
                <img
                    class="thumbhash-placeholders_image"
                    src="<?php echo esc_attr($thumbhashURL) ?>"
                    alt="<?php esc_attr_e('Thumbhash placeholder', 'thumbhash-placeholders') ?>">
            <?php endif; ?>

            <button
                data-thumbhash-placeholders-generate
                type="button"
                class="button button-small">
                <?php echo esc_html($buttonLabel) ?>
            </button>

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

        $id = intval($_POST['id'] ?? null);

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
