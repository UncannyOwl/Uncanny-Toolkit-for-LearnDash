<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class HideAdminBar extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {
			/* Hide admin bar on frontend for the user role */
			add_filter( 'show_admin_bar', array( __CLASS__, 'show_admin_bar' ), 11 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'hide-admin-bar';
		$class_title       = esc_html__( 'Hide Admin Bar', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/hide-admin-bar/';
		$class_description = esc_html__( 'Hides the Admin Bar at the top of WordPress pages based on the user role.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-minus-square-o"></i>';
		$category          = 'wordpress';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => self::get_class_settings( $class_title ),
			'icon'             => $class_icon,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {
		// Return true if no dependency or dependency is available

		return true;
	}

	/**
	 * HTML for modal to create settings
	 *
	 * @param $class_title
	 *
	 * @return bool | string Return either false or settings html modal
	 *
	 */
	public static function get_class_settings( $class_title ) {

		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
			$roles    = $wp_roles->get_names();
		} else {
			$roles = $wp_roles->get_names();
		}

		$options = array();

		foreach ( $roles as $role_value => $role_name ) {
			$role = get_role($role_value);
			$capabilities = $role->capabilities;
			if( ! isset( $capabilities['manage_options'] ) || $capabilities['manage_options'] !== true){
				array_push( $options, array( 'type' => 'checkbox', 'label' => $role_name, 'option_name' => $role_value ) );
			}
		}

		// Build html
		$html = self::settings_output( array(
			'class'   => __CLASS__,
			'title'   => $class_title,
			'options' => $options,
		) );

		return $html;
	}

	/**
	 * Hide admin bar on frontend for the user role
	 *
	 * @return boolean
	 *
	 */
	public static function show_admin_bar( $hide_admin ) {

		if ( is_user_logged_in() ) {
			if ( is_multisite() ) {
				$user       = new \WP_User(
					get_current_user_id(),
					'',
					get_current_blog_id()
				);
				$user_roles = $user->roles;
				if ( empty( $user_roles ) ) {
					$user       = new \WP_User(
						get_current_user_id(),
						'',
						1
					);
					$user_roles = $user->roles;
				}
				$hide_roles = get_blog_option( get_current_blog_id(), 'HideAdminBar', '' );
			} else {
				$user_roles = wp_get_current_user()->roles;
				$hide_roles = get_option( 'HideAdminBar', '' );
			}
			// if user has manage_option cap.
			if ( current_user_can( 'manage_options' ) ) {
				return $hide_admin;
			}
			
			if ( $hide_roles ) {
				foreach ( $hide_roles as $role ) {

					if ( 'on' === $role['value'] && in_array( $role['name'], $user_roles ) ) {
						return false;
					}
				}
			}

			return $hide_admin;
		}

		return false;
	}
}
