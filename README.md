# Dionysos Wordpress Field
> This component is a part of the **Olympus Dionysos fields** for **WordPress**.
> It uses a duplicate `findPosts` WordPress custom modal to manage field.

```sh
composer require getolympus/olympus-dionysos-field-wordpress
```

---

[![Olympus Component][olympus-image]][olympus-url]
[![CodeFactor Grade][codefactor-image]][codefactor-url]
[![Packagist Version][packagist-image]][packagist-url]
[![MIT][license-image]][license-blob]

---

<p align="center">
    <img src="https://github.com/GetOlympus/olympus-dionysos-field-wordpress/blob/master/assets/field-wordpress-64.png" />
</p>

---

## Field initialization

Use the following lines to add a `wordpress field` in your **WordPress** admin pages or custom post type meta fields:

```php
return \GetOlympus\Dionysos\Field\Wordpress::build('my_wordpress_field_id', [
    'title'       => 'Which is your favourite post?',
    'default'     => [],
    'description' => 'Tell us which one did like this week.',
    'field'       => 'ID',
    'multiple'    => false,
    'type'        => 'post',
    'settings'    => [],

    /**
     * Texts definition
     * @see the `Texts definition` section below
     */
    't_addblock_title' => 'Click on the edit button',
    't_addblock_description' => 'Click on the "+" button to add your item.',
    't_addblocks_description' => 'Click on the "+" button to add a new item.',
    't_addblock_label' => 'Add',
    't_editblock_label' => 'Edit',
    't_removeblock_label' => 'Remove',

    't_modaltitle_label' => 'Choose a content',
    't_modalclose_label' => 'Close',
    't_modalsearch_label' => 'Search',
    't_modalsubmit_label' => 'Select',

    't_ajaxerror_label' => 'No item found',
]);
```

## Variables definitions

| Variable      | Type    | Default value if not set | Accepted values |
| ------------- | ------- | ------------------------ | --------------- |
| `title`       | String  | `'Code'` | *empty* |
| `default`     | Array   | *empty* | *empty* |
| `description` | String  | *empty* | *empty* |
| `field`       | String  | `ID` | depends on `type` value |
| `multiple`    | Boolean | `false` | `true` or `false` |
| `type`        | String  | `post` | see [Accepted type](#accepted-type) |
| `settings`    | Array   | *empty* | depends on `type` value |

Notes:
* Set `multiple` to `true` to display checkboxes instead of radio buttons
* `field` variable is used to let you retrieve the data you need, depending on `type` value (for example: in the `'type' => 'term'` case, you'll get `term_id` by default)

## Texts definition

| Code | Default value | Definition |
| ---- | ------------- | ---------- |
| `t_addblock_title` | Click on the edit button | Used as a notice to help users when there is no label yet |
| `t_addblock_description` | Click on the "+" button to add your item. | Used as a notice to help users in single format |
| `t_addblocks_description` | Click on the "+" button to add a new item. | Used as a notice to help users in multiple format |
| `t_addblock_label` | Add | Add button label |
| `t_editblock_label` | Edit | Edit button label |
| `t_removeblock_label` | Remove | Remove button label |
| `t_modaltitle_label` | Choose a content | Modal title |
| `t_modalclose_label` | Close | Modal close button label |
| `t_modalsearch_label` | Search | Modal search button label |
| `t_modalsubmit_label` | Select | Modal select button label |
| `t_ajaxerror_label` | No item found | Error message on ajax failure |

## Accepted type

* `categories` or `category` (see `get_categories` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_categories/) for `field` and `settings` variables)
* `menus` or `menu` (see `wp_get_nav_menus` on [WordPress reference](https://developer.wordpress.org/reference/functions/wp_get_nav_menus/) for `field` and `settings` variables)
* `pages` or `page` (see `get_pages` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_pages/) for `field` and `settings` variables)
* `posts` or `post` (see `wp_get_recent_posts` on [WordPress reference](https://developer.wordpress.org/reference/functions/wp_get_recent_posts/) for `field` and `settings` variables)
* `posttypes` or `posttype` (see `get_post_types` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_post_types/) for `field` and `settings` variables)
* `tags` or `tag` (see `get_the_tags` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_the_tags/) for `field` and `settings` variables)
* `taxonomies` or `taxonomy` (see `get_taxonomies` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_taxonomies/) for `field` and `settings` variables)
* `terms` or `term` (see `get_terms` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_terms/) for `field` and `settings` variables)
* `users` or `user` (see `get_users` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_users/) for `field` and `settings` variables)

## Retrive data

Retrieve your value from Database with a simple `get_option('my_wordpress_field_id', [])` (see [WordPress reference][getoption-url]):

```php
// Get wordpress from Database
$wordpress = get_option('my_wordpress_field_id', []);

if (!empty($wordpress)) {
    echo '<ul>';

    foreach ($wordpress as $post_id) {
        echo '<li>'.get_the_title($post_id).'</li>';
    }

    echo '</ul>';
}
```

## Release History

0.0.21
- Add checks on ajax call

0.0.20
- Add wp-util WordPress integration

0.0.19
- Fix display and WordPress core functions integration

## Contributing

1. Fork it (<https://github.com/GetOlympus/olympus-dionysos-field-wordpress/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

---

**Built with ♥ by [Achraf Chouk](https://github.com/crewstyle "Achraf Chouk") ~ (c) since a long time.**

<!-- links & imgs dfn's -->
[olympus-image]: https://img.shields.io/badge/for-Olympus-44cc11.svg?style=flat-square
[olympus-url]: https://github.com/GetOlympus
[codefactor-image]: https://www.codefactor.io/repository/github/GetOlympus/olympus-dionysos-field-wordpress/badge?style=flat-square
[codefactor-url]: https://www.codefactor.io/repository/github/getolympus/olympus-dionysos-field-wordpress
[getoption-url]: https://developer.wordpress.org/reference/functions/get_option/
[license-blob]: https://github.com/GetOlympus/olympus-dionysos-field-wordpress/blob/master/LICENSE
[license-image]: https://img.shields.io/badge/license-MIT_License-blue.svg?style=flat-square
[packagist-image]: https://img.shields.io/packagist/v/getolympus/olympus-dionysos-field-wordpress.svg?style=flat-square
[packagist-url]: https://packagist.org/packages/getolympus/olympus-dionysos-field-wordpress