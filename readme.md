# ThumbHash Placeholders

**Generate image placeholders of WordPress images for smoother lazyloading. 🎨**

## How it works

This plugin uses [ThumbHash](https://evanw.github.io/thumbhash/) to automatically generate a small blurry placeholder image for each image uploaded. In your frontend templates, you can access the image placeholder as a data URI string to display while the high-quality image is loading.

## Installation

1. Install the plugin:

```shell
composer require hirasso/thumbhash-placeholders
```

1. Activate the plugin manually or using WP CLI:

```shell
wp plugin activate thumbhash-placeholders
```

## Usage

### Data Structure

Access the placeholder in your templates:

```php
$placeholder = get_thumbhash_placeholder($id);
```

The placeholder object looks like this:

```
object(Hirasso\ThumbhashPlaceholders\Placeholder)#2491 (1) {
  ["url"]=>
  string(4218) "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAXCAYAAABqBU3hAAAMEElEQVR4AQCBAH7..."
}
```

### Markup

```php
<figure>
  <figure>
    <?php if (function_exists('get_thumbhash_placeholder')): ?>
      <img src="<?php echo get_thumbhash_placeholder($id)->url ?>" aria-hidden="true" alt="">
    <?php endif; ?>
    <?php echo wp_get_attachment_image($id) ?>
  </figure>
</figure>
```

### Styling

```css
figure,
figure img {
  position: relative;
}
figure img[aria-hidden="true"] {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
```

## WP-CLI Commands

### `thumbhash generate`

Generate placeholders for existing images.

```
wp thumbhash generate [<ids>...] [--force]
```

### `thumbhash clear`

Clear placeholders for all or selected images

```
wp thumbhash clear [<ids>...]
```