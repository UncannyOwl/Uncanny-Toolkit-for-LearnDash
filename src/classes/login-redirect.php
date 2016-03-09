<?php

namespace uncanny_learndash_public;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class LoginRedirect extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks(){

		if ( true === self::dependants_exist() ) {
			/* Hide admin bar on frontend for the user role */
			add_filter( 'login_redirect', array( __CLASS__, 'my_login_redirect' ), 10, 1 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title = __( 'Login Redirect', self::get_text_domain() );
		$kb_link = null;
		$class_description = __( 'Redirects all non-admin roles to a specific URL after logging into the site.', self::get_text_domain() );
		$class_icon = '<i class="uo_icon_fa fa fa-share"></i>';

		return array(
				'title'            => $class_title,
				'kb_link'          => $kb_link,
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
		// Create options
		$options = array(

			array(
				'type'        => 'text',
				'label'       => 'Login Redirect',
				'option_name' => 'login_redirect',
			),
		);

		// Build html
		$html = self::settings_output( array(
			'class'   => __CLASS__,
			'title'   => $class_title,
			'options' => $options,
			)
		);

		return $html;
	}

	/**
	 * Redirect user after successful login.
	 *
	 * @param string $redirect_to URL to redirect to.
	 * @param string $request     URL the user is coming from.
	 * @param object $user        Logged user's data.
	 *
	 * @return string
	 */
	public static function my_login_redirect( $redirect_to ) {

		$login_redirect = false;

		$settings = get_option( 'LoginRedirect' );

		foreach ( $settings as $setting ) {

			if ( 'login_redirect' === $setting['name'] ) {
				$login_redirect = $setting['value'];
			}
		}

		//is there a user to check?
		global $user;

		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			//check for admins
			if ( in_array( 'administrator', $user->roles ) ) {
				// redirect them to the default place
				//return $redirect_to;
			}

			if ( ! $login_redirect ) {
				// if redirect is not set than send them home
				return home_url();
			} else {
				return $login_redirect;
			}
		} else {
			return $redirect_to;
		}
	}
}