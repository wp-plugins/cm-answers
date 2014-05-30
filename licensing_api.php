<?php
if( !class_exists('CMA_free_Cminds_Licensing_API') )
{

    class CMA_free_Cminds_Licensing_API
    {
        const ACTIVATE_ACTION = 'activate_license';
        const CHECK_ACTION = 'check_license';
        const GET_VERSION_ACTION = 'get_version';
        const DEACTIVATE_ACTION = 'deactivate_license';
        const NO_ACTIVATIONS_STATUS = 'no_activations_left';
        const MAX_ACTIVATION_COUNT = 1;

        private static $apiEndpointUrl = 'https://www.cminds.com/';
        private static $supportUrl = 'https://plugins.cminds.com/cm-support/';
        private static $customerAreaLoginUrl = 'https://www.cminds.com/guest-login/';
        private static $customerAreaRegisterUrl = 'https://www.cminds.com/guest-registration/';
        private $url = null;
        private $itemName = null;
        private $validItemNames = null;
        private $baseParams = null;
        private $pluginMenu = null;
        private $pluginMenuPage = null;
        private $pluginUpdateMenuPage = null;
        private $pluginName = null;
        private $pluginFile = null;
        private $pluginSlug = null;
        private $optionGroup = null;
        private $optionLicenseKey = null;
        private $optionLicenseActivateKey = null;
        private $optionLicenseDeactivateKey = null;
        private $optionLicenseStatus = null;
        private $optionCountLicenseActivations = null;
        private $optionCountLicenseMaxActivations = null;
        private $license = null;
        private $licenseStatus = null;
        private $message = '';
        private $messageError = FALSE;
        private static $instances = array();
        private $releaseNotesUrl = null;
        private $licensePageContent = null;

        public function __construct($itemName, $pluginMenu, $pluginName, $pluginFile, $pluginSpecificUrls, $pluginSlug = '', $additionalValidItemNames = null)
        {
            $this->url = get_bloginfo('wpurl');

            $this->pluginMenu = $pluginMenu;
            $this->pluginMenuPage = mb_strtolower($this->pluginMenu) . '_license';
            $this->pluginUpdateMenuPage = mb_strtolower($this->pluginMenu) . '_update';

            $this->pluginFile = $pluginFile;

            $this->releaseNotesUrl = $pluginSpecificUrls['release-notes'];

            $this->pluginName = $pluginName;
            $this->pluginSlug = $pluginSlug ? $pluginSlug : self::camelCaseToHypenSeparated($pluginName);

            $this->optionGroup = $this->pluginMenu; //'cminds-' . $this->pluginSlug . '-license';
            $this->optionLicenseKey = 'cminds-' . $this->pluginSlug . '-license-key';
            $this->optionLicenseActivateKey = 'cminds-' . $this->pluginSlug . '-license-activate';
            $this->optionLicenseDeactivateKey = 'cminds-' . $this->pluginSlug . '-license-deactivate';
            $this->optionLicenseStatus = 'cminds-' . $this->pluginSlug . '-license-status';
            $this->optionCountLicenseActivations = 'cminds-' . $this->pluginSlug . '-license-activation-count';
            $this->optionCountLicenseMaxActivations = 'cminds-' . $this->pluginSlug . '-license-max-ac';


            $this->license = trim(get_option($this->optionLicenseKey, ''));
            $this->licenseStatus = trim(get_option($this->optionLicenseStatus, ''));
            $this->itemName = $itemName;

            $this->validItemNames = array($this->itemName);
            if( $additionalValidItemNames && is_array($additionalValidItemNames) )
            {
                $this->validItemNames = array_merge($this->validItemNames, $additionalValidItemNames);
            }

            $this->baseParams = array(
                'item_name' => urlencode($this->itemName),
                'url'       => $this->url,
                'license'   => $this->license,
            );

            self::$instances[$this->optionGroup] = $this;

            add_action('admin_menu', array($this, 'update_menu'), 21);
            add_action('admin_menu', array($this, 'license_menu'), 20);

            add_action('admin_init', array($this, 'register_license_option'));

//            add_action('admin_init', array($this, 'activate_license'));
//            add_action('admin_init', array($this, 'deactivate_license'));
//            add_action('admin_notices', array($this, 'showMessage'));
//            add_action('update_option_' . $this->optionLicenseKey, array($this, 'after_new_license_key'), 10, 2);
        }

        public function license_menu()
        {
            if( has_action('cminds-answers-license-page') )
            {
                add_submenu_page($this->pluginMenu, 'License', 'License', 'manage_options', $this->pluginMenuPage, array($this, 'license_page'));
            }
        }

        public function license_page()
        {
            $license = get_option($this->optionLicenseKey);
            $status = get_option($this->optionLicenseStatus);

            ob_start();
            /*
             * Call the action in the add-ons
             */
            do_action('cminds-answers-license-page');
            $this->licensePageContent = ob_get_clean();
            ?>

            <div class="wrap">
                <h2><?php printf(__('%s - License Options'), $this->pluginName); ?></h2>

                <p><strong>Licensing instructions</strong> <a href="javascript:void(0)" onclick="jQuery(this).parent().next().slideToggle()">Show/Hide</a></p>
                <div class="cminds-licensing-instructions" style="display:none;">
                    You have two options to get your license key:
                    <ol>
                        <li>
                            <p>
                                You can get your license keys by logging in the <a target="_blank" href="<?php echo self::$customerAreaLoginUrl ?>">Cminds Customer Area</a>. <br/>
                                If you don't have an account yet. You have to first <a target="_blank" href="<?php echo self::$customerAreaRegisterUrl ?>">register</a> using the e-mail you've used for the purchase. <br/>
                                Your license key will be available as shown in the screenshot below.
                            </p>
                            <img title="Cminds Customer Area screenshot" alt="Example Cminds Customer Area screenshot" src="<?php echo plugin_dir_url(__FILE__) ?>cminds_user_area.png" />
                        </li>
                        <li>
                            <p>
                                You can get the license key for your product from the receipt we've sent you by email after your purchase. In the e-mail there's a link to the online version of the receipt. <br/>
                                The online receipt should look similar to the screenshot below.
                            </p>
                            <img height="400" title="Example Cminds receipt with license key" alt="Example Cminds receipt" src="<?php echo plugin_dir_url(__FILE__) ?>cminds_receipt.png" />
                        </li>
                    </ol>

                    <p>
                        Your license key should be a string of 32 characters (letters and digits). <br/>
                        If there's no license key on the customer page nor online receipt, please <a target="_blank" href="<?php echo self::$supportUrl ?>">contact support</a>.
                    </p>
                </div>

                <p>
                    Please activate your license key according to the amount of licenses you purchased. <br/>
                    If you want to move your plugin to another site please deactivate first before moving and reactivating. <br/>
                    In order to activate the plugin you have to paste the code and "Save changes" then click the "Activate" button. <br/>
                </p>

                <form method="post" action="options.php">

                    <?php settings_fields($this->optionGroup); ?>

                    <table class="form-table">
                        <tbody>
                            <?php
                            echo $this->licensePageContent;
                            ?>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }

        public function update_menu()
        {
            if( has_action('cminds-answers-update-page') )
            {
                add_submenu_page($this->pluginMenu, 'Version Update', 'Version Update', 'manage_options', $this->pluginUpdateMenuPage, array($this, 'update_page'));
            }
        }

        public function update_page()
        {
            $versionInfo = $this->getUpdateInfo();

            ob_start();
            /*
             * Call the action in the add-ons
             */
            do_action('cminds-answers-update-page');
            $this->updatePageContent = ob_get_clean();
            ?>

            <div class="wrap">

                <h2><?php printf(__('%s - Version Update'), $this->pluginName); ?></h2>

                <p><strong>Updating instructions</strong> <a href="javascript:void(0)" onclick="jQuery(this).parent().next().slideToggle()">Show/Hide</a></p>
                <div class="cminds-updating-instructions" style="display:none">
                    <ol>
                        <li>
                            <p>
                                You can get your downloads by logging in the <a target="_blank" href="<?php echo self::$customerAreaLoginUrl ?>">Cminds Customer Area</a>. <br/>
                                If you don't have an account yet. You have to first <a target="_blank" href="<?php echo self::$customerAreaRegisterUrl ?>">register</a> using the e-mail you've used for the purchase. <br/>
                                Your download files will be available as shown in the screenshot below.
                            </p>
                            <img title="Example Customer area with download link" alt="Example Cminds update - download link" src="<?php echo plugin_dir_url(__FILE__) ?>cminds_update.png" />
                        </li>
                        <li>
                            <p>
                                After downloading the latest version. Please follow these steps:
                            </p>
                            <ol>
                                <li>
                                    Deactivate the <?php echo $this->pluginName ?>
                                </li>
                                <li>
                                    Delete the <?php echo $this->pluginName ?> files. <br/>
                                    <strong>The plugin's data WILL NOT be erased.</strong>
                                </li>
                                <li>
                                    Install the new version of the plugin either through the Dashboard or the FTP
                                </li>
                                <li>
                                    Activate the new version of <?php echo $this->pluginName ?>
                                </li>
                            </ol>
                        </li>
                        <li>
                            <p>
                                For a list of the changes in each of the versions please look at the <a target="_blank" href="<?php echo $this->releaseNotesUrl ?>">Release Notes</a>.
                            </p>
                        </li>
                    </ol>
                </div>

                <?php
                /*
                 * Call the action in the add-ons
                 */
                echo $this->updatePageContent;
                ?>
            </div>
            <?php
        }

        public function register_license_option()
        {
            // creates our settings in the options table
            register_setting($this->optionGroup, $this->optionLicenseKey, array($this, 'sanitize_license'));
        }

        public function sanitize_license($new)
        {
            $old = get_option($this->optionLicenseKey);
            if( $old && $old != $new )
            {
                delete_option($this->optionLicenseStatus); // new license has been entered, so must reactivate
            }
            if( !$new )
            {
                delete_option($this->optionLicenseKey);
                return false;
            }
            return $new;
        }

        /**
         * Shows the message
         */
        public function showMessage()
        {
            $this->display_license_message();

            /*
             * Only show to admins
             */
            if( current_user_can('manage_options') && !empty($this->message) )
            {
                cminds_show_message($this->message, $this->messageError);
            }
        }

        /**
         * Returns the list of API actions
         * @return string
         */
        private function get_valid_actions()
        {
            $validActions = array(self::ACTIVATE_ACTION, self::DEACTIVATE_ACTION, self::GET_VERSION_ACTION, self::CHECK_ACTION);
            return $validActions;
        }

        /**
         * API call to the licencing server
         *
         * @param type $action
         * @param type $params
         * @return boolean
         */
        private function api_call($action = '')
        {
            $apiCallResults = array();

            foreach($this->validItemNames as $itemName)
            {
                $this->baseParams['item_name'] = urlencode($itemName);

                if( in_array($action, self::get_valid_actions()) )
                {
                    $params = array_merge(array('edd_action' => $action), $this->baseParams);
                }
                else
                {
                    $apiCallResults[] = false;
                }

                $url = add_query_arg($params, self::$apiEndpointUrl);
                $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));

                if( is_wp_error($response) )
                {
                    $apiCallResults[] = false;
                }

                $license_data = json_decode(wp_remote_retrieve_body($response));
                $apiCallResults[] = $license_data;
            }

            foreach($apiCallResults as $callResult)
            {
                if( $callResult !== FALSE )
                {
                    if( is_object($callResult) )
                    {
                        $possibleResult = $callResult;

                        /*
                         * Return immediately if there's a success
                         */
                        if( (isset($possibleResult->success) && $possibleResult->success == true) || !empty($possibleResult->new_version) )
                        {
                            return $possibleResult;
                        }
                    }
                }
            }

            /*
             * Return the result with 'error'
             */
            if( is_object($possibleResult) )
            {
                return $possibleResult;
            }

            /*
             * None of the call results is different than FALSE
             */
            return FALSE;
        }

        public function display_license_message()
        {
            $licenseStatus = get_option($this->optionLicenseStatus);

            switch($licenseStatus)
            {
                case self::NO_ACTIVATIONS_STATUS:
                    /*
                     * This license activation limit has beeen reached
                     */
                    $this->message = 'Your have reached your activation limit for "' . $this->pluginName . '"! <br/>'
                            . 'Please, purchase a new license or contact <a target="_blank" href="' . self::$supportUrl . '">support</a>.';
                    $this->messageError = TRUE;
                    break;
                case 'deactivated':

                case 'site_inactive':
                case 'inactive':
                    /*
                     * This license is invalid / either it has expired or the key was invalid
                     */
                    $this->message = 'Your license key provided for "' . $this->pluginName . '" is inactive! <br/>'
                            . 'Please, go to <a href="' . add_query_arg(array('page' => $this->pluginMenuPage), admin_url('admin.php')) . '">plugin\'s License page</a> and click "Activate License".';
                    $this->messageError = TRUE;
                    break;
                case 'invalid':
                    /*
                     * This license is invalid / either it has expired or the key was invalid
                     */
                    $this->message = 'Your license key provided for "' . $this->pluginName . '" is invalid! <br/>'
                            . 'Please go to <a href="' . add_query_arg(array('page' => $this->pluginMenuPage), admin_url('admin.php')) . '">plugin\'s License page</a> for the licencing instructions.';
                    $this->messageError = TRUE;
                    break;
                case '':
                    /*
                     * This license is invalid / either it has expired or the key was invalid
                     */
                    $this->message = 'To use "' . $this->pluginName . '" you have to provide a valid license key! <br/>'
                            . 'Please go to <a href="' . add_query_arg(array('page' => $this->pluginMenuPage), admin_url('admin.php')) . '">plugin\'s License page</a> to enter your license.';
                    $this->messageError = TRUE;
                    break;

                default:
                    break;
            }
        }

        public function activate_license()
        {
            $post = filter_input(INPUT_POST, $this->optionLicenseActivateKey);
            $pluginPage = filter_input(INPUT_POST, 'option_page');

            /*
             *  listen for our activate button to be clicked
             */
            if( !$post )
            {
                return;
            }

            /*
             * Switch API instance
             */
            if( $pluginPage !== $this->optionGroup )
            {
                self::$instances[$pluginPage]->activate_license();
                return;
            }

            // run a quick security check
            if( !check_admin_referer("$this->optionGroup-options") )
            {
                // get out if we didn't click the button
                return;
            }

            $result = self::api_call(self::ACTIVATE_ACTION);

            if( $result === false )
            {
                cminds_show_message('Error', true);
            }
            else
            {
                /*
                 * Special case when the activation limit is reached
                 */
                if( isset($result->error) && $result->error == self::NO_ACTIVATIONS_STATUS )
                {
                    $newLicenseStatus = self::NO_ACTIVATIONS_STATUS;
                }
                else
                {
                    $newLicenseStatus = $result->license;
                }

                update_option($this->optionCountLicenseActivations, $result->site_count);
                update_option($this->optionCountLicenseMaxActivations, (int) $result->license_limit);
                /*
                 * $result->license will be either "active" or "inactive"
                 */
                update_option($this->optionLicenseStatus, $newLicenseStatus);
            }
        }

        public function deactivate_license()
        {
            $post = filter_input(INPUT_POST, $this->optionLicenseDeactivateKey);
            $pluginPage = filter_input(INPUT_POST, 'option_page');

            /*
             *  listen for our activate button to be clicked
             */
            if( !$post )
            {
                return;
            }

            /*
             * Switch API instance
             */
            if( $pluginPage !== $this->optionGroup )
            {
                self::$instances[$pluginPage]->deactivate_license();
                return;
            }

            // run a quick security check
            if( !check_admin_referer("$this->optionGroup-options") )
            {
                // get out if we didn't click the button
                return;
            }

            $result = self::api_call(self::DEACTIVATE_ACTION);

            if( $result === false )
            {
                cminds_show_message('Error', true);
            }
            else
            {
                update_option($this->optionCountLicenseActivations, $result->site_count);
                /*
                 *  $license_data->license will be either "deactivated" or "failed"
                 */
                update_option($this->optionLicenseStatus, $result->license);
            }
        }

        public function after_new_license_key($a, $b)
        {
            if( $a !== $b )
            {
                $this->baseParams['license'] = trim(get_option($this->optionLicenseKey, ''));
                $this->check_license();
            }
        }

        public function check_license()
        {
            /*
             * Don't check if there's no license
             */
            if( get_option($this->optionLicenseKey) == FALSE )
            {
                return false;
            }

            $result = self::api_call(self::CHECK_ACTION);

            if( $result === false )
            {
                cminds_show_message('Error', true);
            }
            else
            {
                if( $result->license == 'valid' )
                {
                    /*
                     * This license is valid
                     */
                }
                else
                {
                    /*
                     * $result->license will be either "active" or "inactive"
                     */
                    update_option($this->optionLicenseStatus, $result->license);
                }
            }
        }

        /**
         * Get the version information from the server
         * @return type
         */
        public function get_version()
        {
            $pluginPage = filter_input(INPUT_GET, 'page');

            /*
             * Switch API instance
             */
            if( $pluginPage !== $this->pluginUpdateMenuPage )
            {
                self::$instances[$this->optionGroup]->get_version();
                return;
            }

            $result = self::api_call(self::GET_VERSION_ACTION);

            if( $result === false )
            {
                cminds_show_message('Error', true);
            }
            else
            {
                return $result;
            }
        }

        public function getUpdateInfo()
        {
            $versionResult = $this->get_version();
            $pluginInfo = get_plugin_data($this->pluginFile);

            $currentVersion = isset($pluginInfo['Version']) ? $pluginInfo['Version'] : 'n/a';

            if( $versionResult && is_object($versionResult) && !empty($versionResult->new_version) )
            {
                $versionCompare = version_compare($versionResult->new_version, $currentVersion, '>');

                $updateInfoArr = array(
                    'current-version' => $currentVersion,
                    'newest-version'  => $versionResult->new_version,
                    'needs-update'    => $versionCompare,
                );
            }
            else
            {
                $updateInfoArr = array(
                    'current-version' => $currentVersion,
                    'newest-version'  => 'n/a',
                    'needs-update'    => true,
                );
            }

            return $updateInfoArr;
        }

        public function isLicenseOk()
        {
            $licenseActivationCount = get_option($this->optionCountLicenseActivations, 0);
            $licenseMaxActivationCount = (int) get_option($this->optionCountLicenseMaxActivations, 1);
            $licenseMaxActivationCount += self::MAX_ACTIVATION_COUNT;
            $licenseOk = !empty($this->license) && in_array($this->licenseStatus, array('valid', 'expired', 'inactive', self::NO_ACTIVATIONS_STATUS)) && $licenseActivationCount < $licenseMaxActivationCount;
            return $licenseOk;
        }

        /**
         * Change SomethingLikeThis to something-like-this
         *
         * @param str $str text to change
         * @return string
         */
        public static function camelCaseToHypenSeparated($str)
        {
            if( function_exists('lcfirst') === false )
            {

                function lcfirst($str)
                {
                    $str[0] = strtolower($str[0]);
                    return $str;
                }

            }
            return strtolower(preg_replace('/([A-Z])/', '-$1', str_replace(' ', '', lcfirst($str))));
        }

    }
}

if( !function_exists('cminds_show_message') )
{

    /**
     * Generic function to show a message to the user using WP's
     * standard CSS classes to make use of the already-defined
     * message colour scheme.
     *
     * @param $message The message you want to tell the user.
     * @param $errormsg If true, the message is an error, so use
     * the red message style. If false, the message is a status
     * message, so use the yellow information message style.
     */
    function cminds_show_message($message, $errormsg = false)
    {
        if( $errormsg )
        {
            echo '<div id="message" class="error">';
        }
        else
        {
            echo '<div id="message" class="updated fade">';
        }

        echo "<p><strong>$message</strong></p></div>";
    }

}