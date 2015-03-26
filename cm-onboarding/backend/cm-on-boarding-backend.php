<?php
// Exit if accessed directly
if( !defined('ABSPATH') )
{
    exit;
}

/**
 * Main backend class file/controller.
 * What it does:
 * - shows/adds/edits plugin settings
 * - adding metaboxes to admin area
 * - adding admin scripts
 * - other admin area only things
 *
 * How it works:
 * - everything is hooked up in the constructor
 */
class CMOnBoardingBackend
{
    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;
    public static $settingsPageSlug = NULL;
    public static $proPageSlug = NULL;
    public static $aboutPageSlug = NULL;
    public static $customMetaboxes = array();

    const PAGE_YEARLY_OFFER = 'https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/';

    /**
     * Main Instance
     *
     * Insures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @staticvar array $instance
     * @return The one true CMOnBoarding
     */
    public static function instance()
    {
        $class = __CLASS__;
        if( !isset(self::$instance) && !( self::$instance instanceof $class ) )
        {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct()
    {
        if( empty(self::$calledClassName) )
        {
            self::$calledClassName = __CLASS__;
        }
        self::$cssPath = CMOB_PLUGIN_URL . 'backend/assets/css/';
        self::$jsPath = CMOB_PLUGIN_URL . 'backend/assets/js/';
        self::$viewsPath = CMOB_PLUGIN_DIR . 'backend/views/';

        self::$settingsPageSlug = CMOB_SLUG_NAME . '-settings';
        self::$proPageSlug = CMOB_SLUG_NAME . '-pro';
        self::$aboutPageSlug = CMOB_SLUG_NAME . '-about';

        /*
         * Metabox SECTION
         */
        require_once CMOB_PLUGIN_DIR . 'libs/wpalchemy/wpalchemy.php';

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id'          => '_cm_help_items',
            'title'       => 'Help Items',
            'template'    => CMOB_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items.php',
            'types'       => array(CMOnBoardingShared::POST_TYPE),
            'init_action' => array(self::$calledClassName, 'metaInit'),
            'save_filter' => array(self::$calledClassName, 'metaRepeatingSaveFilter'),
        ));

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id'       => '_cm_help_items_custom_fields',
            'title'    => 'Help Item - Options',
            'template' => CMOB_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items-options.php',
            'types'    => array(CMOnBoardingShared::POST_TYPE)
        ));

        add_filter('query_vars', array(self::$calledClassName, 'addQueryVars'));
        add_action('parse_query', array(self::$calledClassName, 'processQueryArg'));

        /*
         * Recreate the default filters on the_content
         * this will make it much easier to output the meta content with proper/expected formatting
         */
        add_filter('meta_content', 'wptexturize');
        add_filter('meta_content', 'convert_smilies');
        add_filter('meta_content', 'convert_chars');
        add_filter('meta_content', 'wpautop');
        add_filter('meta_content', 'shortcode_unautop');
        add_filter('meta_content', 'prepend_attachment');
        add_filter('meta_content', 'do_shortcode');

        /*
         * Metabox SECTION END
         */

        add_filter('mce_css', array(self::$calledClassName, 'plugin_mce_css'));

        add_action('init', array(self::$calledClassName, 'createPostType'));
        add_action('current_screen', array(self::$calledClassName, 'handlePost'));

        add_action('admin_menu', array(self::$calledClassName, 'addMenu'));
//        add_filter('post_row_actions',array(self::$calledClassName, 'addRowAction'), 10, 2);
        add_filter('page_row_actions', array(self::$calledClassName, 'addRowAction'), 10, 2);

        /*
         * JSON API
         */
        add_action('wp_ajax_cm_onboarding_json_api', array(self::$calledClassName, 'outputJson'));
        add_action('wp_ajax_nopriv_cm_onboarding_json_api', array(self::$calledClassName, 'outputJson'));

        /*
         * TEST API
         */
        add_action('wp_ajax_cm_onboarding_widget', array(self::$calledClassName, 'outputWidget'));
        add_action('wp_ajax_nopriv_cm_onboarding_widget', array(self::$calledClassName, 'outputWidget'));

        /*
         * LoadTemplate API
         */
        add_action('wp_ajax_cm_onboarding_template_api', array(self::$calledClassName, 'outputTemplate'));
        add_action('wp_ajax_nopriv_cm_onboarding_template_api', array(self::$calledClassName, 'outputTemplate'));

        /*
         * Preview
         */
        add_action('wp_ajax_cm_onboarding_preview', array(self::$calledClassName, 'outputPreview'));

        add_filter('post_type_link', array(self::$calledClassName, 'replacePostLink'), 999, 4);
        add_filter('plugins_loaded', array(self::$calledClassName, 'stop_ckeditor'));

        /*
         * Metaboxes
         */
        add_action('add_meta_boxes', array(self::$calledClassName, 'registerBoxes'));
        add_action('save_post', array(self::$calledClassName, 'savePostdata'));
        add_action('update_post', array(self::$calledClassName, 'savePostdata'));

        /*
         * Notice
         */
        add_action('admin_notices', array(self::$calledClassName, 'showMessage'));

        add_action('admin_enqueue_scripts', array(self::$calledClassName, 'enqueueStyles'));
    }

    public static function stop_ckeditor($plugins)
    {
        $get = $_GET;
        if( !empty($get['post_type']) || !empty($get['post']) )
        {
            $postType = null;

            if( !empty($get['post_type']) )
            {
                $postType = $get['post_type'];
            }
            elseif( !empty($get['post']) )
            {
                $postType = get_post_type($get['post']);
            }

            if( $postType && $postType == 'cm-help-item' )
            {
                remove_action('init', 'ckeditor_init');
            }
        }

//        global $ckeditor_wordpress;
//
//        remove_action('admin_menu', array(&$ckeditor_wordpress, 'add_option_page'));
//        remove_action('admin_head', array(&$ckeditor_wordpress, 'add_admin_head'));
//        remove_action('personal_options_update', array(&$ckeditor_wordpress, 'user_personalopts_update'));
//        remove_action('admin_print_scripts', array(&$ckeditor_wordpress, 'add_post_js'));
//        remove_action('admin_print_footer_scripts', array(&$ckeditor_wordpress, 'remove_tinymce'));
//
//        remove_action('wp_print_scripts', array(&$ckeditor_wordpress, 'add_comment_js'));
//        remove_filter('ckeditor_external_plugins', array(&$ckeditor_wordpress, 'ckeditor_wpmore_plugin'));
//        remove_filter('ckeditor_buttons', array(&$ckeditor_wordpress, 'ckeditor_wpmore_button'));
//        remove_filter('ckeditor_external_plugins', array(&$ckeditor_wordpress, 'ckeditor_wpgallery_plugin'));
//        remove_filter('ckeditor_load_lang_options', array(&$ckeditor_wordpress, 'ckeditor_load_lang_options'));
//
//        remove_filter('wp_insert_post_data', array(&$ckeditor_wordpress, 'ckeditor_insert_post_data_filter'));
//
//        /** temporary for vvq * */
//        remove_filter('ckeditor_external_plugins', array(&$ckeditor_wordpress, 'ckeditor_externalvvq_plugin'));
//        remove_filter('ckeditor_buttons', array(&$ckeditor_wordpress, 'ckeditor_vvqbuttons'));
//        /** temporary for wppoll * */
//        remove_filter('ckeditor_external_plugins', array(&$ckeditor_wordpress, 'wppoll_external'));
//        remove_filter('ckeditor_buttons', array(&$ckeditor_wordpress, 'wppoll_buttons'));
//        remove_filter('ckeditor_external_plugins', array(&$ckeditor_wordpress, 'starrating_external_plugin'));
//        remove_filter('ckeditor_buttons', array(&$ckeditor_wordpress, 'starrating_buttons'));

        return $plugins;
    }

    public static function replacePostLink($post_link, $post, $leavename, $sample)
    {
        if( $post->post_type == CMOnBoardingShared::POST_TYPE )
        {
            return admin_url('admin-ajax.php?action=cm_onboarding_preview&help_id=' . $post->ID);
        }
        return $post_link;
    }

    public static function addQueryVars($vars)
    {
        $vars[] = "post_id";
        $vars[] = "cm-action";
        return $vars;
    }

    /**
     * Create custom post type
     */
    public static function createPostType()
    {
        $args = array(
            'label'               => 'Help Item',
            'labels'              => array(
                'add_new_item'  => 'Add New Help Item',
                'add_new'       => 'Add Help Item',
                'edit_item'     => 'Edit Help Item',
                'view_item'     => 'View Help Item',
                'singular_name' => 'Help Item',
                'name'          => CMOB_PLUGIN_NAME,
                'menu_name'     => 'Help Items'
            ),
            'description'         => 'CM Help Items',
            'map_meta_cap'        => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_admin_bar'   => true,
            'show_in_menu'        => CMOB_SLUG_NAME,
            '_builtin'            => false,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => false,
            'rewrite'             => array('slug' => CMOnBoardingShared::POST_TYPE, 'with_front' => false, 'feeds' => false, 'feed' => false),
            'query_var'           => true,
            'supports'            => array('title', 'revisions'),
        );

        register_post_type(CMOnBoardingShared::POST_TYPE, $args);
    }

    /**
     * Checks for an action during the query parsing
     */
    public static function processQueryArg()
    {
        $postType = get_query_var('post_type');
        $postId = get_query_var('post_id');
        $action = get_query_var('cm-action');

        if( is_admin() && $postType == CMOnBoardingShared::POST_TYPE && $postId && $action )
        {
            switch($action)
            {
                default:
                    break;
            }

            $redirectUrl = add_query_arg(array('post_type' => CMOnBoardingShared::POST_TYPE), admin_url('edit.php'));
            wp_redirect($redirectUrl);
            exit();
        }
    }

    /**
     * Outputs the JSON
     */
    public static function outputWidget()
    {
        $defaultWidgetIcon = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_ICON);
        $defaultWidgetIconTheme = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_ICON_THEME);
        $defaultWidgetType = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_TYPE);
        $defaultWidgetTheme = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_THEME);
        $defaultWidth = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_WIDTH);
        $defaultHeight = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_HEIGHT);

        $helpItemPostId = filter_input(INPUT_POST, 'help_id');
        if( empty($helpItemPostId) )
        {
            $postId = intval(filter_input(INPUT_POST, 'post_id'));

            $postHelpItem = CMOnBoardingShared::getPostHelpItem($postId);
            $globalHelpItem = CMOnBoardingShared::getGlobalHelpItem();

            if( $postHelpItem === FALSE || $postHelpItem === '-1' || $postHelpItem === '' )
            {
                if( !empty($globalHelpItem) )
                {
                    $helpItemPostId = $globalHelpItem;
                }
                else
                {
                    /*
                     * No HelpItem - not defined
                     */
                    $helpItemPostId = FALSE;
                }
            }
            else
            {
                if( intval($postHelpItem) > 0 )
                {
                    $helpItemPostId = $postHelpItem;
                }
                else
                {
                    /*
                     * No HelpItem - explicit
                     */
                    $helpItemPostId = FALSE;
                }
            }
        }

        $post = get_post($helpItemPostId);
        if( !$post || $post->post_type !== CMOnBoardingShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($helpItemPostId) || wp_is_post_autosave($helpItemPostId) )
        {
            echo 'Wrong post type!';
            die();
        }

        $helpItem = self::prepareHelpItemData($helpItemPostId);
        $linkPath = CMOB_PLUGIN_DIR . 'shared/assets/views/widget.' . $defaultWidgetType . '.phtml';

        if( file_exists($linkPath) )
        {
            ob_start();
            require $linkPath;
            $content = ob_get_contents();
            ob_end_clean();
        }

        $response['body'] = $content;
        $response['type'] = $defaultWidgetType;
        $response['theme'] = $defaultWidgetTheme;
        $response['icon'] = $defaultWidgetIcon;
        $response['icon_theme'] = $defaultWidgetIconTheme;
        $response['widget_height'] = $defaultWidth;
        $response['widget_width'] = $defaultHeight;

        wp_send_json($response);
    }

    /**
     * Outputs the JSON
     */
    public static function outputJson()
    {
        $data = null;
        $pinOption = get_option('cm_onboarding_json_api_pinprotect', false);

        if( !empty($pinOption) )
        {
            $passedPin = filter_input(INPUT_GET, 'pin');
            if( $passedPin != $pinOption )
            {
                echo 'Incorrect PIN!';
                die();
            }
        }

        $helpItemPostId = filter_input(INPUT_GET, 'help_id');

        if( $helpItemPostId )
        {
            $post = get_post($helpItemPostId);
            if( !$post || $post->post_type !== CMOnBoardingShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($helpItemPostId) || wp_is_post_autosave($helpItemPostId) )
            {
                echo 'Wrong post type!';
                die();
            }
            $data = self::prepareHelpItemData($helpItemPostId);
        }
        else
        {
            echo 'No "help_id" parameter!';
            die();
        }

        wp_send_json($data);
        die();
    }

    /**
     * Outputs the template
     */
    public static function outputTemplate()
    {
        $templateId = filter_input(INPUT_POST, 'template');
        $template = self::getTemplate($templateId);

        if( $template )
        {
            echo $template;
        }
        die();
    }

    /**
     * Outputs the preview
     */
    public static function outputPreview()
    {
        $helpItemPostId = filter_input(INPUT_GET, 'help_id');

        if( $helpItemPostId )
        {
            $post = get_post($helpItemPostId);
            if( !$post || $post->post_type !== CMOnBoardingShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($helpItemPostId) || wp_is_post_autosave($helpItemPostId) )
            {
                echo 'Wrong post type!';
                die();
            }
        }
        else
        {
            echo 'No "help_id" parameter!';
            die();
        }

        CMOnBoardingShared::getWidgetScriptsAndStyles(array('help_id' => $helpItemPostId));

        $linkPath = CMOB_PLUGIN_DIR . 'backend/views/preview.phtml';
        $cssPath = self::$cssPath;

        if( file_exists($linkPath) )
        {
            ob_start();
            require $linkPath;
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }
        die();
    }

    public static function prepareHelpItemData($postId, $fillJsonStruct = true)
    {
        $postMeta = array();
        $postData = get_post($postId, ARRAY_A);

        if( !empty($postData) )
        {
            $postData = array_intersect_key($postData, array('ID' => '', 'post_title' => ''));
            if( !empty(self::$customMetaboxes) )
            {
                foreach(self::$customMetaboxes as $metabox)
                {
                    $meta = $metabox->the_meta($postId);
                    if( is_array($meta) )
                    {
                        $postMeta = array_merge($postMeta, $meta);
                    }
                }
            }

            $postData = array_merge($postData, $postMeta);
        }

        $helpItemContent = $fillJsonStruct ? self::fillHelpItemJsonStruct($postData) : $postData;
        return $helpItemContent;
    }

    public static function fillHelpItemJsonStruct($postData)
    {
        $itemsData = array();
        $helpItemObj = new stdClass();

        $helpItemObj->id = $postData['ID'];
        $helpItemObj->title = !empty($postData['post_title']) ? $postData['post_title'] : '';
        $helpItemObj->header = !empty($postData['header']) ? $postData['header'] : '';
        $helpItemObj->footer = !empty($postData['footer']) ? $postData['footer'] : '';

        if( !empty($postData['cm-help-item-group']) )
        {
            foreach($postData['cm-help-item-group'] as $groupKey => $group)
            {
                $dataRow = array();
                $dataRow['id'] = $groupKey;

                foreach($group as $fieldKey => $fieldValue)
                {
                    switch($fieldKey)
                    {
                        case 'textarea':
                            $fieldValue = wpautop(do_shortcode(self::replaceImgWithBase64($fieldValue)));
                            $fieldKey = 'content';
                            break;

                        default:
                            break;
                    }

                    $dataRow[$fieldKey] = $fieldValue;
                }

                $itemsData[] = $dataRow;
            }
        }

        foreach($itemsData as $helpItemItemsArr)
        {
            $helpItem = new stdClass();

            foreach($helpItemItemsArr as $key => $value)
            {
                $helpItem->$key = $value;
            }

            $helpItemObj->helpItems[] = $helpItem;
        }

        return $helpItemObj;
    }

    public static function addRowAction($actions, $post)
    {
        if( $post->post_type == CMOnBoardingShared::POST_TYPE )
        {
            $pinOption = get_option('cm_onboarding_json_api_pinprotect', false);
            $pin = !empty($pinOption) ? '&pin=' . $pinOption : '';
            $actions['onboarding_preview'] = '<a href="' . admin_url('admin-ajax.php?action=cm_onboarding_preview&help_id=' . $post->ID) . '" target="_blank">Preview</a>';
            unset($actions['preview']);
            unset($actions['view']);
        }
        return $actions;
    }

    public static function addMenu()
    {
        global $submenu;
        
        add_menu_page('Help Item', CMOB_PLUGIN_NAME, 'edit_posts', CMOB_SLUG_NAME, 'edit.php?post_type=' . CMOnBoardingShared::POST_TYPE);
        add_submenu_page(CMOB_SLUG_NAME, 'Add New Item', 'Add New Item', 'edit_posts', 'post-new.php?post_type=' . CMOnBoardingShared::POST_TYPE);

        add_submenu_page(CMOB_SLUG_NAME, 'Settings', 'Settings', 'edit_posts', self::$settingsPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMOB_SLUG_NAME, 'Pro Version', 'Pro Version', 'edit_posts', self::$proPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMOB_SLUG_NAME, 'About', 'About', 'edit_posts', self::$aboutPageSlug, array(self::$calledClassName, 'renderAdminPage'));

        add_filter('views_edit-' . CMOnBoardingShared::POST_TYPE, array(self::$calledClassName, 'filterAdminNav'), 10, 1);

        if( current_user_can('manage_options') )
        {
            $submenu[CMOB_SLUG_NAME][999] = array('Yearly membership offer', 'manage_options', self::PAGE_YEARLY_OFFER);
            add_action('admin_head', array(__CLASS__, 'admin_head'));
        }
    }

    public static function admin_head()
    {
        echo '<style type="text/css">
        		#toplevel_page_cm-on-boarding a[href*="cm-wordpress-plugins-yearly-membership"] {color: white;}
    			a[href*="cm-wordpress-plugins-yearly-membership"]:before {font-size: 16px; vertical-align: middle; padding-right: 5px; color: #d54e21;
    				content: "\f487";
				    display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 16px/1 \'dashicons\';
    			}
    			#toplevel_page_cm-on-boarding a[href*="cm-wordpress-plugins-yearly-membership"]:before {vertical-align: bottom;}

        	</style>';
    }

    /**
     * Filters admin navigation menus to show horizontal link bar
     * @global string $submenu
     * @global type $plugin_page
     * @param type $views
     * @return string
     */
    public static function filterAdminNav($views)
    {
        global $submenu, $plugin_page;
        $scheme = is_ssl() ? 'https://' : 'http://';
        $adminUrl = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus = array();

        if( isset($submenu[CMOB_SLUG_NAME]) )
        {
            $thisMenu = $submenu[CMOB_SLUG_NAME];

            $firstMenuItem = $thisMenu[0];
            unset($thisMenu[0]);

            $secondMenuItem = array('Trash', 'edit_posts', 'edit.php?post_status=trash&post_type=' . CMOnBoardingShared::POST_TYPE, 'Trash');
            array_unshift($thisMenu, $firstMenuItem, $secondMenuItem);

            foreach($thisMenu as $item)
            {
                $slug = $item[2];
                $isCurrent = ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php'));
                $isCurrent = ($slug == $currentUri);
                $isExternalPage = strpos($item[2], 'http') !== FALSE;
                $isNotSubPage = $isExternalPage || strpos($item[2], '.php') !== FALSE;
                $url = $isNotSubPage ? $slug : get_admin_url(null, 'admin.php?page=' . $slug);
                $target = $isExternalPage ? '_blank' : '';
                $submenus[$item[0]] = '<a href="' . $url . '" target="' . $target . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[0] . '</a>';
            }
        }
        return $submenus;
    }

    public static function getAdminNav()
    {
        global $self, $parent_file, $submenu_file, $plugin_page, $typenow, $submenu;
        ob_start();
        $submenus = array();

        $menuItem = 'edit.php?post_type=' . CMOnBoardingShared::POST_TYPE;

        if( isset($submenu[$menuItem]) )
        {
            $thisMenu = $submenu[$menuItem];

            foreach($thisMenu as $sub_item)
            {
                $slug = $sub_item[2];

                // Handle current for post_type=post|page|foo pages, which won't match $self.
                $self_type = !empty($typenow) ? $self . '?post_type=' . $typenow : 'nothing';

                $isCurrent = FALSE;
                $subpageUrl = get_admin_url('', 'edit.php?post_type=' . CMOnBoardingShared::POST_TYPE . '&page=' . $slug);

                if(
                        (!isset($plugin_page) && $self == $slug ) ||
                        ( isset($plugin_page) && $plugin_page == $slug && ( $menuItem == $self_type || $menuItem == $self || file_exists($menuItem) === false ) )
                )
                {
                    $isCurrent = TRUE;
                }

                $url = (strpos($slug, '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : $subpageUrl;
                $submenus[] = array(
                    'link'    => $url,
                    'title'   => $sub_item[0],
                    'current' => $isCurrent
                );
            }
            include self::$viewsPath . 'nav.phtml';
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function kia_single_save_filter($meta, $post_id)
    {

        if( isset($meta['test_editor']) )
        {
            $meta['test_editor'] = sanitize_post_field('post_content', $meta['test_editor'], $post_id, 'db');
        }

        return $meta;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function metaRepeatingSaveFilter($meta, $post_id)
    {
        array_walk($meta, function ( &$masterItem, $key )
        {
            foreach($masterItem as &$item)
            {
                if( isset($item['textarea']) )
                {
                    $item['textarea'] = sanitize_post_field('post_content', $item['textarea'], $post_id, 'db');
                }
            }
        }, $post_id);

        return $meta;
    }

    /*
     * Enqueue styles and scripts specific to metaboxs
     */

    public static function enqueueStyles()
    {
        global $parent_file;
        if( CMOB_SLUG_NAME !== $parent_file )
        {
            return;
        }

        /*
         * Enqueue onboarding styles
         */
        wp_enqueue_style('cm_onboarding_css', CMOB_PLUGIN_URL . 'backend/assets/css/cm-onboarding.css');
    }

    public static function enqueueScripts()
    {
// I prefer to enqueue the styles only on pages that are using the metaboxes
        wp_enqueue_style('wpalchemy-metabox', CMOB_PLUGIN_URL . 'libs/wpalchemy/assets/meta.css');

//make sure we enqueue some scripts just in case ( only needed for repeating metaboxes )
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_script('word-count');

        wp_enqueue_script('editor');

        wp_enqueue_script('quicktags');
        wp_enqueue_style('buttons');

        wp_enqueue_script('wplink');

        wp_enqueue_script('wp-fullscreen');
        wp_enqueue_script('media-upload');

// special script for dealing with repeating textareas- needs to run AFTER all the tinyMCE init scripts, so make 'editor' a requirement
        wp_enqueue_script('kia-metabox', CMOB_PLUGIN_URL . 'libs/wpalchemy/assets/kia-metabox.js', array('jquery', 'word-count', 'editor', 'quicktags', 'wplink', 'wp-fullscreen', 'media-upload',), '1.1', true);

        /*
         * Enqueue onboarding scripts
         */
        wp_enqueue_script('cm_onboarding_backend', CMOB_PLUGIN_URL . 'backend/assets/js/cm-onboarding-backend.js', array('jquery'), '1.0.0', true);
        wp_localize_script('cm_onboarding_backend', 'cm_onboarding_backend', array('ajaxurl' => admin_url('admin-ajax.php')));

        /*
         * Enqueue onboarding styles
         */
        wp_enqueue_style('cm_onboarding_css', CMOB_PLUGIN_URL . 'backend/assets/css/cm-onboarding.css');
    }

    public static function metaInit()
    {
        add_action('admin_enqueue_scripts', array(self::$calledClassName, 'enqueueScripts'));
    }

    public static function kia_metabox_scripts()
    {
        wp_print_scripts('kia-metabox');
    }

    public static function plugin_mce_css($mce_css)
    {
        if( !empty($mce_css) )
        {
            $mce_css .= ',';
        }
        $mce_css .= CMOB_PLUGIN_URL . 'backend/assets/css/cm-onboarding.css';
        return $mce_css;
    }

    public static function replaceImgWithBase64($content = '')
    {
        return preg_replace_callback(
                '#<img(.*)src=["\'](.*?)["\'](.*)/>#i', array(__CLASS__, '_replaceImgWithBase64'), $content
        );
    }

    public static function _replaceImgWithBase64($matches)
    {
        $img = '<img ' . $matches[1] . ' src="' . self::_curlBase64Encode($matches[2]) . '" ' . $matches[3] . '/>';
        return $img;
    }

    /**
     * Function grabs the image from the given url and prepares the Base64 encoded representation of this string
     * Then caches it and returns the base64 representation of the image with the right MIME type
     *
     * @param string $url - url of the image
     * @param int $ttl - time to live of cache
     * @return type
     */
    public static function _curlBase64Encode($url = null, $ttl = 86400)
    {
        if( $url )
        {
            $option_name = 'ep_base64_encode_images_' . md5($url);
            $data = get_option($option_name);
            if( isset($data['cached_at']) && (time() - $data['cached_at'] <= $ttl) )
            {
# serve cache
            }
            else
            {
                if( strstr($url, 'http:') === FALSE && strstr($url, 'https:') === FALSE )
                {
                    $base = get_bloginfo('url');
                    $url = $base . '/admin/' . $url;
                }
                $ch = curl_init();
                $options = array(
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSLVERSION     => 3
                );
                curl_setopt_array($ch, $options);
                $returnData = curl_exec($ch);
                if( !$returnData )
                {
                    var_dump(curl_error($ch));
                    die;
                }
                $data['chunk'] = base64_encode($returnData);
                $data['mime'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if( $http_code === 200 )
                {
                    $data['cached_at'] = time();
                    update_option($option_name, $data);
                }
            }
        }

        return 'data:' . $data['mime'] . ';base64,' . $data['chunk'];
    }

    public static function renderAdminPage()
    {
        global $wpdb;
        $pageId = filter_input(INPUT_GET, 'page');

        $content = '';
        $title = '';

        switch($pageId)
        {
            case CMOB_SLUG_NAME . '-settings':
                {
                    $title = CMOnBoarding::__('Settings');
                    wp_enqueue_style('jquery-ui-tabs-css', self::$cssPath . 'jquery-ui-tabs.css');
                    wp_enqueue_script('jquery-ui-tabs');

                    $params = apply_filters('CMOB_admin_settings', array());
                    extract($params);

                    ob_start();
                    require_once CMOB_PLUGIN_DIR . 'backend/views/settings.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMOB_SLUG_NAME . '-about':
                {
                    $title = CMOnBoarding::__('About');
                    $iframeURL = 'https://plugins.cminds.com/product-catalog/?showfilter=No&cat=Plugin&nitems=3';
                    ob_start();
                    include_once self::$viewsPath . 'about.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMOB_SLUG_NAME . '-pro':
                {
                    $title = CMOnBoarding::__('Pro Version');
                    ob_start();
                    include_once self::$viewsPath . 'pro.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMOB_SLUG_NAME . '-userguide':
                {
                    wp_redirect('https://plugins.cminds.com/cm-product-catalog');
                    break;
                }
        }

        self::displayAdminPage($content, $title);
    }

    public static function displayAdminPage($content, $title)
    {
        $nav = self::getAdminNav();
        include_once self::$viewsPath . 'template.phtml';
    }

    /**
     * Saves the settings
     */
    public static function handlePost()
    {
        $page = filter_input(INPUT_GET, 'page');
        $postData = filter_input_array(INPUT_POST);

        if( $page == 'cm-on-boarding-settings' && !empty($postData) )
        {
            $params = CMOB_Settings::processPostRequest();

            // Labels
            $labels = CMOB_Labels::getLabels();
            foreach($labels as $labelKey => $label)
            {
                if( isset($postData['label_' . $labelKey]) )
                {
                    CMOB_Labels::setLabel($labelKey, stripslashes($postData['label_' . $labelKey]));
                }
            }

            if( isset($postData['cmob_pluginCleanup']) )
            {
                self::_cleanup();
            }
        }
    }

    /**
     * Returns the list of post types for which the custom settings may be applied
     * @return type
     */
    public static function getApplicablePostTypes()
    {
        $postTypes = array('post', 'page');
        return apply_filters('cmob-metabox-posttypes', $postTypes);
    }

    /**
     * Register metaboxes
     */
    public static function registerBoxes()
    {
        foreach(self::getApplicablePostTypes() as $postType)
        {
            add_meta_box('cmob-metabox', CMOB_PLUGIN_NAME, array(self::$calledClassName, 'showMetaBox'), $postType, 'side', 'high');
        }
    }

    /**
     * Shows metabox containing selectbox with amazon category ID which should be advertised in the Tooltips on this page
     * @global type $post
     */
    public static function showMetaBox()
    {
        global $post;
        $selectedHelpItem = CMOnBoarding::meta($post->ID, CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM, '-1');
        $showHelpItem = CMOnBoarding::meta($post->ID, CMOnBoardingShared::CMOB_SHOW_HELP_ITEM, '0');

        $args = array(
            'post_type'             => CMOnBoardingShared::POST_TYPE,
            'show_option_no_change' => CMOnBoarding::__('Default'),
            'name'                  => CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM,
            'selected'              => $selectedHelpItem
        );

        echo '<p>' . CMOnBoarding::__('Choose if the Help Item should be displayed on this post/page, and which one to display.') . '</p>';

        echo '<div>';
        echo '<label>' . CMOnBoarding::__('Display Help Item?') . ' </label>';
        echo '<input type="hidden" value="0" name="' . CMOnBoardingShared::CMOB_SHOW_HELP_ITEM . '" />';
        echo '<input type="checkbox" value="1" name="' . CMOnBoardingShared::CMOB_SHOW_HELP_ITEM . '" ' . checked('1', $showHelpItem, false) . '/>';
        echo '</div>';

        echo '<div>';
        echo '<label>' . CMOnBoarding::__('Choose Help Item:') . ' </label>';
        cminds_dropdown_pages($args);
        echo '</div>';
    }

    /**
     * Saves the information form the metabox in the post's meta
     * @param type $post_id
     */
    public static function savePostdata($post_id)
    {
        $postType = isset($_POST['post_type']) ? $_POST['post_type'] : '';

        if( in_array($postType, self::getApplicablePostTypes()) )
        {
            $helpItem = ( isset($_POST[CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM])) ? $_POST[CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM] : '-1';
            update_post_meta($post_id, CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM, $helpItem);

            $helpItem = ( isset($_POST[CMOnBoardingShared::CMOB_SHOW_HELP_ITEM])) ? $_POST[CMOnBoardingShared::CMOB_SHOW_HELP_ITEM] : '0';
            update_post_meta($post_id, CMOnBoardingShared::CMOB_SHOW_HELP_ITEM, $helpItem);
        }

        if( in_array($postType, array(CMOnBoardingShared::POST_TYPE)) )
        {
            delete_option('cm-help-item-show-allpages');
            if( isset($_POST['_cm_help_items_custom_fields']['cm-help-item-show-allpages']) )
            {
                $newGlobalHelpItemId = $_POST['_cm_help_items_custom_fields']['cm-help-item-show-allpages'];
                $globalHelpItem = CMOnBoardingShared::getGlobalHelpItem();

                $doPreview = filter_input(INPUT_POST, 'wp-preview');
                /*
                 * Trying to set another Help Item to show on all pages
                 */
                if( !$doPreview && !empty($newGlobalHelpItemId) && $globalHelpItem > 0 && $globalHelpItem !== $post_id )
                {
                    $url = add_query_arg(array('warning' => 1), $_POST['_wp_http_referer']);
                    wp_safe_redirect($url);
                    exit();
                }
            }
        }
    }

    /**
     * Show the message
     * @global type $post
     * @return type
     */
    public static function showMessage()
    {
        global $post;

        if( empty($post) )
        {
            return;
        }

        $showWarning = filter_input(INPUT_GET, 'warning');
        if( in_array($post->post_type, array(CMOnBoardingShared::POST_TYPE)) && $showWarning == '1' )
        {
            $globalHelpItemId = CMOnBoardingShared::getGlobalHelpItem();
            $url = add_query_arg(array('post' => $globalHelpItemId, 'action' => 'edit'), admin_url('post.php'));

            cminds_show_message('One of the the other <a href="' . $url . '" target="_blank">Help Items (edit)</a> is set to be displayed on every page. You can only have one "global" Help Item.', true);
        }
    }

    /**
     * Function cleans up the plugin, removing the terms, resetting the options etc.
     *
     * @return string
     */
    protected static function _cleanup($force = true)
    {
        /*
         * Remove the options
         */
        CMOB_Settings::deleteAllOptions();
    }

}