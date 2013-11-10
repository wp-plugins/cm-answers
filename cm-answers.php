<?php

/*
  Plugin Name: CM Answers
  Plugin URI: http://answers.cminds.com/
  Description: Allow users to post questions and answers (Q&A) in a stackoverflow style forum which is easy to use and install. Easy social integration & Customization
  Author: CreativeMindsSolutions
  Version: 2.0.8
 */

/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
load_plugin_textdomain( 'cm-answers', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
if (version_compare('5.3', phpversion(), '>')) {
    die(sprintf(__('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s) - please upgrade or contact your system administrator.'), phpversion()));
}

//Define constants

define('CMA_PREFIX', 'CMA_');
define('CMA_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
define('CMA_URL', plugins_url('', __FILE__));
//Init the plugin
require_once CMA_PATH . '/lib/CMA.php';
register_activation_hook(__FILE__, array('CMA', 'install'));
register_uninstall_hook(__FILE__, array('CMA', 'uninstall'));
CMA::init();
?>