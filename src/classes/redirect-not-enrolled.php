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
	 * @var string
	 */
	public static $settings_metabox_key = 'learndash-course-display-content-settings';

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	/**
	 *
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			add_action( 'wp', array( __CLASS__, 'not_enrolled_redirect' ) );

			//Legacy- Add redirect setting to LearnDash Course edit page
			add_filter( 'learndash_post_args', array( __CLASS__, 'add_redirect_to_post_args_legacy' ) );

			// 3.0+  - Add auto complete setting to LearnDash Lessons (auto creates field and loads value)
			add_filter( 'learndash_settings_fields', array( __CLASS__, 'add_redirect_to_post_args' ), 10, 2 ); // 3.0+

			// 3.0+ - Save custom lesson settings field
			add_filter( 'learndash_metabox_save_fields', array( __CLASS__, 'save_uo_redirect_url_meta' ), 60, 3 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'not-enrolled-redirect';
		$class_title       = esc_html__( 'Not Enrolled Redirect', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/redirect-user-if-not-enrolled/';
		$class_description = esc_html__( 'Redirects all non-admin roles to a specific URL if they are not enrolled in the course.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-share"></i>';
		$category          = 'learndash';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
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

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param $settings_field_updates
	 * @param $settings_metabox_key
	 * @param $settings_screen_id
	 */
	public static function save_uo_redirect_url_meta( $settings_field_updates, $settings_metabox_key, $settings_screen_id ) {

		global $post;

		if ( self::$settings_metabox_key === $settings_metabox_key ) {
			// - Update the post's metadata. Nonce already verified by LearnDash
			if ( isset( $_POST['learndash-course-display-content-settings'] ) && isset( $_POST['learndash-course-display-content-settings']['uo_redirect'] ) ) {
				$redirect_url = sanitize_text_field( $_POST['learndash-course-display-content-settings']['uo_redirect'] ); //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
				learndash_update_setting( $post, 'uo_redirect', $redirect_url );
			}
		}

		return $settings_field_updates;
	}


	/**
	 * @param $setting_option_fields
	 * @param $settings_metabox_key
	 *
	 * @return mixed|array
	 */
	public static function add_redirect_to_post_args( $setting_option_fields, $settings_metabox_key ) {
		if ( $settings_metabox_key === self::$settings_metabox_key ) {
			global $post;
			$learndash_post_settings = (array) learndash_get_setting( $post, null );
			$value                   = '';
			if ( isset( $learndash_post_settings['uo_redirect'] ) ) {
				if ( ! empty( $learndash_post_settings['uo_redirect'] ) ) {
					$value = $learndash_post_settings['uo_redirect'];
				}
			}

			$setting_option_fields['uo_redirect'] = array(
				'name'      => 'uo_redirect',
				'label'     => __( 'Not enrolled Redirect URL', 'uncanny-learndash-toolkit' ),
				'type'      => 'text',
				'help_text' => __( 'Redirect the user to the URL if they are not enrolled in the course', 'uncanny-learndash-toolkit' ),
				'default'   => '',
				'value'     => $value,
			);
		}

		return $setting_option_fields;
	}

	/* Add Idle time to LearnDash Options Meta Box
	 *@param array $post_args array of options from the LearnDash custom post type option meta box
	 */
	/**
	 * @param $post_args
	 *
	 * @return array
	 */
	public static function add_redirect_to_post_args_legacy( $post_args ) {

		if ( class_exists( 'LearnDash_Theme_Register' ) ) {
			return $post_args;
		}

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
					'name'      => __( 'Not enrolled Redirect URL', 'uncanny-learndash-toolkit' ),
					'type'      => 'text',
					'help_text' => __( 'Redirect the user to the URL if they are not enrolled in the course', 'uncanny-learndash-toolkit' ),
					'default'   => '',
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
		if ( is_admin() ) {
			return;
		}

		if ( is_archive() ) {
			return;
		}

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		if ( 'sfwd-courses' !== $post->post_type ) {
			return;
		}

		$redirect_to          = '';
		$post_options_timeout = (array) learndash_get_setting( $post );

		if ( isset( $post_options_timeout['uo_redirect'] ) ) {
			$redirect_to = $post_options_timeout['uo_redirect'];
		}

		if ( empty( $redirect_to ) ) {
			return;
		}

		$redirect_to = do_shortcode( $redirect_to );

		//is there a user to check?
		if ( ! is_user_logged_in() ) {

			if( apply_filters( 'uncanny_toolkit_not_enrolled_redirect_nocache', false, $post->ID, 0 ) ){
				nocache_headers();
			}

			wp_safe_redirect( $redirect_to );
			exit;
		}

		$user = wp_get_current_user();
		//check for admins
		if ( user_can( $user, 'administrator' ) ) {
			return;
		}

		// check user access to course via direct enrollment and via group access. If both fails, redirect
		if ( ! sfwd_lms_has_access( $post->ID, get_current_user_id() ) && null === learndash_user_group_enrolled_to_course_from( $user->ID, $post->ID, true ) ) {

			if( apply_filters( 'uncanny_toolkit_not_enrolled_redirect_nocache', false, $post->ID, $user->ID ) ){
				nocache_headers();
			}

			// redirect them to the default place
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}
}
