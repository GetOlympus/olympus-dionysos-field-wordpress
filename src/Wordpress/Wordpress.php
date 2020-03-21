<?php

namespace GetOlympus\Dionysos\Field;

use GetOlympus\Zeus\Field\Field;

/**
 * Builds Wordpress field.
 *
 * @package    DionysosField
 * @subpackage Wordpress
 * @author     Achraf Chouk <achrafchouk@gmail.com>
 * @since      0.0.1
 *
 */

class Wordpress extends Field
{
    /**
     * @var string
     */
    protected $script = 'js'.S.'wordpress.js';

    /**
     * @var string
     */
    protected $style = 'css'.S.'wordpress.css';

    /**
     * @var string
     */
    protected $template = 'wordpress.html.twig';

    /**
     * @var string
     */
    protected $textdomain = 'wordpressfield';

    /**
     * Ajax callback used for specific Field actions.
     *
     * @param  array   $request
     *
     * @return string
     */
    protected function ajaxCallback($request) : string
    {
        // Get contents
        $search   = wp_unslash($request['search']);
        $posttype = $request['type'];

        // Set available post types
        $posttypes = get_post_types(['public' => true], 'objects');
        unset($posttypes['attachment']);

        if (!array_key_exists($posttype, $posttypes)) {
            return '-1';
        }

        // Build args
        $args = [
            'post_type'      => $posttype,
            'post_status'    => 'any',
            'posts_per_page' => 50,
        ];

        if ('' !== $search) {
            $args['s'] = $search;
        }

        // Get posts
        $posts = get_posts($args);

        if (!$posts) {
            wp_send_json_error(parent::t('wordpress.ajax.no_items_found', $this->textdomain));
        }

        // Start building HTML ~ Header
        $html = '<table class="widefat">';
        $html .= '<thead><tr>';
        $html .= '<th class="found-radio"></th>';
        $html .= '<th>'.parent::t('wordpress.ajax.title', $this->textdomain).'</th>';
        $html .= '<th class="no-break">'.parent::t('wordpress.ajax.type', $this->textdomain).'</th>';
        $html .= '<th class="no-break">'.parent::t('wordpress.ajax.date', $this->textdomain).'</th>';
        $html .= '<th class="no-break">'.parent::t('wordpress.ajax.status', $this->textdomain).'</th>';
        $html .= '</tr></thead>';

        // HTML ~ Body
        $html .= '<tbody>';
        $alt   = '';

        foreach ($posts as $post) {
            $alt   = 'alternate' === $alt ? '' : 'alternate';

            // Works on title
            $title = trim($post->post_title);
            $title = $title ? $title : parent::t('wordpress.ajax.no_title', $this->textdomain);

            // Works on status
            $status = parent::t('wordpress.ajax.published', $this->textdomain);

            if (in_array($post->post_status, ['future', 'pending', 'draft'])) {
                $status = parent::t('wordpress.ajax.'.$post->post_status, $this->textdomain);
            }

            // Works on date
            $time = '0000-00-00 00:00:00' == $post->post_date ? '' : mysql2date(__('Y/m/d'), $post->post_date);

            // Build HTML ~ Content
            $html .= '<tr class="'.trim('found-posts '.$alt).'">';
            $html .= '<td class="found-radio">';
            $html .= '<input type="radio" id="found-'.$post->ID.'" name="post_id" value="'.esc_attr($post->ID).'" />';
            $html .= '</td>';
            $html .= '<td class="title" data-l="'.get_permalink($post->ID).'">';
            $html .= '<label for="found-'.$post->ID.'">'.esc_html($title).'</label></td>';
            $html .= '</td>';
            $html .= '<td class="no-break">'.esc_html($posttypes[$post->post_type]->labels->singular_name).'</td>';
            $html .= '<td class="no-break">'.esc_html($time).'</td>';
            $html .= '<td class="no-break">'.esc_html($status).'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Prepare defaults.
     *
     * @return array
     */
    protected function getDefaults() : array
    {
        return [
            'title' => parent::t('wordpress.title', $this->textdomain),
            'default' => [],
            'description' => '',
            'field' => 'ID',
            'mode' => '',
            'multiple' => false,
            'type' => 'post',
            'settings' => [],

            // texts
            't_addblock_title' => parent::t('wordpress.addblock_title', $this->textdomain),
            't_addblock_description' => parent::t('wordpress.addblock_description', $this->textdomain),
            't_addblocks_description' => parent::t('wordpress.addblocks_description', $this->textdomain),
            't_addblock_label' => parent::t('wordpress.addblock_label', $this->textdomain),
            't_editblock_label' => parent::t('wordpress.editblock_label', $this->textdomain),
            't_removeblock_label' => parent::t('wordpress.removeblock_label', $this->textdomain),

            't_modaltitle_label' => parent::t('wordpress.modal.title', $this->textdomain),
            't_modalclose_label' => parent::t('wordpress.modal.close', $this->textdomain),
            't_modalsearch_label' => parent::t('wordpress.modal.search', $this->textdomain),
            't_modalsubmit_label' => parent::t('wordpress.modal.submit', $this->textdomain),
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
            'term' => 'term',
            'users' => 'user',
            'user' => 'user'
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
            $vars['description'] = sprintf(parent::t($translate, $this->textdomain), $vars['type']).'<br/>'.$vars['description'];
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

        $wptype = 'Posts';

        // Data retrieved
        if ('category' === $type) {
            $wptype = 'Categories';
        } else if ('menu' === $type) {
            $wptype = 'Menus';
        } else if ('page' === $type) {
            $wptype = 'Pages';
        } else if ('posttype' === $type) {
            $wptype = 'Posttypes';
        } else if ('tag' === $type) {
            $wptype = 'Tags';
        } else if ('taxonomy' === $type) {
            $wptype = 'Taxonomies';
        } else if ('term' === $type) {
            $wptype = 'Terms';
        } else if ('user' === $type) {
            $wptype = 'Users';
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
        if (!empty($terms_obj) && !is_wp_error($terms_obj)) {
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

    /**
     * Get WordPress Users registered.
     *
     * @uses get_users()
     * @see https://developer.wordpress.org/reference/functions/get_users/
     * @see https://codex.wordpress.org/Function_Reference/get_users
     *
     * @param  array   $options
     * @param  string  $field
     *
     * @return array
     */
    protected function getWPUsers($options, $field = 'ID') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'role' => '',
        ], $options);

        // Build request
        $users_obj = get_users($args);

        // Iterate on tags
        if (!empty($users_obj) && !is_wp_error($users_obj)) {
            foreach ($users_obj as $user) {
                // Check field
                $item = !empty($field) && isset($user->$field) ? $user->$field : $user->ID;

                // Get the id and the name
                $contents[$item] = $user->display_name;
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }
}
