# WP Thumbhash

A WordPress plugin to enhance your images with [thumbhash](https://evanw.github.io/thumbhash/) placeholders

## Installation

1. Install the plugin:

```shell
composer require hirasso/wp-thumbhash
```

1. Activate the plugin manually or using WP CLI:

```shell
wp plugin activate wp-thumbhash
```

## Usage

Every time you upload an image, a matching placeholder will be generated automatically. You can access it in your theme via the template function `wp_thumbhash($id)->url`:

### Markup

```php
<figure>
  <figure>
    <img src="<?= wp_thumbhash($id)->url ?>" aria-hidden="true" alt="">
    <?= wp_get_attachment_image($id) ?>
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

## Configuration

WIP :)