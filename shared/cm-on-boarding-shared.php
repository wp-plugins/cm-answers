<?php
if( !defined('ABSPATH') )
{
    exit;
}

class CMOnBoardingShared
{
    protected static $instance = NULL;
    public static $calledClassName;
    public static $lastProductQuery = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;

    const POST_TYPE = 'cm-help-item';
    const POST_TYPE_TAXONOMY = 'cm-hi-category';
    const CMOB_SELECTED_HELP_ITEM = 'cmob-selected-hi';
    const CMOB_SHOW_HELP_ITEM = 'cmob-show-hi';

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

        self::$cssPath = CMOB_PLUGIN_URL . 'shared/assets/css/';
        self::$jsPath = CMOB_PLUGIN_URL . 'shared/assets/js/';
        self::$viewsPath = CMOB_PLUGIN_DIR . 'shared/views/';

        self::setupConstants();
        self::setupOptions();
        self::loadClasses();
        self::registerActions();
    }

    /**
     * Register the plugin's shared actions (both backend and frontend)
     */
    private static function registerActions()
    {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupConstants()
    {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupOptions()
    {
        /*
         * Adding additional options
         */
        do_action('cmob_setup_options');
    }

    /**
     * Create taxonomies
     */
    public static function cmob_create_taxonomies()
    {
        return;
    }

    /**
     * Load plugin's required classes
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function loadClasses()
    {
        /*
         * Load the file with shared global functions
         */
        include_once CMOB_PLUGIN_DIR . "shared/functions.php";
    }

    public function registerShortcodes()
    {
        return;
    }

    public function registerFilters()
    {
        return;
    }

    public static function initSession()
    {
        if( !session_id() )
        {
            session_start();
        }
    }

    /**
     * Create custom post type
     */
    public static function registerPostTypeAndTaxonomies()
    {
        return;
    }

    /**
     * Gets the list of the products
     * @param type $atts
     * @return type
     */
    public static function getItems($atts = array())
    {
        $postTypes = array(self::POST_TYPE);
        $orderby = CMOB_Settings::getOption(CMOB_Settings::OPTION_ITEMS_ORDERBY);
        $order = CMOB_Settings::getOption(CMOB_Settings::OPTION_ITEMS_ORDER);

        $args = array(
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'post_type'        => $postTypes,
            'orderby'          => $orderby,
            'order'            => $order,
            'suppress_filters' => true
        );

        /*
         * Don't show paused products
         */
        if( !empty($atts['paused']) )
        {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'   => 'CMOB_pause_prod',
                    'value' => '0',
                ),
                array(
                    'key'     => 'CMOB_pause_prod',
                    'value'   => '0',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        /*
         * Don't show paused products
         */
        if( !empty($atts['from_edd']) )
        {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'   => 'CMOB_edd_product',
                    'value' => '1',
                )
            );
        }

        /*
         * Return in categories
         */
        if( !empty($atts['cats']) )
        {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => CMProductCatalogShared::POST_TYPE_TAXONOMY,
                    'terms'    => $atts['cats'],
                    'operator' => 'IN',
                    'field'    => 'slug',
                ),
            );
        }

        /*
         * Return with tags
         */
        if( !empty($atts['tags']) )
        {
            $args['tag_slug__in'] = $atts['tags'];
        }

        /*
         * Return only products with given ids
         */
        if( !empty($atts['item_ids']) )
        {
            $atts['item_ids'] = is_array($atts['item_ids']) ? $atts['item_ids'] : array($atts['item_ids']);
            $args['post__in'] = $atts['item_ids'];
        }

        /*
         * Return only products which title/description includes the query
         */
        if( !empty($atts['query']) )
        {
            $args['s'] = $atts['query'];
        }

        $query = new WP_Query($args);
        /*
         * Store the query to save info about pagination
         */
        self::$lastProductQuery = $query;
        $items = $query->get_posts();

        return $items;
    }

    public static function getItem($productIdName)
    {
        return;
    }

    /**
     * Function returns the help item assigned to the page
     */
    public static function getPostHelpItem($postId)
    {
        $selectedHelpItem = get_post_meta($postId, CMOnBoardingShared::CMOB_SELECTED_HELP_ITEM, true);
        return $selectedHelpItem;
    }

    /**
     * Function returns the help item which has the checkbox saying: "Show on all pages" selected, or FALSE
     */
    public static function getGlobalHelpItem()
    {
        $helpItems = self::getItems();
        $result = get_option('cm-help-item-show-allpages', FALSE);

        if( !$result )
        {
            foreach($helpItems as $helpItem)
            {
                $helpItemMeta = CMOnBoardingBackend::prepareHelpItemData($helpItem->ID, FALSE);

                if( $helpItemMeta['cm-help-item-show-allpages'] )
                {
                    update_option('cm-help-item-show-allpages', $helpItem->ID);
                    $result = $helpItem->ID;
                }
            }
        }

        return $result;
    }

    public static function getIcon()
    {
        $defaultImage = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_ICON);
        $defaultImageTheme = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_ICON_THEME);

        $customIcon = CMOB_Settings::getOption(CMOB_Settings::OPTION_CUSTOM_WIDGET_ICON);
        if( !empty($customIcon) )
        {
            $imageUrl = $customIcon;
        }
        else
        {
            $imageUrl = CMOB_PLUGIN_URL . 'shared/assets/images/icons/' . $defaultImage . '_icon ' . $defaultImageTheme . ' 30x30.png';
        }

        return $imageUrl;
    }

    public static function getIconPosition()
    {
        $position = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_ICON_TOP);
        return $position;
    }

    public static function getWidgetWidth()
    {
        $result = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_WIDTH);
        return $result;
    }

    public static function getWidgetHeight()
    {
        $result = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_HEIGHT);
        return $result;
    }

    public static function getWidgetScriptsAndStyles($atts = array())
    {
        global $post;

        $postId = empty($post->ID) ? '' : $post->ID;

        //Registering Scripts & Styles for the FrontEnd
//        wp_enqueue_style('cmob-style', self::$cssPath . 'cmob-style.css');
        wp_enqueue_script('cmob-widget-loader', self::$jsPath . 'cmob-widget-loader.js', array('jquery'));

        $scriptData = array();
        $scriptData['ajaxurl'] = admin_url('admin-ajax.php');
        $scriptData['js_path'] = self::$jsPath;
        $scriptData['css_path'] = self::$cssPath;
        $scriptData['post_id'] = $postId;
        $scriptData['side'] = CMOB_Settings::getOption(CMOB_Settings::OPTION_DEFAULT_WIDGET_SIDE);
        $scriptData['of_label'] = CMOB_Labels::getLocalized('of_placeholder');
        $scriptData['nothing_found'] = CMOB_Labels::getLocalized('nothing_found');

        if(!empty($atts['help_id']))
        {
            $scriptData['help_id'] = $atts['help_id'];
        }

        wp_localize_script('cmob-widget-loader', 'cmob_data', $scriptData);

        wp_enqueue_style('cm_onboarding_css', CMOB_PLUGIN_URL . 'frontend/assets/css/cm-onboarding.css');

        $icon = CMOnBoardingShared::getIcon();
        $iconPosition = CMOnBoardingShared::getIconPosition();

        $widgetWidth = CMOnBoardingShared::getWidgetWidth();
        $widgetHeight = CMOnBoardingShared::getWidgetHeight();

        $custom_css = "
                #cmob-widget-container{
                    width: {$widgetWidth} !important;
                    -webkit-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -ms-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -moz-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -o-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    transform: translate3d({$widgetWidth}, 0, 0) !important;
                }
                #cmob-widget-container.show {
                    -webkit-transform: translate3d(0, 0, 0) !important;
                    -ms-transform: translate3d(0, 0, 0) !important;
                    -moz-transform: translate3d(0, 0, 0) !important;
                    -o-transform: translate3d(0, 0, 0) !important;
                    transform: translate3d(0, 0, 0) !important;
                }
                #cmob-widget-container .cmob-btn-open {
                    right: calc( {$widgetWidth} - 20px ) !important;
                }
                #cmob-widget-container .cmob-btn-open:hover {
                    right: calc( {$widgetWidth} - 5px ) !important;
                }
                #cmob-widget-container .cmob-btn-open.show:hover {
                    right: calc( {$widgetWidth} - 30px ) !important;
                }

                #cmob-widget-container-wrapper.left #cmob-widget-container {
                    left: -{$widgetWidth} !important;
                    -webkit-transform: translate3d(0, 0, 0) !important;
                    -ms-transform: translate3d(0, 0, 0) !important;
                    -moz-transform: translate3d(0, 0, 0) !important;
                    -o-transform: translate3d(0, 0, 0) !important;
                    transform: translate3d(0, 0, 0) !important;
                }

                #cmob-widget-container-wrapper.left #cmob-widget-container.show {
                    -webkit-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -ms-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -moz-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    -o-transform: translate3d({$widgetWidth}, 0, 0) !important;
                    transform: translate3d({$widgetWidth}, 0, 0) !important;
                }

                #cmob-widget-container-wrapper.left .cmob-btn-open{
                    right: -40px !important;
                    background-position: center right 4px !important;
                }

                #cmob-widget-container-wrapper.left .cmob-btn-open:hover {
                    right: -60px !important;
                }

                #cmob-widget-container-wrapper.left .cmob-btn-open.show:hover {
                    right: -55px !important;
                }

                #cmob-widget-content{
                    max-height: {$widgetHeight} !important;
                }
                .cmob-widget-content{
                    max-height: {$widgetHeight} !important;
                }
                .bx-wrapper{
                    max-height: {$widgetHeight} !important;
                }
                #cmob-widget-container-wrapper .cmob-btn-open{
                    background-image: url('{$icon}') !important;
                    top: {$iconPosition} !important;
                }
                ";
        wp_add_inline_style('cm_onboarding_css', $custom_css);
    }

}