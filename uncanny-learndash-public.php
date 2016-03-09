<?php
/*
Plugin Name: Uncanny LearnDash Functionality
Version: 0.1
Description: This plugin adds a variety of functions to LearnDash sites that help improve the learner experience and LearnDash development workflow. After building dozens of LearnDash platforms, we combined the functions that are common across LearnDash sites into this single plugin.
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

if ( ! defined( 'ULP_PLUGIN_BASENAME' ) ) {
	define( 'ULP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'ULP_PLUGIN_NAME' ) ) {
	define( 'ULP_PLUGIN_NAME', trim( ULP_PLUGIN_BASENAME ), '/' );
}

if ( ! defined( 'ULP_PLUGIN_DIR' ) ) {
	define( 'ULP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . ULP_PLUGIN_NAME );
}

if ( ! defined( 'ULP_PLUGIN_URL' ) ) {
	define( 'ULP_PLUGIN_URL', WP_PLUGIN_URL . '/' . ULP_PLUGIN_NAME );
}

// Plugins Configurations File
include_once( dirname( __FILE__ ) . '/src/config.php' );

// Load all plugin classes(functionality)
include_once( dirname( __FILE__ ) . '/src/boot.php' );

$uncanny_learndash_public = \uncanny_learndash_public\Boot::get_instance();
