<?php
/*
  Plugin Name: CM OnBoarding
  Description: This plugin adds the option to add the on-site help
  Version: 1.0.8
  Author: CreativeMindsSolutions
  Licence: GPL
 */

// Exit if accessed directly
if( !defined('ABSPATH') )
{
    exit;
}

/**
 * Main plugin class file.
 * What it does:
 * - checks which part of the plugin should be affected by the query frontend or backend and passes the control to the right controller
 * - manages installation
 * - manages uninstallation
 * - defines the things that should be global in the plugin scope (settings etc.)
 * @author CreativeMindsSolutions - Marcin Dudek
 */
class CMOnBoarding
{
    public static $calledClassName;
    protected static $instance = NULL;
    public static $isLicenseOK = NULL;
    public static $usersColumnMetaName = 'cm-access-restricted';
    public static $messageOptionName = 'cm-access-restricted-message';

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

        self::setupConstants();


        /*
         * Shared
         */
        include_once CMOB_PLUGIN_DIR . '/shared/classes/Labels.php';
        include_once CMOB_PLUGIN_DIR . '/backend/classes/Settings.php';
        include_once CMOB_PLUGIN_DIR . '/shared/cm-on-boarding-shared.php';
        include_once CMOB_PLUGIN_DIR . '/shared/functions.php';

        $cmOnBoardingSharedInstance = CMOnBoardingShared::instance();

        if( is_admin() )
        {
            /*
             * Backend
             */
            require_once CMOB_PLUGIN_DIR . '/backend/cm-on-boarding-backend.php';
            $cmOnBoardingBackendInstance = CMOnBoardingBackend::instance();
        }
        else
        {
            /*
             * Frontend
             */
            require_once CMOB_PLUGIN_DIR . '/frontend/cm-on-boarding-frontend.php';
            $cmOnBoardingFrontendInstance = CMOnBoardingFrontend::instance();
        }
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
        /**
         * Define Plugin Version
         *
         * @since 1.0
         */
        if( !defined('CMOB_VERSION') )
        {
            define('CMOB_VERSION', '1.0.8');
        }

        /**
         * Define Plugin Directory
         *
         * @since 1.0
         */
        if( !defined('CMOB_PLUGIN_DIR') )
        {
            define('CMOB_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

        /**
         * Define Plugin URL
         *
         * @since 1.0
         */
        if( !defined('CMOB_PLUGIN_URL') )
        {
            define('CMOB_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        /**
         * Define Plugin File Name
         *
         * @since 1.0
         */
        if( !defined('CMOB_PLUGIN_FILE') )
        {
            define('CMOB_PLUGIN_FILE', __FILE__);
        }

        /**
         * Define Plugin Slug name
         *
         * @since 1.0
         */
        if( !defined('CMOB_SLUG_NAME') )
        {
            define('CMOB_SLUG_NAME', 'cm-on-boarding');
        }

        /**
         * Define Plugin name
         *
         * @since 1.0
         */
        if( !defined('CMOB_NAME') )
        {
            define('CMOB_NAME', 'CM-On-Boarding');
        }

        /**
         * Define Plugin name
         *
         * @since 1.0
         */
        if( !defined('CMOB_PLUGIN_NAME') )
        {
            define('CMOB_PLUGIN_NAME', 'CM On-Boarding');
        }

        /**
         * Define Plugin basename
         *
         * @since 1.0
         */
        if( !defined('CMOB_PLUGIN') )
        {
            define('CMOB_PLUGIN', plugin_basename(__FILE__));
        }

        /**
         * Define Plugins Pro page slug
         *
         * @since 1.0
         */
        if( !defined('CMOB_PRO_OPTION') )
        {
            define('CMOB_PRO_OPTION', CMOB_SLUG_NAME . '-pro');
        }
    }

    public static function _install()
    {
        //no code
        return;
    }

    public static function _uninstall()
    {
        //no code
        return;
    }

    /**
     * Get localized string.
     *
     * @param string $msg
     * @return string
     */
    public static function __($msg)
    {
        return __($msg, CMOB_SLUG_NAME);
    }

    /**
     * Get item meta
     *
     * @param string $msg
     * @return string
     */
    public static function meta($id, $key, $default = null)
    {
        $result = get_post_meta($id, $key, true);
        if( $default !== null )
        {
            $result = !empty($result) ? $result : $default;
        }
        return $result;
    }

    public static function proOnlyMsg()
    {
        $href = admin_url('admin.php?page='.CMOB_SLUG_NAME.'-pro');
        return '<div class="cmob-pro-only"><div><strong>Only in PRO!</strong> Go <a href="'.$href.'" class="button button-primary">PRO!</a></div></div>';
    }

    /**
     * Returns the author Url (for free version only)
     */
    public static function getAuthorUrl()
    {
        /*
         * By leaving following snippet in the code, you're expressing your gratitude to creators of this plugin. Thank You!
         */
        $authorUrl = '<div style="display:block;clear:both;"></div><span class="cmetg_poweredby">';
        $authorUrl .= '<a href="http://cminds.com/" target="_blank" class="cmetg_poweredbylink">CreativeMinds WordPress</a>';
        $authorUrl .= ' <a href="http://plugins.cminds.com/" target="_blank" class="cmetg_poweredbylink">Plugin</a>';
        $authorUrl .= ' <a href="http://plugins.cminds.com/" target="_blank" class="cmetg_poweredbylink">' . CMOB_NAME . '</a>';
        $authorUrl .= '</span><div style="display:block;clear:both;"></div>';

        return $authorUrl;
    }

}

/**
 * The main function responsible for returning the one true plugin class
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $marcinPluginPrototype = MarcinPluginPrototypePlugin(); ?>
 *
 * @since 1.0
 * @return object The one true EDD_Remarkety Instance
 */
function CMOnBoardingInit()
{
    return CMOnBoarding::instance();
}

// Get CMOnBoardingInit
$cmOnBoarding = CMOnBoardingInit();

//Installation
register_activation_hook(__FILE__, array('CMOnBoarding', '_install'));
//Uninstallation
register_deactivation_hook(__FILE__, array('CMOnBoarding', '_uninstall'));
