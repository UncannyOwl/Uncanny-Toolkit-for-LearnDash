<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Thanks to @johnbillion
 *
 * @link      https://github.com/johnbillion/user-switching
 * @author    John Blackbourn <john@johnblackbourn.com>
 * @copyright 2009-2019 John Blackbourn
 * @license   GPL v2 or later
 * @pluginURI:   https://johnblackbourn.com/wordpress-plugin-user-switching/
 * @authorURI:   https://github.com/johnbillion/user-switching/graphs/contributors
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

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
		add_action( 'wp_footer', [ __CLASS__, 'add_focus_mode_support' ] );
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
	/**
	 *
	 */
	public static function plugins_loaded() {

		// If user switching plugin is active. Make sure we turn on off the user switching module
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'user-switching/user-switching.php' ) ) {
			$active_classes = get_option( 'uncanny_toolkit_active_classes', 0 );
			if ( 0 !== $active_classes ) {
				if ( is_array( $active_classes ) && isset( $active_classes['uncanny_learndash_toolkit\UserSwitching'] ) ) {
					deactivate_plugins( 'user-switching/user-switching.php' );
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

				// Version 1.5.4 | By John Blackbourn
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
	 * @return array $class_details
	 * @since 1.0.0
	 *
	 * @see   uncanny_learndash_toolkit/AdminMenu::get_class_details()
	 *
	 */
	public static function get_details() {
		$module_id = 'user-switching';
		
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
		$category   = 'wordpress';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
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
	/**
	 * @return bool|mixed
	 */
	public static function dependants_exist() {
		// Return true dependency is available
		return true;
	}

	/**
	 *
	 */
	public static function add_focus_mode_support() {
		?>
        <script>
            jQuery(document).ready(function () {
                jQuery('p#user_switching_switch_on').css('position', 'relative');
            })
        </script>
		<?php
		//Support LearnDash focus mode
		if ( class_exists( 'LearnDash_Settings_Section' ) ) {
			$focus_mode = \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );
			if ( 'yes' === $focus_mode ) {
				?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery('p#user_switching_switch_on').css('float', 'right').css('padding-right', '10px');
                    })
                </script>
				<?php
			}
		}
	}

}
