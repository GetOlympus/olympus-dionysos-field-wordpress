<p align="center">
    <img src="https://img.icons8.com/color/2x/wordpress.png">
</p>

# Wordpress Field
> This component is a part of the [**Olympus Zeus Core**][zeus-url] **WordPress** framework.

[![Olympus Component][olympus-image]][olympus-url]
[![CodeFactor Grade][codefactor-image]][codefactor-url]
[![Packagist Version][packagist-image]][packagist-url]

## Installation

Using `composer` in your PHP project:

```sh
composer require getolympus/olympus-wordpress-field
```

## Field initialization

Use the following lines to add a `wordpress field` in your **WordPress** admin pages or custom post type meta fields:

```php
return \GetOlympus\Field\Wordpress::build('my_wordpress_field_id', [
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
    't_mostused' => 'Most used',
    't_search'   => 'Search',
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
| `t_mostused` | Most used | Used as a notice to help users to user multiselect field |
| `t_search` | Search | Used as a notice to help users to user multiselect field |

## Accepted type

* `categories` or `category` (see `get_categories` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_categories/) for `field` and `settings` variables)
* `menus` or `menu` (see `wp_get_nav_menus` on [WordPress reference](https://developer.wordpress.org/reference/functions/wp_get_nav_menus/) for `field` and `settings` variables)
* `pages` or `page` (see `get_pages` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_pages/) for `field` and `settings` variables)
* `posts` or `post` (see `wp_get_recent_posts` on [WordPress reference](https://developer.wordpress.org/reference/functions/wp_get_recent_posts/) for `field` and `settings` variables)
* `posttypes` or `posttype` (see `get_post_types` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_post_types/) for `field` and `settings` variables)
* `tags` or `tag` (see `get_the_tags` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_the_tags/) for `field` and `settings` variables)
* `taxonomies` or `taxonomy` (see `get_taxonomies` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_taxonomies/) for `field` and `settings` variables)
* `terms` or `term` (see `get_terms` on [WordPress reference](https://developer.wordpress.org/reference/functions/get_terms/) for `field` and `settings` variables)

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

* 0.0.16
- [x] FIX: bug on vars description

* 0.0.15
- [x] FIX: remove twig dependency from composer

* 0.0.14
- [x] FIX: remove zeus-core dependency from composer

* 0.0.13
- [x] FIX: enhance display with an easier twig template

## Authors and Copyright

Achraf Chouk  
[![@crewstyle][twitter-image]][twitter-url]

Please, read [LICENSE][license-blob] for more information.  
[![MIT][license-image]][license-url]

<https://github.com/crewstyle>  
<https://fr.linkedin.com/in/achrafchouk>

## Contributing

1. Fork it (<https://github.com/GetOlympus/olympus-wordpress-field/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

---

**Built with ♥ by [Achraf Chouk](http://github.com/crewstyle "Achraf Chouk") ~ (c) since a long time.**

<!-- links & imgs dfn's -->
[olympus-image]: https://img.shields.io/badge/for-Olympus-44cc11.svg?style=flat-square
[olympus-url]: https://github.com/GetOlympus
[zeus-url]: https://github.com/GetOlympus/Zeus-Core
[codefactor-image]: https://www.codefactor.io/repository/github/GetOlympus/olympus-wordpress-field/badge?style=flat-square
[codefactor-url]: https://www.codefactor.io/repository/github/getolympus/olympus-wordpress-field
[getoption-url]: https://developer.wordpress.org/reference/functions/get_option/
[license-blob]: https://github.com/GetOlympus/olympus-wordpress-field/blob/master/LICENSE
[license-image]: https://img.shields.io/badge/license-MIT_License-blue.svg?style=flat-square
[license-url]: http://opensource.org/licenses/MIT
[packagist-image]: https://img.shields.io/packagist/v/getolympus/olympus-wordpress-field.svg?style=flat-square
[packagist-url]: https://packagist.org/packages/getolympus/olympus-wordpress-field
[twitter-image]: https://img.shields.io/badge/crewstyle-blue.svg?style=social&logo=twitter
[twitter-url]: http://twitter.com/crewstyle