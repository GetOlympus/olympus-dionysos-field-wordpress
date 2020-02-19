<?php

namespace GetOlympus\Field;

use GetOlympus\Zeus\Field\Field;
use GetOlympus\Zeus\Utils\Translate;

/**
 * Builds Wordpress field.
 *
 * @package DionysosField
 * @subpackage Wordpress
 * @author Achraf Chouk <achrafchouk@gmail.com>
 * @since 0.0.1
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
    protected function getDefaults() : array
    {
        return [
            'title' => Translate::t('wordpress.title', $this->textdomain),
            'default' => [],
            'description' => '',
            'field' => 'ID',
            'mode' => '',
            'multiple' => false,
            'type' => 'post',
            'settings' => [],

            // Texts
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
    protected function getVars($value, $contents) : array
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

        // Available mode display
        $modes = ['default', 'extended'];

        // Get contents
        $vars = $contents;

        // Retrieve field value
        $vars['value'] = !is_array($value) ? [$value] : $value;

        // Mode
        $vars['mode'] = isset($vars['mode']) ? $vars['mode'] : '';
        $vars['mode'] = in_array($vars['mode'], $modes) ? $vars['mode'] : 'default';

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
            $vars['description'] = sprintf(Translate::t($translate, $this->textdomain), $vars['type']).'<br/>'.$vars['description'];
        }

        // Update vars
        return $vars;
    }

    /**
     * Get Wordpress contents already registered.
     *
     * @param  string  $type
     * @param  bool    $multiple
     * @param  array   $settings
     * @param  int     $post_id
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPContents($type, $multiple = false, $settings = [], $post_id = 0, $field = '') : array
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
     * @see https://developer.wordpress.org/reference/functions/get_categories/
     *
     * @param  array   $options
     * @param  string  $field
     * @param  array   $contents
     * @param  int     $parent
     * @param  string  $prefix
     *
     * @return array
     */
    protected function getWPCategories($options, $field = 'cat_ID', $contents = [], $parent = 0, $prefix = '') : array
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
     * @see https://developer.wordpress.org/reference/functions/wp_get_nav_menus/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array   $wpcontents
     */
    protected function getWPMenus($options, $field = 'term_id') : array
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
     * @see https://developer.wordpress.org/reference/functions/get_pages/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPPages($options, $field = 'ID') : array
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
     * @see https://developer.wordpress.org/reference/functions/wp_get_recent_posts/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPPosts($options, $field = 'ID') : array
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
     * @see https://developer.wordpress.org/reference/functions/get_post_types/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPPosttypes($options, $field = 'name') : array
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
     * @see https://developer.wordpress.org/reference/functions/get_the_tags/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPTags($options, $field = 'term_id') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([], $options);
        $id = isset($args['ID']) ? $args['ID'] : 0;

        // Build request
        $tags_obj = get_the_tags($id);

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
     * @see https://developer.wordpress.org/reference/functions/get_taxonomies/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPTaxonomies($options, $field = '') : array
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
     * @see https://developer.wordpress.org/reference/functions/get_terms/
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPTerms($options, $field = 'term_id') : array
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
