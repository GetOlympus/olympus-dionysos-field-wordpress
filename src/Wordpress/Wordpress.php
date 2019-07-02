<?php

namespace GetOlympus\Field;

use GetOlympus\Zeus\Field\Controller\Field;
use GetOlympus\Zeus\Translate\Controller\Translate;

/**
 * Builds Wordpress field.
 *
 * @package Field
 * @subpackage Wordpress
 * @author Achraf Chouk <achrafchouk@gmail.com>
 * @since 0.0.1
 *
 * @see https://olympus.readme.io/v1.0/docs/wordpress-field
 *
 */

class Wordpress extends Field
{
    /**
     * @var array
     */
    //protected $adminscripts = ['wp-lists', 'zeus-tabs'];

    /**
     * @var string
     */
    protected $template = 'wordpress.html.twig';

    /**
     * @var string
     */
    protected $textdomain = 'wordpressfield';

    /**
     * Prepare defaults.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'title' => Translate::t('wordpress.title', $this->textdomain),
            'default' => [],
            'description' => '',
            'field' => 'ID',
            'multiple' => false,
            'type' => 'post',
            'settings' => [],

            // Texts
            't_all' => Translate::t('wordpress.all', $this->textdomain),
            't_items' => Translate::t('wordpress.items', $this->textdomain),
            't_mostused' => Translate::t('wordpress.most_used', $this->textdomain),
            't_search' => Translate::t('wordpress.search', $this->textdomain),
        ];
    }

    /**
     * Prepare variables.
     *
     * @param  object  $value
     * @param  array   $contents
     *
     * @return array
     */
    protected function getVars($value, $contents)
    {
        // Available types
        $types = [
            'categories' => 'category',
            'category' => 'category',
            'menus' => 'menu',
            'menu' => 'menu',
            'pages' => 'page',
            'page' => 'page',
            'posts' => 'post',
            'post' => 'post',
            'posttypes' => 'posttype',
            'posttype' => 'posttype',
            'tags' => 'tag',
            'tag' => 'tag',
            'taxonomies' => 'taxonomy',
            'taxonomy' => 'taxonomy',
            'terms' => 'term',
            'term' => 'term'
        ];

        // Get contents
        $vars = $contents;

        // Retrieve field value
        $vars['value'] = !is_array($value) ? [$value] : $value;

        // Check types
        $vars['type'] = array_key_exists($vars['type'], $types) ? $types[$vars['type']] : 'post';

        // Get the categories
        $vars['contents'] = $this->getWPContents(
            $vars['type'],
            $vars['multiple'],
            $vars['settings'],
            $vars['value'],
            $vars['field']
        );

        // Field description
        if (empty($vars['contents'])) {
            $translate = $vars['multiple'] ? 'wordpress.no_items_found' : 'wordpress.no_item_found';
            $vars['description'] = sprintf(Translate::t($translate, $this->textdomain), $vars['mode']).'<br/>'.$vars['description'];
        }

        // Update vars
        return $vars;
    }

    /**
     * Get Wordpress contents already registered.
     *
     * @param   string  $type       Wordpress content type to return
     * @param   boolean $multiple   Define if there is multiselect or not
     * @param   array   $settings   Define settings if needed
     * @param   integer $post_id    Define the post ID for meta boxes
     * @param   string  $field      Define the value of each select options
     * @return  array   $wpcontents Array of Wordpress content type registered
     */
    protected function getWPContents($type = 'post', $multiple = false, $settings = [], $post_id = 0, $field = '')
    {
        // Access WordPress contents
        $wpcontents = [];

        // Exclude current item
        if (isset($settings['exclude']) && 'current' === $settings['exclude']) {
            $settings['exclude'] = $post;
        }

        // Data retrieved
        if ('category' === $type) {
            $wptype = 'Categories';
        } else if ('menu' === $type) {
            $wptype = 'Menus';
        } else if ('page' === $type) {
            $wptype = 'Pages';
        } else if ('post' === $type) {
            $wptype = 'Posts';
        } else if ('posttype' === $type) {
            $wptype = 'Posttypes';
        } else if ('tag' === $type) {
            $wptype = 'Tags';
        } else if ('taxonomy' === $type) {
            $wptype = 'Taxonomies';
        } else {
            $wptype = 'Terms';
        }

        // Get contents
        $function = 'getWP'.$wptype;
        $wpcontents = $this->$function($settings, $field);

        // Return value
        return $wpcontents;
    }

    /**
     * Get WordPress Categories registered.
     *
     * @uses get_categories()
     *
     * @param   array   $options    Define options if needed
     * @param   string  $field      Define the value of each select options
     * @param   array   $contents   Define all already set contents
     * @param   integer $parent     Define parent category
     * @param   string  $prefix     Define text to display before name
     * @return  array   $wpcontents Array of WordPress items
     */
    protected function getWPCategories($options = [], $field = 'cat_ID', $contents = [], $parent = 0, $prefix = '')
    {
        // Build options
        $args = array_merge([
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => $parent,
        ], $options);

        // Build request
        $categories_obj = get_categories($args);

        // Iterate on categories
        if (!empty($categories_obj)) {
            foreach ($categories_obj as $cat) {
                // For Wordpress version < 3.0
                if (empty($cat->cat_ID)) {
                    continue;
                }

                // Check field
                $item = !empty($field) && isset($cat->$field) ? $cat->$field : $cat->cat_ID;

                // Get the id and the name
                $contents[$item] = $prefix.$cat->cat_name;

                // Get children
                $contents = $this->getWPCategories($options, $field, $contents, $cat->cat_ID, $prefix.'- ');
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Menus registered.
     *
     * @uses wp_get_nav_menus()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPMenus($options = [], $field = 'term_id')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'hide_empty' => false,
            'orderby' => 'none'
        ], $options);

        // Build request
        $menus_obj = wp_get_nav_menus($args);

        // Iterate on menus
        if (!empty($menus_obj)) {
            foreach ($menus_obj as $menu) {
                // For Wordpress version < 3.0
                if (empty($menu->term_id)) {
                    continue;
                }

                // Check field
                $item = !empty($field) && isset($menu->$field) ? $menu->$field : $menu->term_id;

                // Get the id and the name
                $contents[$item] = $menu->name;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Pages registered.
     *
     * @uses get_pages()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPPages($options = [], $field = 'ID')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'sort_column' => 'post_parent,menu_order'
        ], $options);

        // Build request
        $pages_obj = get_pages($args);

        // Iterate on pages
        if (!empty($pages_obj)) {
            foreach ($pages_obj as $pag) {
                // For Wordpress version < 3.0
                if (empty($pag->ID)) {
                    continue;
                }

                // Check field
                $item = !empty($field) && isset($pag->$field) ? $pag->$field : $pag->ID;

                // Get the id and the name
                $contents[$item] = $pag->post_title;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Posts registered.
     *
     * @uses wp_get_recent_posts()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPPosts($options = [], $field = 'ID')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'post_type' => 'post',
            'post_status' => 'publish'
        ], $options);

        // Build request
        $posts_obj = wp_get_recent_posts($args, OBJECT);

        // Iterate on posts
        if (!empty($posts_obj)) {
            foreach ($posts_obj as $pos) {
                // For Wordpress version < 3.0
                if (empty($pos->ID)) {
                    continue;
                }

                // Check field
                $item = !empty($field) && isset($pos->$field) ? $pos->$field : $pos->ID;

                // Get the id and the name
                //$contents[$pos->post_type][$item] = $pos->post_title;
                $contents[$item] = $pos->post_title;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Post Types registered.
     *
     * @uses get_post_types()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPPosttypes($options = [], $field = 'name')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([], $options);

        // Build request
        $types_obj = get_post_types($args, 'object');

        // Iterate on posttypes
        if (!empty($types_obj)) {
            foreach ($types_obj as $typ) {
                // Check field
                $item = !empty($field) && isset($typ->$field) ? $typ->$field : $typ->name;

                // Get the the name
                $contents[$item] = $typ->labels->name.' ('.$typ->name.')';
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Tags registered.
     *
     * @uses get_the_tags()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPTags($options = [], $field = 'term_id')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([], $options);

        // Build request
        $tags_obj = get_the_tags();

        // Iterate on tags
        if (!empty($tags_obj)) {
            foreach ($tags_obj as $tag) {
                // Check field
                $item = !empty($field) && isset($tag->$field) ? $tag->$field : $tag->term_id;

                // Get the id and the name
                $contents[$item] = $tag->name;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Taxonomies registered.
     *
     * @uses get_taxonomies()
     * @uses get_taxonomy()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPTaxonomies($options = [], $field = '')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'public' => 1
        ], $options);

        // Build request
        $taxs_obj = get_taxonomies($args);

        // Iterate on tags
        if (!empty($taxs_obj)) {
            foreach ($taxs_obj as $tax) {
                // Get taxonomy details
                $taxo = get_taxonomy($tax);

                // Check field
                $item = !empty($field) && isset($taxo->$field) ? $taxo->$field : $tax;

                // Get the id and the name
                $contents[$item] = $taxo->labels->name.' ('.$taxo->name.')';
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }

    /**
     * Get WordPress Terms registered.
     *
     * @uses get_terms()
     *
     * @param   array  $options     Define options if needed
     * @param   string $field       Define the value of each select options
     * @return  array  $wpcontents  Array of WordPress items
     */
    protected function getWPTerms($options = [], $field = 'term_id')
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'hide_empty' => false,
        ], $options);

        // Build request
        $terms_obj = get_terms($args);

        // Iterate on tags
        if (!empty($terms_obj) && ! is_wp_error($terms_obj)) {
            foreach ($terms_obj as $term) {
                // Check field
                $item = !empty($field) && isset($term->$field) ? $term->$field : $term->term_id;

                // Get the id and the name
                $contents[$item] = $term->name;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }
}
