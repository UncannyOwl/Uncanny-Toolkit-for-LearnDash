<?php
/*
Plugin Name: Uncanny LearnDash Toolkit
Version: 0.2.5
Description: Extend the LearnDash plugin with a variety of complementary features to make building engaging learner experiences even easier.
Author: www.uncannyowl.com
Author URI: www.uncannyowl.com
Plugin URI: www.uncannyowl.com/uncanny-learndash-toolkit/
Text Domain: uncanny_learndash_toolkit
Domain Path: /languages
*/

// All Class instance are store in Global Variable $uncanny_learndash_toolkit
global $uncanny_learndash_toolkit;

// On first activation, redirect to toolkit settings page
register_activation_hook(__FILE__, 'uncanny_learndash_toolkit_plugin_activate');
add_action('admin_init', 'uncanny_learndash_toolkit_plugin_redirect');

function uncanny_learndash_toolkit_plugin_activate() {
	update_option('uncanny_learndash_toolkit_plugin_do_activation_redirect', 'yes' );
}
function uncanny_learndash_toolkit_plugin_redirect() {
	if ( 'yes' === get_option('uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no') ) {

		update_option('uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no');

		if(!isset($_GET['activate-multi']))
		{
			wp_redirect("admin.php?page=uncanny-learnDash-toolkit");
		}
	}
}

// Add settings link on plugin page
$uncanny_learndash_toolkit_plugin_basename = plugin_basename(__FILE__);

add_filter( 'plugin_action_links_'.$uncanny_learndash_toolkit_plugin_basename, 'uncanny_learndash_toolkit_plugin_settings_link' );

function uncanny_learndash_toolkit_plugin_settings_link($links) {
	$settings_link = '<a href="admin.php?page=uncanny-learnDash-toolkit">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}


// Plugins Configurations File
include_once( dirname( __FILE__ ) . '/src/config.php' );

// Load all plugin classes(functionality)
include_once( dirname( __FILE__ ) . '/src/boot.php' );

$uncanny_learndash_toolkit = \uncanny_learndash_toolkit\Boot::get_instance();