<?php

abstract class CMA_PostType {

    private static $postTypesToRegister = array();
    private static $taxonomiesToRegister = array();
    protected $ID;
    protected $post;
    protected $postMeta = array();



    /* =============================================================
     * Class methods
     * ============================================================= */

    /**
     * Tracks all the post types registered by sub-classes, and hooks into WP to register them
     *
     * @static
     * @param string $postType
     * @param string $singular
     * @param string $plural
     * @param array $args
     * @return void
     */
    protected static function registerPostType($postType, $singular = '', $plural = '', $menu='', $args = array()) {
        self::addRegisterPostTypesHooks();

        if (!$singular) {
            $singular = $postType;
        }
        if (!$plural) {
            $plural = $singular . 's';
        }
        if (!$menu)
            $menu = $plural;
        $defaults = array(
            'show_ui' => true,
            'public' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions'),
            'label' => $plural,
            'labels' => self::postTypeLabels($singular, $plural, $menu),
        );
        $args = wp_parse_args($args, $defaults);
        if (isset(self::$postTypesToRegister[$postType])) {
                      return;
        }
        self::$postTypesToRegister[$postType] = $args;
    }

    /**
     * Generate a set of labels for a post type
     *
     * @static
     * @param string $singular
     * @param string $plural
     * @return array All the labels for the post type
     */
    private static function postTypeLabels($singular, $plural, $menu) {
        return array(
            'name' => $plural,
            'singular_name' => $singular,
            'add_new' => sprintf(__('Add %s', 'cm-answers'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'cm-answers'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'cm-answers'), $singular),
            'new_item' => sprintf(__('New %s', 'cm-answers'), $singular),
            'all_items' => $plural,
            'view_item' => sprintf(__('View %s', 'cm-answers'), $singular),
            'search_items' => sprintf(__('Search %s', 'cm-answers'), $plural),
            'not_found' => sprintf(__('No %s found', 'cm-answers'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'cm-answers'), $plural),
            'menu_name' => $menu
        );
    }

    /**
     * Add the hooks necessary to register post types at the right time
     * @return void
     */
    private static function addRegisterPostTypesHooks() {
        static $registered = FALSE; // only do it once
        if (!$registered) {
            $registered = TRUE;
            add_action('init', array(get_class(), 'registerPostTypes'));
            add_action('template_redirect', array(get_class(), 'contextFixer'));
            add_filter('body_class', array(get_class(), 'bodyClasses'));
        }
    }

    /**
     * Register each queued up post type
     * @return void
     */
    public static function registerPostTypes() {
        foreach (self::$postTypesToRegister as $postType => $args) {
            register_post_type($postType, $args);
        }
    }

    /**
     * is_home should be false if on a managed post type
     * @return void
     */
    public static function contextFixer() {
        if (in_array(get_query_var('post_type'), array_keys(self::$postTypesToRegister))) {
            global $wp_query;
            $wp_query->is_home = false;
        }
    }

    /**
     * If a managed post type is queried, add the post type to body classes
     * @param array $c
     * @return array
     */
    public static function bodyClasses($c) {
        $query_post_type = get_query_var('post_type');
        if (in_array($query_post_type, array_keys(self::$postTypesToRegister))) {
            $c[] = $query_post_type;
            $c[] = 'type-' . $query_post_type;
        }
        return $c;
    }

    /**
     * Tracks all the taxonomies registered by sub-classes, and hooks into WP to register them
     *
     * @static
     * @param string $post_type
     * @param string $singular
     * @param string $plural
     * @param array $args
     * @return void
     */
    protected static function registerTaxonomy($taxonomy, $postTypes, $singular = '', $plural = '', $args = array()) {
        self::addRegisterTaxonomiesHooks();

        if (!$singular) {
            $singular = $taxonomy;
        }
        if (!$plural) {
            $plural = $singular . 's';
        }
        $defaults = array(
            'hierarchical' => TRUE,
            'labels' => self::taxonomyLabels($singular, $plural),
            'show_ui' => TRUE,
            'query_var' => TRUE,
        );
        $args = wp_parse_args($args, $defaults);
        if (isset(self::$taxonomiesToRegister[$taxonomy])) {
            return;
        }
        self::$taxonomiesToRegister[$taxonomy] = array(
            'post_types' => $postTypes,
            'args' => $args
        );
    }

    private static function taxonomyLabels($singular, $plural) {
        return array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => 'Search ' . $plural,
            'popular_items' => 'Popular ' . $plural,
            'all_items' => 'All ' . $plural,
            'parent_item' => 'Parent ' . $singular,
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'edit_item' => 'Edit ' . $singular,
            'update_item' => 'Update ' . $singular,
            'add_new_item' => 'Add New ' . $singular,
            'new_item_name' => 'New ' . $singular . ' Name',
            'menu_name' => $plural,
        );
    }

    /**
     * Add the hooks necessary to register post types at the right time
     * @return void
     */
    private static function addRegisterTaxonomiesHooks() {
        static $registered = FALSE; // only do it once
        if (!$registered) {
            $registered = TRUE;
            add_action('init', array(get_class(), 'registerTaxonomies'));
        }
    }

    /**
     * Register each queued up taxonomy
     *
     * @static
     * @return void
     */
    public static function registerTaxonomies() {
        foreach (self::$taxonomiesToRegister as $taxonomy => $data) {
            register_taxonomy($taxonomy, $data['post_types'], $data['args']);
        }
    }

    /* =============================================================
     * Instance methods
     * ============================================================= */
    /*
     * Multiton Design Pattern
     * ------------------------------------------------------------- */

    final protected function __clone() {
        // cannot be cloned
        trigger_error(__CLASS__ . ' may not be cloned', E_USER_ERROR);
    }

    final protected function __sleep() {
        // cannot be serialized
        trigger_error(__CLASS__ . ' may not be serialized', E_USER_ERROR);
    }

    /**
     * @static
     * @abstract
     * @param int $id
     * @return PostType|NULL
     */
    public static function getInstance($id = 0)
    {
        trigger_error(__CLASS__ . ' may not be instantialized', E_USER_ERROR);
    }

    /**
     * @param int $id The ID of the post
     */
    protected function __construct($id) {
        $this->ID = $id;
        $this->refresh();
        $this->registerUpdateHooks();
    }

    public function getId() {
        return $this->ID;
    }

    public function __destruct() {
//		$this->unregister_update_hooks();
    }

    /**
     * Update with fresh data from the database
     * @return void
     */
    protected function refresh() {
        $this->loadPost();
        $this->loadPostMeta();
    }

    /**
     * Update the post
     *
     * @return void
     */
    protected function loadPost() {
        $this->post = get_post($this->ID);
    }

    protected function savePost() {
        wp_update_post($this->post);
    }

    /**
     * Update post meta
     * @return void
     */
    protected function loadPostMeta() {
        $meta = get_post_custom($this->ID);
        // get_post_meta unserializes, but get_post_custom does not
        foreach ($meta as $key => $value) {
            $this->postMeta[$key] = array_map('maybe_unserialize', $value);
        }
    }

    /**
     * Watch for updates to the post or its meta
     *
     * @return void
     */
    protected function registerUpdateHooks() {
        add_action('save_post', array($this, 'postUpdated'), 1000, 2);
        add_action('updated_post_meta', array($this, 'postMetaUpdated'), 1000, 4);
        add_action('added_post_meta', array($this, 'postMetaUpdated'), 1000, 4);
    }

    /**
     * I'm dying, don't talk to me.
     *
     * @return void
     */
    protected function unregisterUpdateHooks() {
        remove_action('save_post', array($this, 'postUpdated'), 1000, 2);
        remove_action('updated_post_meta', array($this, 'postMetaUpdated'), 1000, 4);
        remove_action('added_post_meta', array($this, 'postMetaUpdated'), 1000, 4);
    }

    /**
     * A post was updated. Refresh if necessary.
     *
     * @param int $post_id The ID of the post that was updated
     * @param object $post
     * @return void
     */
    public function postUpdated($post_id, $post) {
        if ($post_id == $this->ID) {
            $this->refresh();
        }
    }

    /**
     * A post's meta was updated. Refresh if necessary.
     *
     * @param int $meta_id
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @return void
     */
    public function postMetaUpdated($meta_id, $post_id, $meta_key, $meta_value) {
        if ($post_id == $this->ID) {
            $this->refresh();
        }
    }

    /**
     * Get the post object
     *
     * @return object
     */
    public function getPost() {
        return $this->post;
    }

    /**
     * Set the title of the post and save
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title) {
        $this->post->post_title = $title;
        $this->post->post_name = sanitize_title_with_dashes($title);
        $this->savePost();
    }
    public function getTitle() {
        return $this->post->post_title;
    }

    /**
     * Saves the given meta key/value pairs to the post.
     *
     * By default, keys will be unique per post. Override in a child class to change this.
     *
     * @param array $meta An associative array of meta keys and their values to save
     * @return void
     */
    public function savePostMeta($meta = array()) {
        foreach ($meta as $key => $value) {
            update_post_meta($this->ID, $key, $value);
            // $this->post_meta should automatically refresh at this point
        }
    }

    public function addPostMeta($meta = array(), $unique = FALSE) {
        foreach ($meta as $key => $value) {
            add_post_meta($this->ID, $key, $value, $unique);
            // $this->post_meta should automatically refresh at this point
        }
    }

    public function deletePostMeta($meta) {
        foreach ($meta as $key => $value) {
            delete_post_meta($this->ID, $key, $value);
        }
    }

    /**
     * Returns post meta about the post
     *
     * @param string|NULL $meta_key A string indicating which meta key to retrieve, or NULL to return all keys
     * @param bool $single TRUE to return the first value, FALSE to return an array of values
     * @return string|array
     */
    public function getPostMeta($meta_key = NULL, $single = TRUE) {
        if ($meta_key !== NULL) { // get a single field
            if (isset($this->postMeta[$meta_key])) {
                if ($single) {
                    return $this->postMeta[$meta_key][0];
                } else {
                    return $this->postMeta[$meta_key];
                }
            } else {
                return '';
            }
        } else {
            return $this->postMeta;
        }
    }

    public static function findByMeta($post_type, $meta = array()) {
        global $wpdb;
        $sql = "SELECT DISTINCT p.ID FROM {$wpdb->posts} p ";
        $args = array();

        $meta_count = 1;
        $join = '';
        foreach ($meta as $key => $value) {
            $join .= "INNER JOIN {$wpdb->postmeta} pmeta%d ON p.ID = pmeta%d.post_id ";
            $args[] = $meta_count;
            $args[] = $meta_count;
            $meta_count++;
        }

        $where = 'WHERE p.post_type = %s ';
        $args[] = $post_type;

        $meta_count = 1;
        foreach ($meta as $key => $value) {
            $where .= 'AND pmeta%d.meta_key = %s AND pmeta%d.meta_value = %s ';
            $args[] = $meta_count;
            $args[] = $key;
            $args[] = $meta_count;
            $args[] = $value;
            $meta_count++;
        }

        $query = $wpdb->prepare($sql . $join . $where, $args);
        $column = $wpdb->get_col($query);
        return $column;
    }

}
