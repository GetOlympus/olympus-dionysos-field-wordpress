<?php

namespace GetOlympus\Field;

use GetOlympus\Hera\Field\Controller\Field;
use GetOlympus\Hera\Translate\Controller\Translate;

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
     * Prepare variables.
     */
    protected function setVars()
    {
        $this->getModel()->setFaIcon('fa-wordpress');
        $this->getModel()->setTemplate('wordpress.html.twig');
    }

    /**
     * Prepare HTML component.
     *
     * @param array $content
     * @param array $details
     */
    protected function getVars($content, $details = [])
    {
        // Build defaults
        $defaults = [
            'id' => '',
            'title' => Translate::t('wordpress.title', [], 'wordpressfield'),
            'default' => [],
            'description' => '',
            'mode' => 'posts',
            'multiple' => false,
            'options' => [],

            // Texts
            't_items' => Translate::t('wordpress.items', [], 'wordpressfield'),
        ];

        // Build defaults data
        $vars = array_merge($defaults, $content);

        // Check if an id is defined at least
        $postid = !isset($details['post_id']) ? 0 : $details['post_id'];

        // Retrieve field value
        $vars['val'] = $this->getValue($content['id'], $details, $vars['default']);
        $vars['val'] = !is_array($vars['val']) ? [$vars['val']] : $vars['val'];

        // Get the categories
        $vars['contents'] = $this->getWPContents(
            $vars['mode'],
            $vars['multiple'],
            $vars['options'],
            $postid
        );

        // Field description
        if (!empty($vars['contents']) && 1 <= count($vars['contents'])) {
            $description = $vars['multiple'] ? Translate::t('wordpress.description', [], 'wordpressfield').'<br/>' : '';
        } else {
            $translate = $vars['multiple'] ? 'wordpress.no_items_found' : 'wordpress.no_item_found';
            $description = sprintf(Translate::t($translate, [], 'wordpressfield'), $vars['mode']).'<br/>';
        }

        // Update description
        $vars['description'] = $description.$vars['description'];

        // Update vars
        $this->getModel()->setVars($vars);
    }

    /**
     * Get Wordpress contents already registered.
     *
     * @param   string  $type       Wordpress content type to return
     * @param   boolean $multiple   Define if there is multiselect or not
     * @param   array   $options    Define options if needed
     * @param   integer $post_id    Define the post ID for meta boxes
     * @return  array   $wpcontents Array of Wordpress content type registered
     */
    protected function getWPContents($type = 'posts', $multiple = false, $options = [], $post_id = 0)
    {
        // Access WordPress contents
        $wpcontents = [];

        // Exclude current item
        if (isset($options['exclude']) && 'current' === $options['exclude']) {
            $options['exclude'] = $post;
        }

        // Get asked contents
        $authorized = [
            'categories', 'category',
            'menus', 'menu',
            'pages', 'page',
            'posts', 'post',
            'posttypes', 'posttype',
            'tags', 'tag',
            'terms', 'term'
        ];

        // Check contents
        if (!in_array($type, $authorized)) {
            return [];
        }

        // Data retrieved
        if (in_array($type, ['categories', 'category'])) {
            $wptype = 'Categories';
        } else if (in_array($type, ['menus', 'menu'])) {
            $wptype = 'Menus';
        } else if (in_array($type, ['pages', 'page'])) {
            $wptype = 'Pages';
        } else if (in_array($type, ['posts', 'post'])) {
            $wptype = 'Posts';
        } else if (in_array($type, ['posttypes', 'posttype'])) {
            $wptype = 'Posttypes';
        } else if (in_array($type, ['tags', 'tag'])) {
            $wptype = 'Tags';
        } else {
            $wptype = 'Terms';
        }

        // Get contents
        $function = 'getWP'.$wptype;
        $wpcontents = $this->$function($options);

        // Return value
        return $wpcontents;
    }

    /**
     * Get WordPress Categories registered.
     *
     * @uses get_categories()
     *
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPCategories($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.category', [], 'wordpressfield');

        // Build options
        $args = array_merge([
            'hide_empty' => 0
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

                // Get the id and the name
                $contents[0][$cat->cat_ID] = $cat->cat_name;
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPMenus($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.menu', [], 'wordpressfield');

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

                // Get the id and the name
                $contents[0][$menu->term_id] = $menu->name;
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPPages($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.page', [], 'wordpressfield');

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

                // Get the id and the name
                $contents[0][$pag->ID] = $pag->post_title;
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPPosts($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.post', [], 'wordpressfield');

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

                // Get the id and the name
                $contents[$pos->post_type][$pos->ID] = $pos->post_title;
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPPosttypes($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.posttype', [], 'wordpressfield');

        // Build options
        $args = array_merge([], $options);

        // Build request
        $types_obj = get_post_types($args, 'object');

        // Iterate on posttypes
        if (!empty($types_obj)) {
            foreach ($types_obj as $typ) {
                // Get the the name
                $contents[0][$typ->name] = $typ->labels->name.' ('.$typ->name.')';
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPTags($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.tag', [], 'wordpressfield');

        // Build options
        $args = array_merge([], $options);

        // Build request
        $tags_obj = get_the_tags();

        // Iterate on tags
        if (!empty($tags_obj)) {
            foreach ($tags_obj as $tag) {
                // Get the id and the name
                $contents[0][$tag->term_id] = $tag->name;
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
     * @param   array $options      Define options if needed
     * @return  array $wpcontents   Array of WordPress items
     */
    protected function getWPTerms($options = [])
    {
        // Build contents
        $contents = [];
        $contents[-1] = Translate::t('wordpress.choose.term', [], 'wordpressfield');

        // Build options
        $args = array_merge([
            'public' => 1
        ], $options);

        // Build request
        $taxs_obj = get_taxonomies($args);

        // Iterate on tags
        if (!empty($taxs_obj)) {
            foreach ($taxs_obj as $tax) {
                // Get the id and the name
                $taxo = get_taxonomy($tax);
                $contents[0][$tax] = $taxo->labels->name.' ('.$taxo->name.')';
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }
}
