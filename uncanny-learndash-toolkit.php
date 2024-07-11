<?php
/*
 * Plugin Name:         Uncanny Toolkit for LearnDash
 * Description:         Extend LearnDash with a variety of complementary features to make building engaging learner experiences even easier.
 * Author:              Uncanny Owl
 * Author URI:          https://www.uncannyowl.com
 * Plugin URI:          https://www.uncannyowl.com/uncanny-learndash-toolkit/
 * Text Domain:         uncanny-learndash-toolkit
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Version:             3.7.0.1
 * Requires at least:   5.4
 * Requires PHP:        7.4
*/

// All Class instance are store in Global Variable $uncanny_learndash_toolkit
global $uncanny_learndash_toolkit;

// Define version
if ( ! defined( 'UNCANNY_TOOLKIT_VERSION' ) ) {
	/**
	 *
	 */
	define( 'UNCANNY_TOOLKIT_VERSION', '3.7.0.1' );
}

// Define prefix
if ( ! defined( 'UNCANNY_TOOLKIT_PREFIX' ) ) {
	/**
	 *
	 */
	define( 'UNCANNY_TOOLKIT_PREFIX', 'ult' );
}

// Register REST API endpoint
if ( ! defined( 'UNCANNY_TOOLKIT_REST_API_END_POINT' ) ) {
	/**
	 *
	 */
	define( 'UNCANNY_TOOLKIT_REST_API_END_POINT', 'ult/v2' );
}

// Define the main plugin file
if ( ! defined( 'UNCANNY_TOOLKIT_FILE' ) ) {
	/**
	 *
	 */
	define( 'UNCANNY_TOOLKIT_FILE', __FILE__ );
}

// Define the main plugin directory
if ( ! defined( 'UNCANNY_TOOLKIT_DIR' ) ) {
	/**
	 *
	 */
	define( 'UNCANNY_TOOLKIT_DIR', dirname( __FILE__ ) );
}

// Allow Translations to be loaded
add_action( 'plugins_loaded', 'uncanny_learndash_toolkit_text_domain' );

/**
 *
 */
function uncanny_learndash_toolkit_text_domain() {
	load_plugin_textdomain( 'uncanny-learndash-toolkit', false, basename( UNCANNY_TOOLKIT_FILE ) . '/languages/' );
}

// Add settings link on plugin page
$uncanny_learndash_toolkit_plugin_basename = plugin_basename( UNCANNY_TOOLKIT_FILE );


/**
 * @param $links
 *
 * @return mixed
 */
function uo_settings_link( $links ) {
	$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=uncanny-toolkit' ), __( 'Modules', 'uncanny-learndash-toolkit' ) );
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . $uncanny_learndash_toolkit_plugin_basename, 'uo_settings_link' );

/**
 * @param $links
 *
 * @return mixed
 */
function uo_upgrade_to_pro_link( $links ) {
	if ( ! defined( 'UNCANNY_TOOLKIT_PRO_PATH' ) ) {
		$settings_link = sprintf( '<a href="%s" target="_blank" style="color: rgb(0, 163, 42); font-weight: 700;">%s</a>', 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/?utm_source=uncanny_toolkit&utm_medium=plugins_page&utm_content=update_to_pro', __( 'Upgrade to Pro', 'uncanny-learndash-toolkit' ) );

		array_unshift( $links, $settings_link );
	}

	return $links;
}

add_filter( 'plugin_action_links_' . $uncanny_learndash_toolkit_plugin_basename, 'uo_upgrade_to_pro_link', 99 );

// Load breadcrumb front-end plugin function
require_once UNCANNY_TOOLKIT_DIR . '/src/includes/uncanny-breadcrumbs-function.php';

// Plugins Configurations File
require_once UNCANNY_TOOLKIT_DIR . '/src/config.php';

// Include the two factor functions.
require_once UNCANNY_TOOLKIT_DIR . '/src/includes/uo-toolkit-2fa-functions.php';

// Load all plugin classes(functionality)
require_once UNCANNY_TOOLKIT_DIR . '/src/boot.php';

$boot                            = '\uncanny_learndash_toolkit\Boot';
$uncanny_learndash_toolkit_class = new $boot();

/**
 * Load notifications.
 */
require_once __DIR__ . '/src/notifications/notifications.php';

if ( class_exists( '\Uncanny_Owl\Notifications' ) ) {

	$notifications = new \Uncanny_Owl\Notifications();

	// On activate, persists/update `uncanny_owl_over_time_toolkit-free`.
	register_activation_hook(
		__FILE__,
		function() {
			update_option( 'uncanny_owl_over_time_toolkit-free', array( 'installed_date' => time() ), false );
		}
	);

	// Initiate the Notifications handler, but only load once.
	if ( false === \Uncanny_Owl\Notifications::$loaded ) {

		$notifications::$loaded = true;

		add_action( 'admin_init', array( $notifications, 'init' ) );

	}
}
/**
 * Load Uncanny Owl Assets.
 */
require_once __DIR__ . '/src/learndash-plugins-page/learndash-plugins-page.php';
