<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class UserSwitching
 *
 * @package     uncanny_learndash_toolkit
 * @since       2.2
 *
 */
class UserSwitching extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 *
	 * The construct is only executed when the class is defined as active. If class is in the array retrieved by
	 * get_option( 'uncanny_toolkit_active_classes' )  will create a new class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
	}

	/*
	 * Initialize actions, filters, and/or custom functions
	 *
	 * Most, if not all, functions should be run after plugins have been loaded. This will give access to modify and/or
	 * override functions for any external plugin or theme. We can also check if a plugin or theme exists before
	 * executing any action, filters, and/or extending classes from it.
	 *
	 * @since 1.0.0
	 */
	public static function plugins_loaded() {


		// If user switching plugin is active. Make sure we turn on off the user switching module
		// TODO not working yet
		if ( in_array( 'user-switching\user-switching.php', (array) get_option( 'active_plugins', array() ) ) ) {

			deactivate_plugins( '/plugin-folder/plugin-name.php' );

			$active_classes = get_option( 'uncanny_toolkit_active_classes', 0 );

			if ( 0 !== $active_classes ) {
				if ( is_array( $active_classes ) && isset( $active_classes['uncanny_learndash_toolkit\UserSwitching'] ) ) {
					unset( $active_classes['uncanny_learndash_toolkit\UserSwitching'] );
					$new_classes = $active_classes;
					update_option( 'uncanny_toolkit_active_classes', $new_classes );
				}
			}

		} else {

			if ( ! class_exists( 'user_switching' ) ) {


				// User Switching's auth_cookie
				if ( ! defined( 'USER_SWITCHING_COOKIE' ) ) {
					define( 'USER_SWITCHING_COOKIE', 'wordpress_user_sw_' . COOKIEHASH );
				}

				// User Switching's secure_auth_cookie
				if ( ! defined( 'USER_SWITCHING_SECURE_COOKIE' ) ) {
					define( 'USER_SWITCHING_SECURE_COOKIE', 'wordpress_user_sw_secure_' . COOKIEHASH );
				}

				// User Switching's logged_in_cookie
				if ( ! defined( 'USER_SWITCHING_OLDUSER_COOKIE' ) ) {
					define( 'USER_SWITCHING_OLDUSER_COOKIE', 'wordpress_user_sw_olduser_' . COOKIEHASH );
				}

				// Version 1.2.0 | By John Blackbourn
				require_once( Config::get_include( 'user-switching.php' ) );
				\user_switching::get_instance();

			}
		}
	}

	/**
	 * Detailed description of module
	 *
	 * This information is only loaded in the admin settings page to create a module which includes an on/off switch
	 * and settings modal pop up that populates module options in the WP DB. The details are retrieve by creating a
	 * reflection class(http://php.net/manual/en/class.reflectionclass.php). The class does not need to be initialized to get the details
	 * @see uncanny_learndash_toolkit/AdminMenu::get_class_details()
	 *
	 * @since 1.0.0
	 *
	 * @return array $class_details
	 */
	public static function get_details() {

		/*
		 * Link to class's associated knowledge base article
		 *
		 * @type Null Will NOT show a link on the plugins settings page
		 * @type String Will show a link icon on the user facing module
		 */
		$kb_link = 'https://www.uncannyowl.com/knowledge-base/user-switching-module/';

		// Title should match the post_title from Knowledge base article
		$class_title = esc_html__( 'User Switching', 'uncanny-learndash-toolkit' );

		/*
		 * Description should be adapted from the post_content in the knowledge base article. Exclude any technical
		 * documentation including: shortcodes, attributes, use cases, images, and/or steps. (approx. 25-200 characters)
		 */
		$class_description = esc_html__( 'Easily and securely switch between users.  Great for testing your site and troubleshooting user issues.', 'uncanny-learndash-toolkit' );

		/*
		 * Settings define the inputs that are added to the settings modal pop. If no seetings are needed, false can be
		 * passed and a modal and settings icon like will not be added to the module
		 *
		 * @type Array will create an html output of input within a modal pop up
		 * @type Bool false Will exclude a settings link and modal pop-up
		 */
		$class_settings = false;

		/*
		 * Check if the class is dependant on another plugin and/or theme
		 * @see dependants_exist();
		 *
		 * @type bool true The class does not depend on any external plugins and/or themes
		 * @type function Checks for dependencies
		 */
		$class_dependents_available = true;

		$class_icon = '<i class="uo_icon_fa fa fa-exchange"></i>';
		$type       = 'free';
		$tags       = 'user-handling'; //learndash | general | user-handling

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'tags'             => $tags,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => $class_dependents_available,
			'settings'         => $class_settings,
			'icon'             => $class_icon,
		);

	}

	/*
	 * Checks if the class is dependant on another variable, function, plugin and/or theme
	 *
	 * If the dependency does not exists then the on/off switch on the module is replace with a message.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean || string
	 */
	public static function dependants_exist() {
		// Return true dependency is available
		return true;
	}

}
