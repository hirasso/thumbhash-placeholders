# ThumbHash Placeholders

**A WordPress plugin to generate image placeholders for smoother lazyloading 🎨**

![Tests](https://github.com/hirasso/thumbhash-placeholders/actions/workflows/tests.yml/badge.svg)

## How it works

This plugin uses [ThumbHash](https://evanw.github.io/thumbhash/) to automatically generate a small blurry placeholder image for each image during upload. That image placeholder can be rendered as a data URI string to display while the high-quality image is loading.

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
object(Hirasso\WP\ThumbhashPlaceholders\Placeholder)#2491 (1) {
  ["dataURI"]=>
  string(4218) "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAXCAYAAABqBU3hAAAMEElEQVR4AQCBAH7..."
  ["hash"]=>
  string(28) "GwgOBYAJdaaGeIi..."
}
```

### Markup

```php
<figure>
  <figure>
    <?php if (function_exists('get_thumbhash_placeholder')): ?>
      <img src="<?php echo get_thumbhash_placeholder($id)->dataURI ?>" aria-hidden="true" alt="">
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

Generate placeholders for all or selected images. Pass `--force` to re-generate.

```
wp thumbhash generate [<ids>...] [--force]
```

### `thumbhash clear`

Clear placeholders for all or selected images

```
wp thumbhash clear [<ids>...]
```