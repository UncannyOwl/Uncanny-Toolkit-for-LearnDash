<?php
/*
Plugin Name: Uncanny LearnDash Toolkit
Version: 3.0.1
Description: Extend the LearnDash plugin with a variety of complementary features to make building engaging learner experiences even easier.
Author: Uncanny Owl
Author URI: www.uncannyowl.com
Plugin URI: www.uncannyowl.com/uncanny-learndash-toolkit/
Text Domain: uncanny-learndash-toolkit
Domain Path: /languages
*/

// All Class instance are store in Global Variable $uncanny_learndash_toolkit
global $uncanny_learndash_toolkit;

// Define version test
if ( ! defined( 'UNCANNY_TOOLKIT_VERSION' ) ) {
	define( 'UNCANNY_TOOLKIT_VERSION', '3.0.1' );
}

// Define version test
if ( ! defined( 'UNCANNY_TOOLKIT_PREFIX' ) ) {
	define( 'UNCANNY_TOOLKIT_PREFIX', 'ultp' );
}

function ultp_notice() {
	$user_id = get_current_user_id();

	if ( empty( get_user_meta( $user_id, 'uofel_notice_dismissed' ) ) ) {
		echo '<div class="notice notice-warning below-h2">
<button id="uofel" type="button" style="    position: relative;
    top: 0;
    right: 1px;
    border: none;
    margin: 0;
    padding: 9px;
    background: 0 0;
    color: #0073aa;
    cursor: pointer;
    float: right;" >Dismiss<span class="screen-reader-text">Dismiss this notice.</span></button>
				<p><strong>IMPORTANT!</strong> The Front End Login module of the Uncanny LearnDash Toolkit changed significantly in version 3.0. Please re-check your settings and review your login page as a logged out user. </p>
			
			</div>
			<script>
jQuery("#uofel").on("click", function(){    
    key = encodeURI("uofel-dismissed"); value = encodeURI("yes");
    var kvp = document.location.search.substr(1).split("&");
    var i=kvp.length; var x; while(i--) 
    {
        x = kvp[i].split("=");

        if (x[0]==key)
        {
            x[1] = value;
            kvp[i] = x.join("=");
            break;
        }
    }

    if(i<0) {kvp[kvp.length] = [key,value].join("=");}

    //this will reload the page, it\'s likely better to store this until finished
    document.location.search = kvp.join("&"); 
    });
</script>';
	}
}

add_action( 'admin_notices', 'ultp_notice' );

function ultp_notice_dismissed() {
	$user_id = get_current_user_id();
	if ( isset( $_GET['uofel-dismissed'] ) ) {
		add_user_meta( $user_id, 'uofel_notice_dismissed', 'true', true );
	}
}

add_action( 'admin_init', 'ultp_notice_dismissed' );


// Show admin notices for minimum versions of PHP, WordPress, and LearnDash
add_action( 'admin_notices', 'learndash_version_notice' );

function learndash_version_notice() {

	global $wp_version;

	//Minimum versions
	$wp         = '4.0';
	$php        = '5.3';
	$learn_dash = '2.1';

	// Set LearnDash version
	$learn_dash_version = 0;
	if ( defined( 'LEARNDASH_VERSION' ) ) {
		$learn_dash_version = LEARNDASH_VERSION;
	}

	// Get current screen
	$screen = get_current_screen();

	if ( ! version_compare( PHP_VERSION, '5.6', '>=' ) ) {

		// Show notice if php version is less than 5.6
		$version = $php;
		$current = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

		?>
		<div class="notice notice-error">
			<h3><?php echo sprintf(

					esc_html__( 'The %s requires PHP version %s or higher (5.6 or higher is recommended). Because you are using an unsupported version of PHP (%s), the Toolkit plugin will not initialize. Please contact your hosting company to upgrade to PHP 5.6 or higher.', 'uncanny-learndash-toolkit'
					),

					'Uncanny LearnDash Toolkit',
					$version,
					$current ); ?>
			</h3>
		</div>
		<?php

	} elseif ( version_compare( $wp_version, $wp, '<' ) && ( isset( $_REQUEST['page'] ) && 'uncanny-toolkit' === $_REQUEST['page'] ) ) {

		// Show notice if WP version is less than 4.0 and the current page is the Toolkit settings page
		$flag    = 'WordPress';
		$version = $wp;
		$current = $wp_version;

		?>
		<!-- No Notice Style below WordPress -->
		<style>
			.notice-error {
				border-left-color: #dc3232 !important;
			}

			.notice {
				background: #fff;
				border-left: 4px solid #fff;
				-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				margin: 5px 15px 2px;
				padding: 1px 12px;
			}
		</style>
		<div class="notice notice-error">
			<h3><?php echo sprintf(

					esc_html__( 'The %s plugin requires %s version %s or greater. Your current version is %s.', 'uncanny-learndash-toolkit'
					),

					'Uncanny LearnDash Toolkit',
					$flag,
					$version,
					$current ); ?>
			</h3>
		</div>
		<?php

	} elseif ( ! version_compare( $learn_dash_version, $learn_dash, '>=' ) && ( isset( $_REQUEST['page'] ) && 'uncanny-toolkit' === $_REQUEST['page'] ) ) {

		// Show notice if LearnDash is less than 2.1 and the current page is the Toolkit settings page
		if ( 0 !== $learn_dash_version ) {

			?>
			<div class="notice notice-error">
				<h3><?php echo sprintf(

						esc_html__( 'The Uncanny LearnDash Toolkit requires LearnDash version 2.1 or higher to work properly. Please make sure you have version 2.1 or higher installed before using any LearnDash modules in the Toolkit. Your current version is: %s', 'uncanny-learndash-toolkit' ),

						$learn_dash_version
					); ?>
				</h3>
			</div>
			<?php

		} elseif ( class_exists( 'SFWD_LMS' ) ) {

			?>
			<div class="notice notice-error">
				<h3><?php echo sprintf(

						esc_html__( 'The Uncanny LearnDash Toolkit requires LearnDash version 2.1 or higher to work properly. Please make sure you have version 2.1 or higher installed before using any LearnDash modules in the Toolkit.', 'uncanny-learndash-toolkit' ),

						$learn_dash_version
					); ?>
				</h3>
			</div>
			<?php

		}

	}
}

// Allow Translations to be loaded
add_action( 'plugins_loaded', 'uncanny_learndash_toolkit_text_domain' );

function uncanny_learndash_toolkit_text_domain() {
	load_plugin_textdomain( 'uncanny-learndash-toolkit', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// PHP version 5.3 and up only
if ( version_compare( PHP_VERSION, '5.6', '>=' ) ) {

	// On first activation, redirect to toolkit settings page if min php version is met
	register_activation_hook( __FILE__, 'uncanny_learndash_toolkit_plugin_activate' );
	add_action( 'admin_init', 'uncanny_learndash_toolkit_plugin_redirect' );

	function uncanny_learndash_toolkit_plugin_activate() {
		update_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'yes' );

	}

	function uncanny_learndash_toolkit_plugin_redirect() {
		if ( 'yes' === get_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no' ) ) {

			update_option( 'uncanny_learndash_toolkit_plugin_do_activation_redirect', 'no' );

			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( admin_url( 'admin.php?page=uncanny-toolkit' ) );
			}
		}
	}

	// Add settings link on plugin page
	$uncanny_learndash_toolkit_plugin_basename = plugin_basename( __FILE__ );

	add_filter( 'plugin_action_links_' . $uncanny_learndash_toolkit_plugin_basename, 'uncanny_learndash_toolkit_plugin_settings_link' );

	function uncanny_learndash_toolkit_plugin_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=uncanny-toolkit' ) . '">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	// Load breadcrumb front-end plugin function
	include_once( dirname( __FILE__ ) . '/src/includes/uncanny-breadcrumbs-function.php' );

	// Plugins Configurations File
	include_once( dirname( __FILE__ ) . '/src/config.php' );

	// Load all plugin classes(functionality)
	include_once( dirname( __FILE__ ) . '/src/boot.php' );

	$boot                            = '\uncanny_learndash_toolkit\Boot';
	$uncanny_learndash_toolkit_class = new $boot;

}
