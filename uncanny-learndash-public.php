<?php
/*
Plugin Name: Uncanny LearnDash Functionality
Version: 1.0
Description: There is a lot
Author: uncannyowl.com
Author URI: uncannyowl.com
Plugin URI: uncannyowl.com
Text Domain: uncanny_learndash_public
Domain Path: /languages
*/

// All Class instance are store in Global Variable $uncanny_learndash_public
global $uncanny_learndash_public;


if ( ! isset( $uncanny_learndash_public ) ) {
	$uncanny_learndash_public = new \stdClass();
}

if (!defined('ULP_PLUGIN_BASENAME'))
	define('ULP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if (!defined('ULP_PLUGIN_NAME'))
	define('ULP_PLUGIN_NAME', trim( ULP_PLUGIN_BASENAME ), '/' );

if (!defined('ULP_PLUGIN_DIR'))
	define('ULP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MYPLUGIN_PLUGIN_NAME);

if (!defined('ULP_PLUGIN_URL'))
	define('ULP_PLUGIN_URL', WP_PLUGIN_URL . '/' . MYPLUGIN_PLUGIN_NAME);

// Plugins Configurations File
include_once( dirname( __FILE__ ). '/src/config.php');

// Load all plugin classes(functionality)
include_once( dirname( __FILE__ ). '/src/boot.php');
$uncanny_learndash_public = \uncanny_learndash_public\Boot::get_instance();