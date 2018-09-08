<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class LoginRedirect
 * @package uncanny_custom_toolkit
 */
class RedirectNotEnrolled extends Config implements RequiredFunctions {

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

			add_action( 'wp', array( __CLASS__, 'not_enrolled_redirect' ) );

			// Add redirect setting to LearnDash Course edit page
			add_filter( 'learndash_post_args', array( __CLASS__, 'add_redirect_to_post_args' ) );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title       = esc_html__( 'Not Enrolled Redirect', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/redirect-user-if-not-enrolled/';
		$class_description = esc_html__( 'Redirects all non-admin roles to a specific URL if they are not enrolled in the course.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-share"></i>';
		$tags              = 'learndash';
		$type              = 'free';

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'tags'             => $tags,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => false,
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

		/* Checks for LearnDash */
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		// Return true if no dependency or dependency is available
		return true;
	}

	/* Add Idle time to LeearnDash Options Meta Box
	 *@param array $post_args array of options from the LearnDash custom post type option meta box
	 */
	public static function add_redirect_to_post_args( $post_args ) {

		// Push existing and new fields
		$new_post_args = array();

		// Loop through all post arguments
		foreach ( $post_args as $key => $val ) {

			// add option on LD post type settings meta box
			if ( 'sfwd-courses' === $val['post_type'] ) {
				$new_post_args[ $key ]           = $val;
				$new_post_args[ $key ]['fields'] = array();

				//Add new field to top
				$new_post_args[ $key ]['fields']['uo_redirect'] = array(
					'name'      => __( 'Not enrolled Redirect URL', 'uncanny-pro-toolkit' ),
					'type'      => 'text',
					'help_text' => __( 'Redirect the user to the URL if they are not enrolled in the course', 'uncanny-pro-toolkit' ),
					'default'   => ''
				);

				// loop through existing fields to get proper placement of new fields
				foreach ( $post_args[ $key ]['fields'] as $field_key => $field_val ) {
					$new_post_args[ $key ]['fields'][ $field_key ] = $field_val;

				}
			} else {
				$new_post_args[ $key ] = $val;
			}
		}

		return $new_post_args;
	}

	/**
	 * Redirect user if they are not enrolled in the course
	 */
	public static function not_enrolled_redirect() {

		global $post;

		if ( ! is_admin() && $post != NULL && 'sfwd-courses' === $post->post_type ) {


			$post_options_timeout = learndash_get_setting( $post );

			if( isset( $post_options_timeout['uo_redirect']) ){
				$redirect_to = $post_options_timeout['uo_redirect'];
			}else{
				$redirect_to = '';
			}


			if ( ! empty( $redirect_to ) ) {


				//is there a user to check?

				if ( ! is_user_logged_in() ) {
					wp_redirect( $redirect_to );
					exit;

				}

				$user = wp_get_current_user();


				if ( isset( $user->roles ) && is_array( $user->roles ) ) {
					
					//check for admins
					if ( ! in_array( 'administrator', $user->roles ) ) {

						if ( ! sfwd_lms_has_access( $post->ID, get_current_user_id() ) ) {
							// redirect them to the default place
							wp_redirect( $redirect_to );
							exit;
						}

					}

				}
			}


		}


	}


}
