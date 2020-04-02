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
     * @var array
     */
    protected $adminscripts = ['wp-util'];

    /**
     * @var array
     */
    protected $posttypes = [
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
        $field    = isset($request['field']) ? $request['field'] : '';
        $settings = isset($request['settings']) ? $request['settings'] : [];
        $type     = isset($request['type']) ? $request['type'] : 'post';

        // Check types
        if (!array_key_exists($type, $this->posttypes)) {
            return '-1';
        }

        // Get wanted objects
        $objects = $this->getWPContents($type, $settings, $field, $search);

        // Check objects
        if (!$objects) {
            wp_send_json_error(parent::t('wordpress.ajax.no_items_found', $this->textdomain));
        }

        // Start building HTML ~ Header
        $html = '<table class="widefat">';
        $html .= '<thead><tr>';
        $html .= '<th class="found-radio"></th>';
        $html .= '<th>'.parent::t('wordpress.ajax.title', $this->textdomain).'</th>';
        $html .= '</tr></thead>';

        // HTML ~ Body
        $html .= '<tbody>';
        $alt   = '';

        foreach ($objects as $key => $obj) {
            $alt   = 'alternate' === $alt ? '' : 'alternate';

            // Works on vars
            $title = trim($obj['title']) ? $obj['title'] : parent::t('wordpress.ajax.no_title', $this->textdomain);
            $link = $obj['link'];

            // Build HTML ~ Content
            $html .= '<tr class="'.trim('found-posts '.$alt).'">';
            $html .= '<td class="found-radio">';
            $html .= '<input type="radio" id="found-'.$key.'" name="key" value="'.esc_attr($key).'" />';
            $html .= '</td>';
            $html .= '<td class="found-title">';
            $html .= '<label for="found-'.$key.'">'.esc_html($title).'</label>';
            $html .= '<br/><a href="'.$link.'" target="_blank"><small>'.$link.'</small></a>';
            $html .= '</td>';
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

            't_ajaxerror_label' => parent::t('wordpress.ajax.no_items_found', $this->textdomain),
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
        // Available mode display
        $modes = ['default', 'extended'];

        // Get contents
        $vars = $contents;

        // Retrieve field value
        $vars['value'] = !is_array($value) && !empty($value) ? [$value] : $value;

        // Mode
        $vars['mode'] = isset($vars['mode']) ? $vars['mode'] : '';
        $vars['mode'] = in_array($vars['mode'], $modes) ? $vars['mode'] : 'default';

        // Check types
        $vars['type'] = array_key_exists($vars['type'], $this->posttypes) ? $this->posttypes[$vars['type']] : 'post';

        // Update vars
        return $vars;
    }

    /**
     * Get Wordpress contents already registered.
     *
     * @param  string  $type
     * @param  array   $settings
     * @param  string  $field
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPContents($type, $settings = [], $field = '', $search = '') : array
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
        $wpcontents = $this->$function($settings, $field, $search);

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
     * @param  string  $search
     * @param  array   $ctn
     * @param  int     $pt
     * @param  string  $pfx
     *
     * @return array
     */
    protected function getWPCategories($options, $field = 'cat_ID', $search = '', $ctn = [], $pt = 0, $pfx = '') : array
    {
        // Build options
        $args = array_merge([
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => $pt,
        ], $options);

        if (!empty($search)) {
            $args['search'] = $search;
        }

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
                $ctn[$item] = [
                    'title' => $pfx.$cat->cat_name,
                    'link'  => get_category_link($cat->cat_ID),
                ];

                // Get children
                $ctn = $this->getWPCategories($options, $field, $ctn, $cat->cat_ID, $pfx.'- ');
            }
        }

        // Return all values in a well formatted way
        return $ctn;
    }

    /**
     * Get WordPress Menus registered.
     *
     * @uses wp_get_nav_menus()
     * @see https://developer.wordpress.org/reference/functions/wp_get_nav_menus/
     *
     * @param  array   $options
     * @param  string  $field
     * @param  string  $search
     *
     * @return array   $wpcontents
     */
    protected function getWPMenus($options, $field = 'term_id', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'hide_empty' => false,
            'orderby' => 'none'
        ], $options);

        if (!empty($search)) {
            $args['search'] = $search;
        }

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
                $contents[$item] = [
                    'title' => $menu->name,
                    'link'  => admin_url('nav-menus.php?menu='.$menu->term_id),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPPages($options, $field = 'ID', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'sort_column' => 'post_parent,menu_order'
        ], $options);

        $args['post_type'] = 'page';

        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Build request
        $pages_obj = get_posts($args);

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
                $contents[$item] = [
                    'title' => $pag->post_title,
                    'link'  => get_page_link($pag->ID),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPPosts($options, $field = 'ID', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'post_type' => 'post',
            'post_status' => 'publish'
        ], $options);

        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Build request
        $posts_obj = get_posts($args);

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
                $contents[$item] = [
                    'title' => $pos->post_title,
                    'link'  => get_permalink($pos->ID),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPPosttypes($options, $field = 'name', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([], $options);

        if (!empty($search)) {
            $args['name'] = $search;
            $args['singular_name'] = $search;
        }

        // Build request
        $types_obj = get_post_types($args, 'objects', 'or');

        // Iterate on posttypes
        if (!empty($types_obj)) {
            foreach ($types_obj as $typ) {
                // Check field
                $item = !empty($field) && isset($typ->$field) ? $typ->$field : $typ->name;

                // Get the the name
                $contents[$item] = [
                    'title' => $typ->labels->name.' ('.$typ->name.')',
                    'link'  => admin_url('edit.php?post_type='.$typ->name),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPTags($options, $field = 'term_id', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([], $options);
        $id = isset($args['ID']) ? $args['ID'] : 0;

        // No search allowed for now.

        // Build request
        $tags_obj = get_the_tags($id);

        // Iterate on tags
        if (!empty($tags_obj)) {
            foreach ($tags_obj as $tag) {
                // Check field
                $item = !empty($field) && isset($tag->$field) ? $tag->$field : $tag->term_id;

                // Get the id and the name
                $contents[$item] = [
                    'title' => $tag->name,
                    'link'  => get_term_link($tag->term_id),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPTaxonomies($options, $field = '', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'public' => 1
        ], $options);

        if (!empty($search)) {
            $args['name'] = $search;
            $args['singular_name'] = $search;
        }

        // Build request
        $taxs_obj = get_taxonomies($args, 'objects', 'or');

        // Iterate on tags
        if (!empty($taxs_obj)) {
            foreach ($taxs_obj as $tax) {
                // Get taxonomy details
                $taxo = get_taxonomy($tax);

                // Check field
                $item = !empty($field) && isset($taxo->$field) ? $taxo->$field : $tax;

                // Get the id and the name
                $contents[$item] = [
                    'title' => $taxo->labels->name.' ('.$taxo->name.')',
                    'link'  => admin_url('edit-tags.php?taxonomy='.$taxo->name),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPTerms($options, $field = 'term_id', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'hide_empty' => false,
        ], $options);

        if (!empty($search)) {
            $args['search'] = $search;
        }

        // Build request
        $terms_obj = get_terms($args);

        // Iterate on tags
        if (!empty($terms_obj) && !is_wp_error($terms_obj)) {
            foreach ($terms_obj as $term) {
                // Check field
                $item = !empty($field) && isset($term->$field) ? $term->$field : $term->term_id;

                // Get the id and the name
                $contents[$item] = [
                    'title' => $term->name,
                    'link'  => get_term_link($term->term_id),
                ];
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
     * @param  string  $search
     *
     * @return array
     */
    protected function getWPUsers($options, $field = 'ID', $search = '') : array
    {
        // Build contents
        $contents = [];

        // Build options
        $args = array_merge([
            'role' => '',
        ], $options);

        if (!empty($search)) {
            $args['search'] = $search;
        }

        // Build request
        $users_obj = get_users($args);

        // Iterate on tags
        if (!empty($users_obj) && !is_wp_error($users_obj)) {
            foreach ($users_obj as $user) {
                // Check field
                $item = !empty($field) && isset($user->$field) ? $user->$field : $user->ID;

                // Get the id and the name
                $contents[$item] = [
                    'title' => $user->display_name,
                    'link'  => get_edit_user_link($user->ID),
                ];
            }
        }

        // Return all values in a well formatted way
        return $contents;
    }
}
