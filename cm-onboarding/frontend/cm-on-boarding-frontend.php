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
class CMOnBoardingFrontend
{
    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;

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

        self::$cssPath = CMOB_PLUGIN_URL . 'shared/assets/css/';
        self::$jsPath = CMOB_PLUGIN_URL . 'shared/assets/js/';
        self::$viewsPath = CMOB_PLUGIN_DIR . 'shared/views/';

//        if( CMProductCatalog::$isLicenseOK )
//        {
        add_filter('wp_enqueue_scripts', array(self::$calledClassName, 'cmob_enqueue_styles'));
//        }
    }

    public static function cmob_enqueue_styles()
    {
        if( !is_admin() )
        {
            CMOnBoardingShared::getWidgetScriptsAndStyles();
        }
    }

}