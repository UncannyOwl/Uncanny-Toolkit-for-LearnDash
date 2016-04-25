<?php
/*
Plugin Name: Uncanny LearnDash Toolkit
Version: 1.2.3
Description: Extend the LearnDash plugin with a variety of complementary features to make building engaging learner experiences even easier.
Author: www.uncannyowl.com
Author URI: www.uncannyowl.com
Plugin URI: www.uncannyowl.com/uncanny-learndash-toolkit/
Text Domain: uncanny-learndash-toolkit
Domain Path: /languages
*/

// All Class instance are store in Global Variable $uncanny_learndash_toolkit
global $uncanny_learndash_toolkit;

// On first activation, redirect to toolkit settings page
register_activation_hook( __FILE__, 'uncanny_learndash_toolkit_plugin_activate' );
add_action( 'admin_init', 'uncanny_learndash_toolkit_plugin_redirect' );
add_action( 'admin_notices', 'learndash_version_notice' );


function uncanny_learndash_toolkit_plugin_activate( $wp = '4.0', $php = '5.3', $learn_dash = '2.1' ) {
	global $wp_version;

	$learn_dash_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/sfwd-lms/sfwd_lms.php', $markup = true, $translate = true );
	$uot_data        = get_plugin_data( dirname( __FILE__ ) . '/uncanny-learndash-toolkit.php', $markup = true, $translate = true );

	if ( version_compare( PHP_VERSION, $php, '<' ) ) {
		$version = $php;
		$current = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
		deactivate_plugins( basename( __FILE__ ) );
		exit( sprintf( '<p>The <strong>%s</strong> requires PHP version %s or higher (5.6 or higher is recommended). 
						Because you are using an unsupported version of PHP (%s), the Toolkit plugin will be automatically deactivated. 
						Please contact your hosting company to upgrade to PHP 5.6 or higher.</p>', $uot_data['Name'], $version, $current ) );
	} elseif ( version_compare( $wp_version, $wp, '<' ) ) {
		$flag    = 'WordPress';
		$version = $wp;
		$current = $wp_version;
		deactivate_plugins( basename( __FILE__ ) );
		exit( sprintf( '<p>The <strong>%s</strong> plugin requires %s version %s or greater. Your current version is %s.</p>', $uot_data['Name'], $flag, $version, $current ) );
	} elseif ( version_compare( $learn_dash_data['Version'], $learn_dash, '<' ) ) {
		$version = $learn_dash;
		$current = $learn_dash_data['Version'];
		deactivate_plugins( basename( __FILE__ ) );
		exit( sprintf( '<p>The <strong>%s</strong> requires LearnDash version %s or higher to work properly. 
						Please make sure you have version %s or higher installed before using any LearnDash modules in the Toolkit.
						Your current version is: %s</p>', $uot_data['Name'], $version, $version, $current ) );
	} else {
		update_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'yes' );
	}
}

function learndash_version_notice() {
	$learn_dash_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/sfwd-lms/sfwd_lms.php', $markup = true, $translate = true );
	if ( version_compare( $learn_dash_data['Version'], '2.2', '<' ) && ( isset( $_REQUEST['page'] ) && 'uncanny-learnDash-toolkit' === $_REQUEST['page'] ) ) {
		?>
		<div class="notice notice-error">
			<h3><?php esc_html_e( 'The Uncanny LearnDash Toolkit requires LearnDash version 2.2 or higher to work properly. 
						Please make sure you have version 2.2 or higher installed before using any LearnDash modules in the Toolkit.
						Your current version is: ' . $learn_dash_data['Version'], 'uncanny-learndash-toolkit' ); ?></h3>
		</div>
		<?php
	}
}

function uncanny_learndash_toolkit_plugin_redirect() {
	if ( 'yes' === get_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no' ) ) {

		update_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no' );

		if ( ! isset( $_GET['activate-multi'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=uncanny-learnDash-toolkit' ) );
		}
	}
}

// Add settings link on plugin page
$uncanny_learndash_toolkit_plugin_basename = plugin_basename( __FILE__ );

add_filter( 'plugin_action_links_' . $uncanny_learndash_toolkit_plugin_basename, 'uncanny_learndash_toolkit_plugin_settings_link' );

function uncanny_learndash_toolkit_plugin_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=uncanny-learnDash-toolkit' ) . '">Settings</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

// Allow Translations to be loaded
add_action( 'plugins_loaded', 'uncanny_learndash_toolkit_text_domain' );

function uncanny_learndash_toolkit_text_domain() {
	load_plugin_textdomain( 'uncanny-learndash-toolkit', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Load breadcrumb front-end plugin function
include_once( dirname( __FILE__ ) . '/src/includes/uncanny-breadcrumbs-function.php' );

// Plugins Configurations File
include_once( dirname( __FILE__ ) . '/src/config.php' );

// Load all plugin classes(functionality)
include_once( dirname( __FILE__ ) . '/src/boot.php' );

$uncanny_learndash_toolkit = \uncanny_learndash_toolkit\Boot::get_instance();
