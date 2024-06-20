<?php

namespace uncanny_learndash_toolkit;

use UncannyOwl\Toolkit\FrontendLogin\Turnstile_Support;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( '' !== Config::get_settings_value( 'uo_frontend_registration', 'FrontendLoginPlus' ) ) {
	include_once Config::get_include( 'custom-user-notification.php' );
}

/**
 * Class FrontendLoginPlus
 *
 * @package uncanny_custom_toolkit
 */
class FrontendLoginPlus extends Config implements RequiredFunctions {

	// Title of our new column

	/**
	 * @var string
	 */
	private static $column_title = 'Verified';
	// Meta key that will populate in our new column
	/**
	 * @var string
	 */
	private static $user_meta_key_col = 'uo_is_verified';

	/**
	 * @var string
	 */
	public static $login_error = '';

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action(
			'plugins_loaded',
			array(
				__CLASS__,
				'run_frontend_hooks',
			)
		);

		add_filter(
			'bulk_actions-users',
			array(
				$this,
				'uo_fl_users_bulk_actions_add',
			)
		);

		add_filter(
			'handle_bulk_actions-users',
			array(
				$this,
				'uo_fl_users_bulk_actions_update',
			),
			10,
			3
		);

		add_filter(
			'handle_network_bulk_actions-users-network',
			array(
				$this,
				'uo_fl_users_bulk_actions_multisite_update',
			),
			10,
			3
		);

		add_filter(
			'bulk_actions-users-network',
			array(
				$this,
				'uo_fl_users_bulk_actions_for_multisite',
			)
		);
	}


	/**
	 * Add verify and unverify items in bulk actions dropdown only for super admin.
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function uo_fl_users_bulk_actions_for_multisite( $actions ) {

		$actions['uo_verified']     = esc_html__( 'Verify', 'uncanny-learndash-toolkit' );
		$actions['uo_not_verified'] = esc_html__( 'Unverify', 'uncanny-learndash-toolkit' );

		return $actions;
	}

	/**
	 *
	 * This method is used to update user's verification status when admin uses
	 * bulk action for the users.
	 *
	 * @param $redirect_url
	 * @param $action
	 * @param $user_ids
	 *
	 * @return mixed
	 */
	public function uo_fl_users_bulk_actions_multisite_update( $redirect_url, $action, $user_ids ) {

		if ( $action === 'uo_verified' ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, 'uo_is_verified', 1 );
			}
			$redirect_url = add_query_arg( 'activate-user', count( $user_ids ), $redirect_url );
		}

		if ( $action === 'uo_not_verified' ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, 'uo_is_verified', 0 );
			}
			$redirect_url = add_query_arg( 'activate-user', count( $user_ids ), $redirect_url );
		}

		return $redirect_url;
	}


	/**
	 *
	 * This method is used add two new status in user bulk actions.
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function uo_fl_users_bulk_actions_add( $actions ) {

		$actions['uo_verified']     = esc_html__( 'Verified', 'uncanny-learndash-toolkit' );
		$actions['uo_not_verified'] = esc_html__( 'Not Verified', 'uncanny-learndash-toolkit' );

		return $actions;
	}


	/**
	 *
	 * This method is used to update user's verification status when admin uses
	 * bulk action for the users.
	 *
	 * @param $redirect_url
	 * @param $action
	 * @param $user_ids
	 *
	 * @return mixed
	 */
	public function uo_fl_users_bulk_actions_update( $redirect_url, $action, $user_ids ) {

		if ( $action === 'uo_verified' ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, 'uo_is_verified', 1 );
			}
			$redirect_url = add_query_arg( 'activate-user', count( $user_ids ), $redirect_url );
		}

		if ( $action === 'uo_not_verified' ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, 'uo_is_verified', 0 );
			}
			$redirect_url = add_query_arg( 'activate-user', count( $user_ids ), $redirect_url );
		}

		return $redirect_url;
	}

	/**
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			/* CHECK IF PLUGIN NEEDS TO SET MANUAL VERIFICATION */
			$uo_manual_verification   = 'no';
			$is_login_page_set        = 'no';
			$uo_frontend_registration = 'no';
			$class_name               = str_replace(
				array(
					__NAMESPACE__,
					'\\',
				),
				'',
				__CLASS__
			);
			$settings                 = get_option( $class_name, array() );
			$enable_ajax_support      = 'no';

			foreach ( $settings as $setting ) {

				if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
					$uo_manual_verification = 'yes';
				}

				if ( 'login_page' === $setting['name'] && '0' !== $setting['value'] ) {
					$is_login_page_set = 'yes';
				}

				if ( 'uo_frontend_registration' === $setting['name'] && '0' !== $setting['value'] ) {
					$uo_frontend_registration = 'yes';
				}

				if ( 'uo_frontendloginplus_enable_ajax_support' === $setting['name'] && 'on' === $setting['value'] ) {
					$enable_ajax_support = 'yes';
				}
			}

			add_filter(
				'uo-front-login-lost-pwd-template',
				array(
					__CLASS__,
					'set_ult_login_theme',
				),
				9,
				1
			);
			add_filter(
				'uo-front-login-register-template',
				array(
					__CLASS__,
					'set_ult_login_theme',
				),
				9,
				1
			);
			add_filter(
				'uo-front-login-reset-template',
				array(
					__CLASS__,
					'set_ult_login_theme',
				),
				9,
				1
			);
			add_filter(
				'uo-front-login-login-template',
				array(
					__CLASS__,
					'set_ult_login_theme',
				),
				9,
				1
			);

			add_filter(
				'uncannyowl-learndash-toolkit-js',
				array(
					__CLASS__,
					'uo_ajax_login_js',
				),
				10
			);

			add_action(
				'wp_print_scripts',
				array(
					__CLASS__,
					'uo_ajax_login_js_recaptcha_handler',
				),
				10,
				2
			);

			if ( 'yes' === $enable_ajax_support ) {

				// override menu login item
				add_filter(
					'wp_nav_menu_objects',
					array(
						__CLASS__,
						'uo_login_menu_items',
					),
					40,
					2
				);

				add_action(
					'uo_forgot_before_submit',
					array(
						__CLASS__,
						'ajax_lp_error_message_box',
					),
					100,
					1
				);

				add_filter(
					'uncannyowl-learndash-toolkit-js',
					array(
						__CLASS__,
						'uo_ajax_login_js_ajax',
					),
					11
				);

				add_action(
					'wp_ajax_ult-login',
					array(
						__CLASS__,
						'uo_login_action',
					)
				);
				add_action(
					'wp_ajax_nopriv_ult-login',
					array(
						__CLASS__,
						'uo_login_action',
					)
				);
				add_action(
					'wp_ajax_ult-forgot-password',
					array(
						__CLASS__,
						'uo_lostPass_action',
					)
				);
				add_action(
					'wp_ajax_nopriv_ult-forgot-password',
					array(
						__CLASS__,
						'uo_lostPass_action',
					)
				);
				add_action(
					'wp_ajax_ult-reset-password',
					array(
						__CLASS__,
						'uo_reset_password_action',
					)
				);
				add_action(
					'wp_ajax_nopriv_ult-reset-password',
					array(
						__CLASS__,
						'uo_reset_password_action',
					)
				);
			}

			if ( 'yes' === $uo_manual_verification ) {
				add_action(
					'user_register',
					array(
						__CLASS__,
						'registration_save',
					),
					10,
					1
				);
			}

			if ( 'yes' === $is_login_page_set ) {

				/* Add Manual Verification */
				if ( 'yes' === $uo_manual_verification ) {

					// Add new column to wp admin user list that states if the user is or isn't verified
					add_filter(
						'manage_users_columns',
						array(
							__CLASS__,
							'add_meta_column',
						)
					);
					// Populate the new column
					add_filter(
						'manage_users_custom_column',
						array(
							__CLASS__,
							'add_meta_column_content',
						),
						10,
						3
					);

					// Add new column to wp admin user list that states if the user is or isn't verified
					add_filter(
						'wpmu_users_columns',
						array(
							__CLASS__,
							'add_meta_column',
						)
					);

					// Add Custom fields to user profile : user is view their own profile
					add_action(
						'show_user_profile',
						array(
							__CLASS__,
							'my_show_extra_profile_fields',
						)
					);
					// Add Custom fields to user profile : user is view another user's profile
					add_action(
						'edit_user_profile',
						array(
							__CLASS__,
							'my_show_extra_profile_fields',
						)
					);
					// Save custom fields from user profiles : user is view their own profile
					add_action(
						'personal_options_update',
						array(
							__CLASS__,
							'my_save_extra_profile_fields',
						)
					);
					// Save custom fields from user profile : user is view another user's profile
					add_action(
						'edit_user_profile_update',
						array(
							__CLASS__,
							'my_save_extra_profile_fields',
						)
					);

				}

				// URLs
				add_filter(
					'site_url',
					array(
						__CLASS__,
						'filter_site_url',
					),
					5,
					3
				);
				add_filter(
					'network_site_url',
					array(
						__CLASS__,
						'filter_site_url',
					),
					5,
					3
				);
				// Set Cookies for login-page-ui.php early
				add_action( 'wp', array( __CLASS__, 'set_cookies' ) );

				add_action(
					'wp',
					array(
						__CLASS__,
						'maybe_set_cookies',
					),
					99
				); // Set cookies
				add_action(
					'init',
					array(
						__CLASS__,
						'maybe_set_cookies',
					),
					0
				); // Set cookies before shutdown and ob flushing

				// Create Login UI Shortcode that can be added anywhere
				add_shortcode(
					'uo_login_ui',
					array(
						__CLASS__,
						'uo_login_ui',
					)
				);

				// Create Login Modal Shortcode that can be added anywhere
				add_shortcode(
					'uo_login_modal',
					array(
						__CLASS__,
						'uo_login_modal',
					)
				);

				// Redirect from wp-login.php to custom login page
				add_action( 'init', array( __CLASS__, 'redirect_login_page' ) );
				add_action(
					'init',
					array(
						__CLASS__,
						'validate_reset_password',
					)
				);
				// Redirect after lost password
				add_filter(
					'lostpassword_redirect',
					array(
						__CLASS__,
						'redirect_lost_password',
					)
				);
				// Redirect to custom login page if login has failed
				add_action(
					'wp_login_failed',
					array(
						__CLASS__,
						'login_failed',
					)
				);
				// check recatpcha for lost password
				add_action(
					'lostpassword_post',
					array(
						__CLASS__,
						'lostpassword_post',
					)
				);

				if ( 'yes' === $uo_frontend_registration ) {
					// Redirect to custom login page after registration
					add_filter(
						'registration_redirect',
						array(
							__CLASS__,
							'redirect_registration',
						)
					);
					// Redirect User after registration errors
					add_action(
						'register_post',
						array(
							__CLASS__,
							'redirect_registration_errors',
						),
						10,
						3
					);
				}

				// Redirect to custom login page if username or password is empty
				add_filter(
					'authenticate',
					array(
						__CLASS__,
						'verify_username_password_40',
					),
					40,
					3
				);
				add_filter(
					'authenticate',
					array(
						__CLASS__,
						'maybe_remove_login_hooks',
					),
					10,
					3
				);
				// Redirect from wp-login.php to custom login page if user logged out
				add_action(
					'wp_logout',
					array(
						__CLASS__,
						'logout_page',
					),
					20,
					1
				);

				// Custom password retrieve message
				add_filter(
					'retrieve_password_message',
					array(
						__CLASS__,
						'custom_retrieve_password_message',
					),
					10,
					4
				);
				add_filter(
					'retrieve_password_title',
					array(
						__CLASS__,
						'custom_retrieve_password_title',
					),
					10,
					3
				);
				// Add lost password link to login form
				add_action(
					'login_form_bottom',
					array(
						__CLASS__,
						'add_lost_password_link',
					)
				);
				// Add shortcode to page with warning if it wasn't added
				add_action(
					'loop_end',
					array(
						__CLASS__,
						'maybe_add_ui_shortcode',
					)
				);

				// Add google recaptcha.
				add_action(
					'login_form_middle',
					array(
						__CLASS__,
						'add_recaptcha_box',
					)
				);

				self::turnstile_initialize();

			}

			/* Remove LearnDash interruption in login form while this module active */
			remove_filter( 'login_form_top', 'learndash_add_login_field_top' );

			/* Redirect Login Page */
			// Create Login Only Shortcode that can be added anywhere
			add_shortcode( 'uo_login', array( __CLASS__, 'uo_login_form' ) );

		}

	}

	/**
	 * Initializes turnstile support.
	 *
	 * @return void
	 */
	public static function turnstile_initialize() {

		// Adds Turnstile support into front-end login.
		require_once trailingslashit( UNCANNY_TOOLKIT_DIR ) . 'src/classes/frontend-login/turnstile-support.php';

		Turnstile_Support::register_hooks();

	}

	/**
	 * @param $user_id
	 */
	public static function registration_save( $user_id ) {
		update_user_meta( $user_id, self::$user_meta_key_col, '0' );
	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'front-end-login';
		$class_title       = esc_html__( 'Front End Login', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/front-end-login/';
		$class_description = esc_html__( 'Adds a custom login form and can optionally force users to be verified by an admin before they can sign in.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-sign-in"></i>';
		$category          = 'wordpress'; // phpcs:ignore
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => self::get_class_settings( $class_title ),
			'icon'             => $class_icon,
			'category'         => $category,
		);

	}

	/**
	 * Get error
	 *
	 * @return Login error
	 */

	public static function get_error() {
		return self::$login_error;
	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or
	 *     plugin
	 */
	public static function dependants_exist() {

		if ( defined( 'THEME_MY_LOGIN_VERSION' ) ) {
			return 'This module cannot be active while Theme My Login is in use. Please use Theme My Login instead.';
		}

		return true;
	}

	/**
	 * Get all pages for `Login Page` dropdown.
	 *
	 * Prevents WPML or any other translation plugins from filtering the pages result for dropdown.
	 *
	 * @ticket https://secure.helpscout.net/conversation/2022239531/43912?folderId=6246612
	 *
	 * @return array The list of pages.
	 */
	public static function get_pages() {

		global $wpdb;

		$pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->posts
					WHERE post_type = %s AND post_status = %s
					ORDER BY post_title ASC",
				'page',
				'publish'
			)
		);

		return apply_filters( 'uncanny_toolkit_flp_get_pages', $pages );

	}

	/**
	 * HTML for modal to create settings
	 *
	 * @param String
	 * @param bool
	 *
	 * @return array Return either false or settings html modal
	 */
	public static function get_class_settings( $class_title, $only_options = false ) {

		$drop_down = array();

		array_push(
			$drop_down,
			array(
				'value' => 0,
				'text'  => '- Select Page -',
			)
		);

		$pages = self::get_pages();

		foreach ( $pages as $page ) {
			array_push(
				$drop_down,
				array(
					'value' => $page->ID,
					'text'  => $page->post_title,
				)
			);
		}

		$password_reset_message = __( 'Someone requested a password reset for the user %User Login%.', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";

		if ( 'text/html' == apply_filters( 'wp_mail_content_type', 'text/html' ) ) {
			$password_reset_message .= __( 'If you want to reset your password, click the link below. %Reset Link%', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";
		} else {
			$password_reset_message .= __( "If you want to reset your password, click the link below. <a href='%Reset Link%'>Reset Password' )</a>", 'uncanny-learndash-toolkit' ) . "\r\n";
		}

		$password_reset_message .= __( 'If you did not request a password reset, you may safely ignore this email.', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";

		$verified_user_body = __( 'Your account has been approved! ', 'uncanny-learndash-toolkit' );
		$verified_user_body .= __( 'Please visit %Home Url% to login. ', 'uncanny-learndash-toolkit' );

		// Create options
		$options = array(
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . __( 'General', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			/* Override LD login modal. commenting it for next release.
			, array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Override LearnDash Login Form (Requires Learndash 3.0 Login & Registration enabled)', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_override_ld_login_form',
			),*/
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable AJAX', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_enable_ajax_support',
			),
			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Select Template', 'uncanny-learndash-toolkit' ),
				'select_name' => 'uo_frontend_login_template',
				'options'     => array(
					array(
						'value' => 'default',
						'text'  => __( 'Default', 'uncanny-learndash-toolkit' ),
					),
					array(
						'value' => 'layout_1',
						'text'  => __( 'Boxed with shadow', 'uncanny-learndash-toolkit' ),
					),
				),
			),
			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Login Page', 'uncanny-learndash-toolkit' ),
				'select_name' => 'login_page',
				'options'     => $drop_down,
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Manual User Verification', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_needs_verifcation',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'User Verified Email', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Disable User Verification Email', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_disable_verification_email',
			),
			array(
				'type'        => 'text',
				'placeholder' => esc_html__( '%Site Name% - Account Verified', 'uncanny-learndash-toolkit' ),
				'label'       => esc_html__( 'Subject', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_verified_email_subject',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<strong>' .
								esc_html__( 'Available variables for subject', 'uncanny-learndash-toolkit' ) .
								'</strong><br /><ul><li><strong>%Site Name%</strong> &mdash; ' .
								esc_html__( 'Prints Site\'s Name', 'uncanny-learndash-toolkit' ) .
								'</li></ul>',
			),
			array(
				'type'        => 'textarea',
				'placeholder' => $verified_user_body,
				'label'       => esc_html__( 'Body', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_verified_email_body',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<strong>' .
								esc_html__( 'Available variables for email body', 'uncanny-learndash-toolkit' ) .
								'</strong><br />' .
								'<ul><li><strong>%Display Name%</strong> &mdash; ' .
								esc_html__( 'Prints User\'s Display Name', 'uncanny-learndash-toolkit' ) .
								'</li><li><strong>%First Name%</strong> &mdash; '
								. esc_html__( 'Prints User\'s First Name', 'uncanny-learndash-toolkit' ) .
								'</li><li><strong>%Last Name%</strong> &mdash; '
								. esc_html__( 'Prints User\'s Last Name', 'uncanny-learndash-toolkit' ) .
								'</li><li><strong>%Home Url%</strong> &mdash; '
								. esc_html__( 'Prints Site\'s Home URL', 'uncanny-learndash-toolkit' ) .
								'</li></ul>',
			),
			// Will implement in later version
			/*array(
				'type'        => 'checkbox',
				'label'       => 'Frontend Registration',
				'option_name' => 'uo_frontend_registration',
			),*/
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . __( 'Login Form', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Title', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Login', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_title_label',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Hide the Title', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_hide_title_label',
			),

			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Description', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Log in below to access your courses.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_description',
			),

			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Hide the Description', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_hide_description',
			),

			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Login Field Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Username or Email Address', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_username_label_login',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Password Field Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_password_label',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Disable Remember Me Checkbox', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_disable_rememberme',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Remember Me Checkbox Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Remember Me', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_rememberme_label',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Return to login link', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Return to login', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_modal_login_instead_label',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Forgot Password Link Text', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Forgot Password?', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_forgetpass_label',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Button Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Log In', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_button_label',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Register Link Text', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Register', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_register_link_text',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Register Link', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_register_link',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Show Register Link', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_show_register_link',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Invalid Credentials Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Invalid username and/or password.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_failed_error',
			),
			// Field is now required
			//			array(
			//				'type'        => 'text',
			//				'label'       => esc_html__( 'Empty Field Message', 'uncanny-learndash-toolkit' ),
			//				'placeholder' => esc_html__( 'Username and/or Password is empty.', 'uncanny-learndash-toolkit' ),
			//				'option_name' => 'uo_frontend_login_empty_error',
			//			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Account Not Verified Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Your account is not yet verified.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_notverified_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Logged out message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'You are logged out.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_false_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Log Out Link Text', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Log Out', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_logout_text',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Hide All For Logged In Users', 'uncanny-learndash-toolkit' ),
				'option_name' => 'hide_logged_in_ui',
			),
			array(
				'type'        => 'textarea',
				'label'       => esc_html__( 'Message For Logged In Users', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'You are logged in.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_logged_in_message',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . __( 'Modal Login', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Modal Login Button Label [uo_login_modal]', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Log In', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_modal_button_title_label',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( "Don't dim the background when the modal is open", 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_modal_background',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Ignore Login redirect for Modal Login', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_modal_ignore_redirect',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'Google reCAPTCHA Settings', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Site Key', 'uncanny-learndash-toolkit' ),
				'placeholder' => '',
				'option_name' => 'uo_frontend_login_recaptcha_key',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Secret Key', 'uncanny-learndash-toolkit' ),
				'placeholder' => '',
				'option_name' => 'uo_frontend_login_recaptcha_secret_key',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'reCAPTCHA Not Checked Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Please verify that you are not a robot.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_recaptchaempty_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'reCAPTCHA Error Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'There was an error validating the form. Please contact the site administrator.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_recaptchafailed_error',
			),
			// Turnstile settings.
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'Cloudflare Turnstile Settings', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Site Key', 'uncanny-learndash-toolkit' ),
				'placeholder' => '',
				'option_name' => 'uo_frontend_login_turnstile_recaptcha_key',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Secret Key', 'uncanny-learndash-toolkit' ),
				'placeholder' => '',
				'option_name' => 'uo_frontend_login_turnstile_recaptcha_secret',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Turnstile Not Checked Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Verify you are human.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_turnstile_recaptcha_empty_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Turnstile Error Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'There was an error validating the form. Please contact the site administrator.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_turnstile_error_message',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Turnstile Render Error Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'An error has occurred while displaying Turnstile. Please contact the site administrator.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_turnstile_render_error_message',
			),
			// Turnstile settings end.
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'Forgot Password Form', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Title', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Forgot Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_forgot_pass_title',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Description', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Enter your email address or username and we’ll send you instructions to reset your password.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_forgot_pass_desc',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Field Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Username or Email Address', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_username_label_reset',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Button Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Recover Account', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_forgot_pass_button_label',
			),
			// Field is now required
			//			array(
			//				'type'        => 'text',
			//				'label'       => esc_html__( 'Empty Field Message', 'uncanny-learndash-toolkit' ),
			//				'placeholder' => esc_html__( 'Enter your username or email address.', 'uncanny-learndash-toolkit' ),
			//				'option_name' => 'uo_login_forgot_pass_empty_field',
			//			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Invalid Credentials Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'That is not a registered username or email address.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_login_forgot_pass_invalid_creds',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Email Failed Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Password reset email failed to send.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_failedsendemail_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Email Success Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Check your email for a password reset link.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_successsendemail',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'Forgot Password Email', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'text',
				'placeholder' => esc_html__( 'Password Reset', 'uncanny-learndash-toolkit' ),
				'label'       => esc_html__( 'Subject', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_resetpassword_email_subject',
			),
			array(
				'type'        => 'textarea',
				'placeholder' => $password_reset_message,
				'label'       => esc_html__( 'Body', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_resetpassword_email_body',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<strong>' .
								esc_html__( 'Available variables for email body', 'uncanny-learndash-toolkit' ) .
								'</strong><br /><ul><li><strong>%User Login%</strong> &mdash; ' .
								esc_html__( 'Prints User\'s Login', 'uncanny-learndash-toolkit' ) .
								'</li><li><strong>%Reset Link%</strong> &mdash; '
								. esc_html__( 'Prints Password Reset Link', 'uncanny-learndash-toolkit' ) .
								'</li></ul>',
			),
			array(
				'type'       => 'html',
				'inner_html' => '<h2>' . esc_html__( 'Reset Password Form', 'uncanny-learndash-toolkit' ) . '</h2>',
			),
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Require strong password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_reset_password_strength',
				'description' => esc_html__( '(Minimum 8 characters with 1 uppercase, 1 lowercase, 1 number)', 'uncanny-learndash-toolkit' ),
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Title', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Reset Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_title',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Description', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Enter a new password below.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_desc',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Password Field Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'New Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_pass_field',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Confirm Password Field Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Confirm New Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_confirm_pass_field',
			),
			// Will implement in later version
			//			array(
			//				'type'        => 'text',
			//				'label'       => esc_html__( 'Password Hint Indicator Text', 'uncanny-learndash-toolkit' ),
			//				'placeholder' => esc_html__( 'Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; )', 'uncanny-learndash-toolkit' ),
			//				'option_name' => 'uo_frontend_login_reset_pass_hint',
			//			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Button Label', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Reset Password', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_pass_button',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Passwords Do Not Match Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'The passwords you entered do not match.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_passwordnotmatch_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Passwords strength is not strong', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'The passwords you entered is not strong.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_passwordstrength_error',
			),
			// Field is now required
			//			array(
			//				'type'        => 'text',
			//				'label'       => esc_html__( 'Empty Field Message', 'uncanny-learndash-toolkit' ),
			//				'placeholder' => esc_html__( 'Enter your new password in both fields.', 'uncanny-learndash-toolkit' ),
			//				'option_name' => 'uo_frontend_login_passwordempy_error',
			//			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Invalid Reset Link Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Invalid password reset link.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_invalidresetkey_error',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Password Reset Successful Message', 'uncanny-learndash-toolkit' ),
				'placeholder' => esc_html__( 'Your password was successfully reset. Please log in.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontend_login_reset_successful',
			),
			array(
				'type'       => 'html',
				'inner_html' => uo_toolkit_2fa_oci_button(),
			),
		);

		if ( $only_options ) {
			return $options;
		}

		// Build html
		$html = self::settings_output(
			array(
				'class'   => __CLASS__,
				'title'   => $class_title,
				'options' => $options,
			)
		);

		return $html;
	}

	/*
	 * Add a column to wp admin user table
	 *
	 * @return Array
	 *
	 */
	/**
	 * @param $columns
	 *
	 * @return mixed
	 */
	public static function add_meta_column( $columns ) {
		$columns['uo_column'] = apply_filters( 'uo_user_column_title', self::$column_title );

		return $columns;
	}

	/*
	 * Populate Column added to wo admin user table
	 *
	 * @return Array All columns
	 *
	 */
	/**
	 * @param $value
	 * @param $column_name
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function add_meta_column_content( $value, $column_name, $user_id ) {

		$user_verified_value = get_user_meta( $user_id, self::$user_meta_key_col, true );

		if ( 'uo_column' === $column_name ) {

			$message = __( 'Auto Verified', 'uncanny-learndash-toolkit' );

			if ( '1' === $user_verified_value ) {
				$message = '<span style="color:green;">' . __( 'Verified', 'uncanny-learndash-toolkit' ) . '</span>';
			}

			if ( '0' === $user_verified_value ) {
				$message = '<span style="color:green;">' . __( 'Not Verified', 'uncanny-learndash-toolkit' ) . '</span>';
			}

			if ( user_can( $user_id, 'activate_plugins' ) ) {
				$message = '<span style="color:green;">' . __( 'Auto Verified', 'uncanny-learndash-toolkit' ) . '</span>';
			}

			return $message;
		}

		return $value;
	}

	/*
	 * Add custom field to user profile
	 * @param object $user
	 */
	/**
	 * @param $user
	 */
	public static function my_show_extra_profile_fields( $user ) {
		$checked = esc_attr( get_user_meta( $user->ID, 'uo_is_verified', true ) );

		if ( user_can( $user->ID, 'activate_plugins' ) ) {
			$checked = '1';
		}

		$current = true;
		$echo    = true;
		?>

		<table class="form-table">
			<tr class="uo_is_verified-container">
				<th scope="row">
					<h2>Verify User</h2>
				</th>
				<td>
					<label for="uo_is_verified">
						<input type="checkbox" id="uo_is_verified"
							   name="uo_is_verified"
							   value="1" <?php checked( $checked, $current, $echo ); ?>/>
						Verify this user and allow them to log in
					</label>
				</td>
			</tr>

		</table>

		<?php
	}

	/**
	 * Save verification status of user.
	 *
	 * @param int $user_id User's ID.
	 *
	 * @return bool
	 */
	public static function my_save_extra_profile_fields( $user_id ) {

		// Only run for admin level users.
		if ( ! current_user_can( 'edit_user' ) ) {
			return false;
		}

		// Get the posted verification status.
		$is_verified = filter_input( INPUT_POST, 'uo_is_verified', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		// if this wasn't posted at all, bail.
		if ( null === $is_verified ) {
			return true;
		}

		// convert status into integers from boolean.
		$is_verified = intval( $is_verified );

		// Update the user's metadata with verification status.
		update_user_meta( $user_id, 'uo_is_verified', $is_verified );

		// Reset verification email if already sent and user unverified
		$is_verification_email_sent = get_user_meta( $user_id, 'uo_verified_email_sent', true );

		if ( 'yes' === $is_verification_email_sent && $is_verified !== 1 ) {
			update_user_meta( $user_id, 'uo_verified_email_sent', 'no' );
		}

		// if the user was verified, run action hook.
		if ( $is_verified ) {
			/**
			 * Fires after a user is manually verified.
			 *
			 * @param int $user_id The verified user's ID.
			 * @param int $verifier_id The user (admin) that verified the user.
			 *
			 * @since 3.4.1
			 */
			do_action( 'uo_user_verified', $user_id, $verifier_id = get_current_user_id() );
		}

		$verified            = get_user_meta( $user_id, 'uo_is_verified', true );
		$verified_email_sent = get_user_meta( $user_id, 'uo_verified_email_sent', true );

		if ( $verified === '1' && 'yes' !== $verified_email_sent ) {

			$disable_email = self::get_settings_value( 'uo_frontendloginplus_disable_verification_email', __CLASS__ );

			if ( 'on' === $disable_email ) {
				return true;
			}

			$user        = get_userdata( $user_id );
			$admin_email = get_option( 'admin_email' );
			$blog_name   = get_option( 'blogname' );

			// Create verified user subject
			$default_subject = sprintf( __( '%s - Account Verified', 'uncanny-learndash-toolkit' ), $blog_name );

			$subject = self::get_settings_value(
				'uo_frontend_verified_email_subject',
				__CLASS__,
				/* translators: %s: site name */
				$default_subject,
				self::get_class_settings( '', true )
			);

			$subject = str_ireplace( '%Site Name%', $blog_name, $subject );

			/**
			 * Filters user verification subject.
			 *
			 * @param string $subject The email subject.
			 * @param \WP_User $user The verified user.
			 */
			$subject = apply_filters( 'uo_verified_email_subject', $subject, $user );

			// Create verified user body
			$default_body = __( 'Your account has been approved! ', 'uncanny-learndash-toolkit' ) . "\r\n\n";
			$default_body .= sprintf( __( 'Please visit %s to login. ', 'uncanny-learndash-toolkit' ), home_url() ) . " \r\n";
			/**
			 * Filters user verification email message.
			 *
			 * @param array $headers The email message.
			 * @param \WP_User $user The verified user.
			 */

			$body = self::get_settings_value(
				'uo_frontend_verified_email_body',
				__CLASS__,
				$default_body,
				self::get_class_settings( '', true )
			);

			$body = str_ireplace( '%Display Name%', $user->display_name, $body );
			$body = str_ireplace( '%First Name%', $user->first_name, $body );
			$body = str_ireplace( '%Last Name%', $user->last_name, $body );
			$body = str_ireplace( '%Home URL%', home_url(), $body );

			$body = apply_filters( 'uo_verified_email_message', $body, $user );

			// Send verified user email
			$to        = $user->user_email;
			$from      = $blog_name . ' <' . $admin_email . '>';
			$headers[] = 'From: ' . $from;
			/**
			 * Filters user verification email headers.
			 *
			 * @param array $headers The email headers.
			 * @param \WP_User $user The verified user.
			 */
			$headers = apply_filters( 'uo_verified_email_headers', $headers, $user );
			$mailed  = wp_mail( $to, $subject, $body, $headers );
			// after wp_mail successful
			$from      = $blog_name . ' <' . $admin_email . '>';
			$headers[] = 'From: ' . $from;
			/**
			 * Filters user verification email headers.
			 *
			 * @param array $headers The email headers.
			 * @param \WP_User $user The verified user.
			 */
			$headers = apply_filters( 'uo_verified_email_headers', $headers, $user );

			$to = $admin_email;

			$subject = sprintf( __( '%s - Account Verified', 'uncanny-learndash-toolkit' ), $blog_name );

			/**
			 * Filters subject of user verification email sent to admin.
			 *
			 * @param array $headers The email headers.
			 * @param \WP_User $user The verified user.
			 */
			$subject = apply_filters( 'uo_verified_email_subject', $subject, $user );

			$message = sprintf( __( '%s account has been approved! ', 'uncanny-learndash-toolkit' ), $user->user_email ) . " \r\n\n";
			$message .= sprintf( __( ' Visit %s to view / edit user. ', 'uncanny-learndash-toolkit' ), admin_url( 'user-edit.php?user_id=' . $user->id ) ) . " \r\n";

			/**
			 * Filters message of user verification email sent to admin.
			 *
			 * @param array $message The email message.
			 * @param \WP_User $user The verified user.
			 */
			$message = apply_filters( 'uo_verified_email_message', $message, $user );

			$admin_mailed = wp_mail( $to, $subject, $message, $headers );

			// after wp_mail successful.
			if ( $admin_mailed && $mailed ) {

				update_user_meta( $user_id, 'uo_verified_email_sent', 'yes' );

				/**
				 * Fires after user verification emails are sent.
				 *
				 * @param int $user_id The verified user's ID.
				 * @param int $verifier_id The user (admin) that verified the user.
				 *
				 * @since 3.4.1
				 */
				do_action( 'uo_user_verification_emails_sent', $user_id, $verifier_id = get_current_user_id() );
			}
		}

		return true;
	}

	/**
	 * Show a notice to users who try to login and are disabled
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function user_login_message( $message ) {
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['unverified'] ) && 1 === $_GET['unverified'] ) {
			$message = '<div id="login_error">' . apply_filters( 'uo_unverified_users_notice', esc_html__( "We haven't verified this account.", 'uncanny-learndash-toolkit' ) ) . '</div>';
		}

		return $message;
	}

	/**
	 *
	 */
	public static function set_cookies() {

		global $post;

		if ( null !== $post ) {

			$current_post_id = $post->ID;

			$login_page_id = self::get_login_redirect_page_id();

			if ( $current_post_id === $login_page_id ) {

				/* Set Reset Password Cookie */
				if ( isset( $_GET['action'] ) ) {
					if ( 'rp' === $_GET['action'] ) {

						if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

							$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
							$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );

							$user = check_password_reset_key( wp_unslash( $_GET['key'] ), wp_unslash( $_GET['login'] ) );
							if ( is_wp_error( $user ) ) {
								// Key is not valid
								$login_page = get_permalink( self::get_login_redirect_page_id() );
								// Password reset cookie was not set OR password rest key check failed

								$login_page     = self::get_login_redirect_page_id();
								$login_page_url = get_permalink( $login_page );

								if ( strpos( $login_page_url, '?' ) ) {
									$login_page_url = $login_page_url . '&';
								} else {
									$login_page_url = $login_page_url . '?';
								}

								if ( is_wp_error( $user ) ) {
									if ( $user->get_error_code() === 'expired_key' ) {
										wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=expiredkey' );
										die();
									} else {
										wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=invalidkey' );
										die();
									}
								}
							}

							setcookie( $rp_cookie, $value, time() + 3600, '/' . get_post_field( 'post_name', $login_page_id ), COOKIE_DOMAIN, is_ssl(), true );
						}
					}
				}
			}
		}
	}

	/**
	 *
	 */
	public static function maybe_set_cookies() {

		global $post;

		if ( null !== $post ) {

			$current_post_id = $post->ID;

			$login_page_id = self::get_login_redirect_page_id();

			if ( $current_post_id === $login_page_id ) {

				/* Set Reset Password Cookie */
				if ( isset( $_GET['action'] ) ) {
					if ( 'rp' === $_GET['action'] ) {

						if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

							$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
							$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
							$user      = check_password_reset_key( wp_unslash( $_GET['key'] ), wp_unslash( $_GET['login'] ) );
							if ( is_wp_error( $user ) ) {
								// Key is not valid
								$login_page     = self::get_login_redirect_page_id();
								$login_page_url = get_permalink( $login_page );

								if ( strpos( $login_page_url, '?' ) ) {
									$login_page_url = $login_page_url . '&';
								} else {
									$login_page_url = $login_page_url . '?';
								}
								// Password reset cookie was not set OR password rest key check failed
								if ( is_wp_error( $user ) ) {
									if ( $user->get_error_code() === 'expired_key' ) {
										wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=expiredkey' );
										die();
									} else {
										wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=invalidkey' );
										die();
									}
								}
							}
							setcookie( $rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), true );
						}
					}
				}
			}
		}

	}

	/**
	 * @param      $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public static function uo_login_form( $atts, $content = null ) {

		$hide_logged_in_ui = self::get_settings_value( 'hide_logged_in_ui', __CLASS__ );

		if ( is_user_logged_in() && 'on' === $hide_logged_in_ui ) {
			return '';
		}

		if ( is_user_logged_in() ) {

			if ( ! empty( $content ) ) {
				return do_shortcode( $content );
			}

			$logged_in_message = self::get_settings_value( 'uo_frontend_login_logged_in_message', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

			$logout_link_text = self::get_settings_value( 'uo_frontend_logout_text', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

			$logout_link = '<a href="' . wp_logout_url() . '" title="' . $logout_link_text . '">' . $logout_link_text . '</a>';

			return $logged_in_message . ' ' . $logout_link;
		}

		$username_label = ( isset( $atts['username-label'] ) ) ?
			$atts['username-label'] :
			self::get_settings_value( 'uo_login_username_label_login', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$password_label = ( isset( $atts['password-label'] ) ) ?
			$atts['password-label'] :
			self::get_settings_value( 'uo_frontend_login_password_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$rememberme_label = ( isset( $atts['rememberme-label'] ) ) ?
			$atts['rememberme-label'] :
			self::get_settings_value( 'uo_frontend_login_rememberme_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$placeholder = ( isset( $atts['placeholder'] ) ? $atts['placeholder'] : 'yes' );
		$redirect    = ( isset( $atts['redirect'] ) ? $atts['redirect'] : home_url() );

		$submit_label = ( isset( $atts['login-button-label'] ) ) ?
			$atts['login-button-label'] :
			self::get_settings_value( 'uo_frontend_login_button_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
			self::add_recaptcha_box();
		}

		if ( 'no' !== $placeholder ) {
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function () {
					jQuery('#user_login').attr('placeholder', '<?php echo $username_label; ?>')
					jQuery('#user_pass').attr('placeholder', '<?php echo $password_label; ?>')
				})
			</script>
			<?php
			$username_label = '';
			$password_label = '';
		}

		// Override if redirect_to already set in login request.
		if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect = $_REQUEST['redirect_to'];
		}
		//Add an additional query variable for login redirect module. It'll override [uo_login redirect]
		if ( ! empty( $redirect ) ) {
			$redirect = strpos( $redirect, '?' ) ? $redirect . '&uo_redirect=1' : $redirect . '?uo_redirect';
		}

		$base_login_form_args = self::fetch_login_form_args();

		$login_form_args = array(
			'echo'           => false,
			'redirect'       => $redirect,
			'form_id'        => 'loginform',
			'label_username' => $username_label,
			'label_password' => $password_label,
			'label_remember' => $rememberme_label,
			'label_log_in'   => $submit_label,
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'rememberme',
			'id_submit'      => 'wp-submit',
			'remember'       => isset( $base_login_form_args['remember'] ) ? $base_login_form_args['remember'] : true,
			'value_username' => null,
			'value_remember' => true,
		);

		$login_form_args = apply_filters( 'uo_frontend_login_args', $login_form_args );

		return wp_login_form( $login_form_args );
	}

	/*
	 * UO login ui shortcode
	 *
	 * v3.0 --- Introduced different templates,
	 * moved a lot of code out of login-page-ui to
	 * the function.
	 *
	 * @return false|string
	 */
	/**
	 * @param array $attribute
	 *
	 * @return false|string
	 */
	public static function uo_login_ui( $attribute = array() ) {

		$attribute = shortcode_atts(
			array(
				'redirect' => '',
			),
			$attribute,
			'uo_login_ui'
		);
		global $uo_login_ui_attribute;
		$uo_login_ui_attribute = $attribute;

		$hide_logged_in_ui = self::get_settings_value( 'hide_logged_in_ui', __CLASS__ );

		if ( is_user_logged_in() && 'on' === $hide_logged_in_ui ) {
			return '';
		}

		$login_page            = self::get_login_redirect_page_id();
		$login_page_url        = get_permalink( $login_page );
		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		if ( strpos( $login_page_url, '?' ) ) {
			$login_page_url = $login_page_url . '&';
		} else {
			$login_page_url = $login_page_url . '?';
		}

		$user_name_label_login = self::get_settings_value( 'uo_login_username_label_login', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$user_name_label_reset = self::get_settings_value( 'uo_login_username_label_reset', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$message_warning = '';

		global $user_login;
		$login = ( isset( $_GET['login'] ) ) ? $_GET['login'] : 'not-set';

		/* Registration */
		$register = false;

		/* Lost Password */
		$lost_password = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'lostpassword' === $_GET['action'] ) {
				$lost_password = true;
			}
		}

		/* Sending Reset Password Link */
		$reset_password_sent         = false;
		$reset_password_sent_success = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'forgot' === $_GET['action'] ) {
				$reset_password_sent = true;
				if ( '1' === $_GET['success'] ) {
					$reset_password_sent_success = true;
				} elseif ( 'recaptchaempty' === $_GET['success'] ) {
					$reset_password_sent_success = 'recaptchaempty';
				} elseif ( 'recaptchafailed' === $_GET['success'] ) {
					$reset_password_sent_success = 'recaptchafailed';
				} elseif ( 'invalidcredentials' === $_GET['success'] ) {
					$reset_password_sent_success = 'invalidcredentials';
				}
			}
		}

		/* Reset Password */
		$reset_password = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'rp' === $_GET['action'] ) {
				$reset_password = true;
			}
		}

		/* Validate Reset Password Information */
		$validate_password_reset = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'validatepasswordreset' === $_GET['action'] ) {
				$validate_password_reset = true;
			}
		}

		$message_details = self::fetch_warning_error_msgs( $login );

		if ( ! empty( $message_details['warning'] ) ) {
			$message_warning = $message_details['warning'];
		}

		if ( $message_warning ) {
			if ( 'false' === $login ) {
				self::$login_error = '<p class="login-msg loggedout"><strong>' . $message_warning . '</strong></p>';
			} else {
				self::$login_error = '<p class="login-msg"><strong>' . $message_warning . '</strong></p>';
			}
		}

		if ( isset( $_GET['exceed_max_concurrent_login'] ) ) {
			self::$login_error = __( 'Your account has exceeded maximum concurrent login number.', 'learndash-integrity' );
		}
		self::$login_error = apply_filters( 'uo_frontend_login_error', self::$login_error, $login, '', $message_warning );

		// Prevent errors, some templates are using the following template
		$login_error = self::$login_error;

		$innerText = apply_filters( 'uo-login-inner-text', self::fetch_inner_text(), $login );

		$default_css = apply_filters( 'uo-login-default-css', '<style>.login-msg{color:red;} .login-msg.loggedout{color:green;}#loginform label[for="user_login"],#loginform label[for="user_pass"] {display: block;}</style>' );

		//Introducing different templates!
		$template_to_load = apply_filters( 'uo-login-template', Config::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', 'default' ) );

		//Render Template
		ob_start();

		include self::get_template( '/login-page-ui-default.php' );

		$login_ui = ob_get_clean();

		return $login_ui;

	}

	/**
	 * @param $login
	 *
	 * @return array
	 */
	public static function fetch_warning_error_msgs( $login ) {

		$message_warning = '';

		switch ( $login ) {

			case 'failed':
				$message_warning = self::get_settings_value( 'uo_frontend_login_failed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				break;
			case 'false':
				// Empty msgid. It is reserved by GNU gettext: gettext("") returns the header entry with meta information, not the empty string.
				$message_warning = self::get_settings_value( 'uo_frontend_login_false_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				break;
			case 'notverified':
				$message_warning = self::get_settings_value( 'uo_frontend_login_notverified_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				break;
			case 'recaptchaempty':
				$message_warning = self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				break;
			case 'recaptchafailed':
				$message_warning = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				break;
		}

		$default = array(
			'error'   => $login,
			'warning' => $message_warning,
		);

		return apply_filters( 'uo_toolkit_frontend_login_error_messages', $default, $login );

	}

	/**
	 * @return array
	 */
	public static function fetch_login_form_args() {

		global $uo_login_ui_attribute;
		$label_username   = self::get_settings_value( 'uo_login_username_label_login', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$label_password   = self::get_settings_value( 'uo_frontend_login_password_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$disable_remember = self::get_settings_value( 'uo_frontendloginplus_disable_rememberme', __CLASS__ );
		$label_remember   = self::get_settings_value( 'uo_frontend_login_rememberme_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$label_log_in     = self::get_settings_value( 'uo_frontend_login_button_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$redirect_to      = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : home_url( '/wp-admin/' );

		if ( isset( $uo_login_ui_attribute ) && isset( $uo_login_ui_attribute['redirect'] ) && ! empty( $uo_login_ui_attribute['redirect'] ) ) {
			$redirect_to = $uo_login_ui_attribute['redirect'];
		}

		return array(
			'echo'           => true,
			'redirect'       => $redirect_to,
			'form_id'        => 'ult-login-form',
			'label_username' => $label_username,
			'label_password' => $label_password,
			'label_remember' => $label_remember,
			'label_log_in'   => $label_log_in,
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'rememberme',
			'id_submit'      => 'wp-submit',
			'remember'       => $disable_remember === 'on' ? false : true,
			'value_username' => null,
			'value_remember' => true,
		);
	}

	/**
	 * @param $user_name_label
	 *
	 * @return array
	 */
	public static function fetch_inner_text() {

		$uo_frontend_login_logged_in_message        = self::get_settings_value( 'uo_frontend_login_logged_in_message', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_logout_text                    = self::get_settings_value( 'uo_frontend_logout_text', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_login_forgot_pass_title                 = self::get_settings_value( 'uo_login_forgot_pass_title', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_login_forgot_pass_desc                  = self::get_settings_value( 'uo_login_forgot_pass_desc', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_login_username_label_reset              = self::get_settings_value( 'uo_login_username_label_reset', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_successsendemail         = self::get_settings_value( 'uo_frontend_login_successsendemail', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_failedsendemail_error    = self::get_settings_value( 'uo_frontend_login_failedsendemail_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_login_forgot_pass_invalid_creds         = self::get_settings_value( 'uo_login_forgot_pass_invalid_creds', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_title              = self::get_settings_value( 'uo_frontend_login_reset_title', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_desc               = self::get_settings_value( 'uo_frontend_login_reset_desc', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_pass_button        = self::get_settings_value( 'uo_frontend_login_reset_pass_button', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_pass_field         = self::get_settings_value( 'uo_frontend_login_reset_pass_field', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_confirm_pass_field = self::get_settings_value( 'uo_frontend_login_reset_confirm_pass_field', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		//$uo_frontend_login_reset_pass_hint = self::get_settings_value( 'uo_frontend_login_reset_pass_hint', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_invalidresetkey_error  = self::get_settings_value( 'uo_frontend_login_invalidresetkey_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_passwordnotmatch_error = self::get_settings_value( 'uo_frontend_login_passwordnotmatch_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_passwordstrength_error = self::get_settings_value( 'uo_frontend_login_passwordstrength_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_reset_successful       = self::get_settings_value( 'uo_frontend_login_reset_successful', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_title_label            = self::get_settings_value( 'uo_frontend_login_title_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_description            = self::get_settings_value( 'uo_frontend_login_description', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_register_link_text           = self::get_settings_value( 'uo_frontend_register_link_text', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_login_forgot_pass_button_label        = self::get_settings_value( 'uo_login_forgot_pass_button_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_recaptchaempty_error   = self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_recaptchafailed_error  = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		$uo_frontend_login_instead_label          = self::get_settings_value( 'uo_frontend_login_modal_login_instead_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		$innerText = array(
			'Logged-In-Message'          => $uo_frontend_login_logged_in_message,
			'Logout'                     => $uo_frontend_logout_text,
			'Password-Recovery-Title'    => $uo_login_forgot_pass_title,
			'Password-Recovery-Desc'     => $uo_login_forgot_pass_desc,
			'Password-Recovery-Label'    => $uo_login_username_label_reset,
			'Success-Email-Sent'         => $uo_frontend_login_successsendemail,
			'Failed-Send-Email'          => $uo_frontend_login_failedsendemail_error,
			'Failed-Pass-Creds'          => $uo_login_forgot_pass_invalid_creds,
			'Reset-Password-Title'       => $uo_frontend_login_reset_title,
			'Reset-Password-Desc'        => $uo_frontend_login_reset_desc,
			'Reset-Password-Button'      => $uo_frontend_login_reset_pass_button,
			'New-Password'               => $uo_frontend_login_reset_pass_field,
			'Confirm-Password'           => $uo_frontend_login_reset_confirm_pass_field,
			//'Password-Indicator-Hint'    => $uo_frontend_login_reset_pass_hint,
			'Password-Reset-Link-Failed' => $uo_frontend_login_invalidresetkey_error,
			'Invalid-Reset-Key'          => $uo_frontend_login_invalidresetkey_error,
			'Expired-Reset-Key'          => $uo_frontend_login_invalidresetkey_error,
			'Password-Not-Match'         => $uo_frontend_login_passwordnotmatch_error,
			'Password-Not-Strong'        => $uo_frontend_login_passwordstrength_error,
			'Reset-Success'              => $uo_frontend_login_reset_successful,
			'Login-Title'                => $uo_frontend_login_title_label,
			'Login-Description'          => $uo_frontend_login_description,
			'Register-Link'              => $uo_frontend_register_link_text,
			'Get-New-Password'           => $uo_login_forgot_pass_button_label,
			'recaptchaempty'             => $uo_frontend_login_recaptchaempty_error,
			'recaptchafailed'            => $uo_frontend_login_recaptchafailed_error,
			'Log-in-instead'             => $uo_frontend_login_instead_label,
		);

		return $innerText;
	}

	/**
	 * Redirect from wp-login.php to custom login page
	 */
	public static function redirect_login_page() {

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$page_viewed = $_SERVER['REQUEST_URI'];

		// This is the request action which holds the param 'action' for POST or GET request.
		$http_request_action = '';

		// Cannot use filter input for $_REQUEST yet.
		if ( isset( $_REQUEST['action'] ) ) {
			$http_request_action = $_REQUEST['action'];
		}

		// GET exclusive 'action'.
		$http_get_action = filter_input( INPUT_GET, 'action', FILTER_UNSAFE_RAW );

		if ( ! $login_page ) {
			return;
		}

		$registering = false;

		if ( isset( $http_get_action ) ) {

			if ( 'register' === $http_get_action ) {
				$registering = true;
			}

			// 2fa support for register.
			if ( 'rp' === $http_get_action ) {
				return;
			}

			// confirm_admin_email support for login.
			if ( 'confirm_admin_email' === $http_get_action ) {
				return;
			}

			// confirmaction support for login.
			if ( 'confirmaction' === $http_get_action ) {
				return;
			}

			if ( 'backup_2fa' === $_GET['action'] ) {

				$user_id = filter_input( INPUT_GET, 'wp-auth-id', FILTER_SANITIZE_NUMBER_INT );

				// Destroy the cookie.
				setcookie( 'uo-toolkit-guard', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );

				// Set the form cookie verification.
				$cookie_nonce = wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user_id ) );
				setcookie( 'uo-toolkit-guard', wp_hash_password( $cookie_nonce ), 0, COOKIEPATH, COOKIE_DOMAIN );

				$parameters = array(
					'provider'           => filter_input( INPUT_GET, 'provider', FILTER_UNSAFE_RAW ),
					'wp-auth-id'         => $user_id,
					'_wpnonce'           => wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user_id ) ),
					'wp-auth-nonce'      => filter_input( INPUT_GET, 'wp-auth-nonce', FILTER_UNSAFE_RAW ),
					'rememberme'         => isset( $_REQUEST['rememberme'] ) ? $_REQUEST['rememberme'] : '',
					'redirect_to'        => filter_input( INPUT_GET, 'redirect_to', FILTER_UNSAFE_RAW ),
					'2fa_authentication' => 1,
					// Pass `2fa_authentication` to show the 2fa form.
				);

				$login_page = add_query_arg( $parameters, $login_page );

				wp_safe_redirect( $login_page, 302 );

				exit;
			}
		}

		// Check email support for register. Not related to 2fa, but this action should be whitelisted.
		$checkemail = filter_input( INPUT_GET, 'checkemail', FILTER_UNSAFE_RAW );

		if ( 'registered' == $checkemail ) {
			return;
		}

		// When the user is wp-login.php and server request method is get and is not registering.
		if ( false !== strpos( $page_viewed, 'wp-login.php' ) && 'GET' === $_SERVER['REQUEST_METHOD'] && ! $registering ) {

			// Ignore redirect if action is logout.
			if ( 'logout' === $http_request_action ) {
				return;
			}

			// Allow 'User Switching' actions. Do not redirect to login page when the module is active.
			$active_classes = get_option( 'uncanny_toolkit_active_classes', 0 );

			if ( 0 !== $active_classes ) {
				if ( is_array( $active_classes ) && isset( $active_classes['uncanny_learndash_toolkit\UserSwitching'] ) ) {
					if ( ! empty( $http_request_action ) ) {
						$allowed_actions = array(
							'switch_to_user',
							'switch_to_olduser',
						);
						if ( in_array( $http_request_action, $allowed_actions, true ) ) {
							return;
						}
					}
				}
			}

			// Accept 'redirect_to' REQUEST parameter which redirects the user to the value of the parameter.
			if ( isset( $_REQUEST['redirect_to'] ) ) {

				if ( is_user_logged_in() ) {
					$redirect_to = $_REQUEST['redirect_to'];
					$redirect    = apply_filters( 'login_redirect', $_REQUEST['redirect_to'], $redirect_to, wp_get_current_user() );
					// Change the redirect from `301` to `302` to prevent aggressive caching of browser.
					wp_safe_redirect( $redirect, 302 );
					exit;
				}

				$login_page = add_query_arg( array( 'redirect_to' => $_REQUEST['redirect_to'] ), $login_page );

			}

			if ( 'lostpassword' === $http_request_action ) {
				$login_page = add_query_arg( array( 'action' => $http_request_action ), $login_page );
			}

			// Allow modifications.
			$login_page = apply_filters( 'uo-redirect-login-page', $login_page );

			// Change the redirect from `301` to `302` to prevent aggressive caching of browser.
			wp_safe_redirect( $login_page, 302 );

			exit;

		}

	}

	/**
	 * Redirect from registration to custom login page
	 */
	public static function redirect_registration() {

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$query           = array();
		$query['action'] = 'register';

		if ( isset( $_GET['checkemail'] ) && 'registered' === $_GET['checkemail'] ) {
			$query['registered'] = 'success';
		}

		wp_safe_redirect( add_query_arg( $query, $login_page ), 301 );
		exit;
	}

	/*
	 * Redirect from registration errors to custom login page
	 * @param string   $sanitized_user_login The submitted username after being sanitized.
	 * @param string   $user_email           The submitted email.
	 * @param WP_Error $errors               Contains any errors with submitted username and email,
	 *                                       e.g., an empty field, an invalid username or email,
	 *                                       e.g., an empty field, an invalid username or email,
	 */
	/**
	 * @param $sanitized_user_login
	 * @param $user_email
	 * @param $errors
	 */
	public static function redirect_registration_errors( $sanitized_user_login, $user_email, $errors ) {

		if ( ! empty( $errors->errors ) ) {
			$error_code = $errors->get_error_code();
			self::redirect_registration_error( $error_code );
			exit();
		}

	}

	/**
	 * @param $error_code
	 */
	public static function redirect_registration_error( $error_code ) {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect(
			add_query_arg(
				array(
					'action'   => 'register',
					'wp-error' => $error_code,
				),
				$login_page
			)
		);
		exit;
	}

	/**
	 * Redirect to custom login page if login has failed
	 */
	public static function login_failed() {

		// Check for REST requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		// Redundant check for REST because in some cases REST_REQUEST constant does not work.
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false ) {
			return;
		}
		// Check for AJAX requests
		if ( wp_doing_ajax() ) {
			return;
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$query_args = array(
			'login' => 'failed',
		);

		// Append redirect_to parameters.
		if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to               = filter_var( $_REQUEST['redirect_to'], FILTER_UNSAFE_RAW );
			$query_args['redirect_to'] = urlencode( $redirect_to );
		}

		// Construct the new url.
		$redirect_url = add_query_arg(
			$query_args,
			$login_page
		);

		if ( ! $login_page ) {
			return;
		}

		wp_safe_redirect( $redirect_url );

		exit;
	}

	/**
	 * Redirect to custom login page if username or password is empty
	 */
	public static function maybe_remove_login_hooks( $user, $username, $password ) {

		// Check for REST requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		}
		// Redundant check for REST because in some cases REST_REQUEST constant does not work.
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false ) {
			return $user;
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );
		if ( ! $login_page ) {
			return $user;
		}
		$login_mode_enabled = '';

		if ( class_exists( '\LearnDash_Settings_Section' ) ) {
			$login_mode_enabled = \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_mode_enabled' );
		}

		if ( isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] === $login_page ) {
			remove_action( 'wp_login_failed', 'learndash_login_failed', 1 );
		} elseif ( 'yes' !== $login_mode_enabled ) {
			remove_action( 'wp_login_failed', 'learndash_login_failed', 1 );
		}

		// remove theme my login login recaptcha validation during our recaptcha validation
		remove_filter( 'wp_authenticate_user', 'tml_recaptcha_validate_login' );

		return $user;
	}

	/**
	 * Redirect to custom login page if username or password is empty
	 */
	public static function verify_username_password_40( $user, $username, $password ) {
		// Check for REST requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		}
		// Redundant check for REST because in some cases REST_REQUEST constant does not work.
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false ) {
			return $user;
		}

		$uo_manual_verification = 'no';

		$settings = get_option( 'FrontendLoginPlus', array() );

		if ( false !== $settings ) {

			foreach ( $settings as $setting ) {

				if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
					$uo_manual_verification = 'yes';
				}
			}
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );
		if ( ! $login_page ) {
			return $user;
		}
		if ( isset( $_GET['redirect_to'] ) ) {
			$login_page = add_query_arg( array( 'redirect_to' => $_GET['redirect_to'] ), $login_page );
		}
		if ( isset( $_GET['redirect_to'] ) && false !== strpos( $_GET['redirect_to'], 'wp-admin' ) ) {
			wp_safe_redirect( add_query_arg( array( 'ojs' => 'wp-admin' ), $login_page ), 301 );
			exit;
		}

		if ( 'yes' === $uo_manual_verification ) {

			if ( ( $user ) && ( is_a( $user, 'WP_User' ) ) ) {

				$user_verified_value = get_user_meta( $user->ID, self::$user_meta_key_col, true );

				// bypass admins
				if ( user_can( $user->ID, 'activate_plugins' ) ) {
					$user_verified_value = '1';
				}

				// Is the use logging in disabled?
				if ( '0' === $user_verified_value ) {
					wp_destroy_current_session();
					wp_clear_auth_cookie();
					wp_safe_redirect( add_query_arg( array( 'login' => 'notverified' ), $login_page ) );
					exit;
				}
			} else {
				$login_page = get_permalink( self::get_login_redirect_page_id() );
				wp_safe_redirect( add_query_arg( array( 'login' => 'failed' ), $login_page ) );
				exit;
			}
		}

		$registering = false;
		if ( isset( $_GET['checkemail'] ) && 'registered' === $_GET['checkemail'] ) {
			$registering = true;
		}

		if ( '' === $username || '' === $password ) {
			if ( false === $registering ) {
				wp_safe_redirect( add_query_arg( array( 'login' => 'empty' ), $login_page ) );
				exit;
			}
		}

		// get referer page id and content for shortcode
		$referer_id         = url_to_postid( wp_get_referer() );
		$is_short_code_page = false;
		if ( ! empty( $referer_id ) ) {
			$referer_page = get_post( $referer_id );

			$is_short_code_page = ( has_shortcode( $referer_page->post_content, 'uo_login_ui' ) || has_shortcode( $referer_page->post_content, 'uo_login' ) || has_block( 'uncanny-toolkit/frontend-login', $referer_page->post_content ) );

		}

		$ld_integrity = self::ld_integrity_captcha_enabled();
		if ( empty( $ld_integrity ) ) {
			$recaptcha_key         = self::get_catpcha_key();
			$recaptcha_secrete_key = self::get_captcha_secret();

			// check if recaptcha is setup
			if ( ! empty( trim( $recaptcha_key ) ) && ! empty( trim( $recaptcha_secrete_key ) ) && ( self::get_login_redirect_page_id() === $referer_id || $is_short_code_page ) ) {

				if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
					wp_safe_redirect( add_query_arg( array( 'login' => 'recaptchafailed' ), $login_page ) );
					exit;
				}

				// make sure its filled
				$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );

				if ( empty( trim( $recaptcha_response ) ) ) {
					wp_safe_redirect( add_query_arg( array( 'login' => 'recaptchaempty' ), $login_page ) );
					exit;
				}

				$result = self::get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response );

				// return if there is an error
				if ( false === $result ) {
					wp_safe_redirect( add_query_arg( array( 'login' => 'recaptchafailed' ), $login_page ) );
					exit;
				}
			}
		}

		/**
		 * Check if redirect_to is set in GET/POST then set it to top priority
		 */

		if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) && $_REQUEST['redirect_to'] != home_url( '/wp-admin/' ) ) {
			// checking if login redirect priority is set.

			$settings = get_option( 'LoginRedirect', array() );
			if ( ! empty( $settings ) ) {
				foreach ( $settings as $setting ) {

					if ( 'redirect_priority' === $setting['name'] ) {
						$redirect_priority = $setting['value'];
					}
				}
			}
			if ( empty( $redirect_priority ) ) {
				$redirect_priority = 999;
			}

			add_filter(
				'login_redirect',
				function ( $redirect_to, $request, $user ) {
					return $_REQUEST['redirect_to'];
				},
				$redirect_priority,
				3
			);
		}
		if ( true === self::is_integrity_concurrent_login_enabled() && method_exists( '\LearnDash\Integrity\Prevent_Concurrent_Login', 'is_login_quota_available' ) && false === \LearnDash\Integrity\Prevent_Concurrent_Login::is_login_quota_available( $user->ID ) ) {
			//wp_logout();
			wp_safe_redirect( add_query_arg( array( 'exceed_max_concurrent_login' => 'maximumtimesloggedin' ), $login_page ) );
			exit;
		}

		return $user;
	}

	/**
	 * Redirect from wp-login.php to custom login page if user logged out
	 */
	public static function logout_page() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'login' => 'false' ), $login_page ) );
		exit;
	}

	/**
	 * Redirect from wp-login.php to custom login page if user lost password
	 * reCatpcha failed
	 */
	public static function lostpassword_post() {

		if ( isset( $_POST['action'] ) && 'send-password-reset' == $_POST['action'] ) {
			return; // bail.
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
			if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'action' => 'forgot',
						//	'success' => 'recaptchafailed',
						),
						$login_page
					)
				);
				exit;
			}
			// make sure its filled
			$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );

			if ( empty( trim( $recaptcha_response ) ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'action'  => 'forgot',
							'success' => 'recaptchaempty',
						),
						$login_page
					)
				);
				exit;
			}

			$result = self::get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response );

			// return if there is an error
			if ( false === $result ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'action'  => 'forgot',
							'success' => 'recaptchafailed',
						),
						$login_page
					)
				);
				exit;
			}
		}
	}

	/**
	 * Redirect from wp-login.php to custom login page if user lost password
	 */
	public static function redirect_lost_password( $lostpassword_redirect ) {

		$valid_credentials = false;
		if ( isset( $_POST['user_login'] ) && ! empty( $_POST['user_login'] ) ) {
			if ( email_exists( $_POST['user_login'] ) ) {
				$valid_credentials = true;
			}
			if ( username_exists( $_POST['user_login'] ) ) {
				$valid_credentials = true;
			}
		}

		if ( ! $valid_credentials ) {
			$login_page = get_permalink( self::get_login_redirect_page_id() );
			wp_safe_redirect(
				add_query_arg(
					array(
						'action'  => 'forgot',
						'success' => 'invalidcredentials',
					),
					$login_page
				)
			);
			exit;
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect(
			add_query_arg(
				array(
					'action'  => 'forgot',
					'success' => 'false',
				),
				$login_page
			)
		);
		exit;
	}


	/**
	 * Set wp-login redirect to frontend page
	 */
	public static function get_login_redirect_page_id() {

		$page_id = 0;

		$settings = get_option( 'FrontendLoginPlus', array() );

		foreach ( $settings as $setting ) {
			if ( 'login_page' === $setting['name'] ) {
				if ( 'publish' === get_post_status( $setting['value'] ) ) {
					$page_id = $setting['value'];
				}
			}
		}

		return (int) $page_id;
	}

	/*
	 * Add lost password link the login form
	 */
	/**
	 * @return string
	 */
	public static function add_lost_password_link() {

		$login_page                         = get_permalink( self::get_login_redirect_page_id() );
		$link                               = add_query_arg( array( 'action' => 'lostpassword' ), $login_page );
		$uo_frontend_login_forgetpass_label = self::get_settings_value( 'uo_frontend_login_forgetpass_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		return '<a class="forgot-link" href="' . $link . '">' . $uo_frontend_login_forgetpass_label . '</a>';
	}

	/**
	 * Add reCaptcha to the login form
	 *
	 * @return false|string
	 */
	public static function add_recaptcha_box() {

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
			ob_start();
			?>
			<div class="ult-form__row ult-form__row--recaptcha">
				<div class="ult-form-recaptcha"
					 data-sitekey="<?php echo esc_attr( $recaptcha_key ); ?>"
					 data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
			</div>
			<?php
			return ob_get_clean();

		} else {
			$ld_integrity = self::ld_integrity_captcha_enabled();
			// Disable captcha in Toolkit if v3 is enabled
			if ( ! empty( $ld_integrity ) && isset( $ld_integrity['v3'] ) && 'yes' === $ld_integrity['v3'] ) {
				$recaptcha = new \LearnDash\Integrity\reCaptcha();
				$recaptcha->enqueue_captcha_script();
			}
		}

	}

	/*
	 * Custom email message to retrieve password
	 */
	/**
	 * @param        $message
	 * @param string $key
	 * @param string $user_login
	 * @param null $user_data
	 *
	 * @return string|string[]
	 */
	public static function custom_retrieve_password_message( $message, $key = '', $user_login = '', $user_data = null ) {

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$reset_args = array(
			'action' => 'rp',
			'key'    => $key,
			'login'  => rawurlencode( $user_login ),
		);

		$reset_link = add_query_arg( $reset_args, $login_page );

		$custom_message = self::get_settings_value( 'uo_frontend_resetpassword_email_body', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		// Adding priority for avoiding conflict with other plugins like WP Better Emails
		add_filter(
			'wp_mail_content_type',
			array(
				__CLASS__,
				'htmlEmailContent',
			),
			100
		);
		$custom_message = nl2br( $custom_message );
		$custom_message = str_ireplace( '%User Login%', $user_login, $custom_message );
		$custom_message = str_ireplace( '%Reset Link%', $reset_link, $custom_message );

		return $custom_message;
	}

	/*
	 * Custom email message to retrieve password
	 */
	/**
	 * @param        $message
	 * @param string $user_login
	 * @param null $user_data
	 *
	 * @return string|string[]
	 */
	public static function custom_retrieve_password_title( $message, $user_login = '', $user_data = null ) {

		$custom_message = self::get_settings_value( 'uo_frontend_resetpassword_email_subject', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		if ( ! empty( $custom_message ) ) {
			$custom_message = str_ireplace( '%User Login%', $user_login, $custom_message );

			return $custom_message;
		}

		return $message;
	}

	/*
	 * Prevent login lock out if frontend login is enabled but no shortcode exists on login page
	 * Add the temporary shortcode with a warning
	 */
	/**
	 *
	 */
	public static function maybe_add_ui_shortcode() {

		global $post;

		$settings = get_option( 'FrontendLoginPlus', array() );

		foreach ( $settings as $setting ) {
			if ( 'login_page' === $setting['name'] && '0' !== $setting['value'] ) {
				$login_page_id = $setting['value'];
				if ( $post instanceof \WP_Post && $post->ID === (int) $login_page_id ) {
					$should_validate = apply_filters( 'uo_frontend_login_modal_page_lockout_detection', true, $post );
					$should_validate = filter_var( strtolower( $should_validate ), FILTER_VALIDATE_BOOLEAN );
					if ( $should_validate && ! has_shortcode( $post->post_content, 'uo_login_ui' ) && ! has_block( 'uncanny-toolkit/frontend-login', $post->post_content ) ) {
						echo '<div id="ult-login-no-setup-notice"><strong>';
						_e( 'Note: This page has been set as the login page for this site. The form below has been added for your convenience. To hide this message, add the shortcode [uo_login_ui] or the Front End Login Gutenberg block to this page.', 'uncanny-learndash-toolkit' );
						echo '</strong></div>';
						echo do_shortcode( '[uo_login_ui]' );
					}
				}
			}
		}
	}

	/**
	 * @param $blocks
	 * @param $block_code
	 *
	 * @return bool
	 */
	/**
	 * @param $contentType
	 *
	 * @return string
	 */
	public static function htmlEmailContent( $contentType ) {
		return 'text/html';
	}

	/**
	 * Filter the result of get_site_url().
	 *
	 * @param string $url The URL.
	 * @param string $path The path.
	 * @param string $scheme The URL scheme.
	 *
	 * @since 3.0
	 * @return string The filtered URL.
	 */
	public static function filter_site_url( $url, $path, $scheme ) {
		global $pagenow;

		// Bail if currently visiting wp-login.php
		if ( 'wp-login.php' == $pagenow ) {
			return $url;
		}

		// Bail if currently customizing
		if ( is_customize_preview() ) {
			return $url;
		}

		// Parse the URL
		$parsed_url = parse_url( $url );

		// Determine the path
		$path = '';
		if ( ! empty( $parsed_url['path'] ) ) {
			$path = basename( trim( $parsed_url['path'], '/' ) );
		}

		// Parse the query
		$query = array();
		if ( ! empty( $parsed_url['query'] ) ) {
			parse_str( htmlspecialchars_decode( $parsed_url['query'] ), $query );
		}

		/**
		 * Bail if the URL is an interim-login URL
		 *
		 * @see https://core.trac.wordpress.org/ticket/31821
		 */
		if ( isset( $query['interim-login'] ) ) {
			return $url;
		}

		// Determine the action
		switch ( $path ) {
			case 'wp-login.php':
				// Determine the action
				$action = isset( $query['action'] ) ? $query['action'] : 'login';

				if ( 'rp' == $action ) {
					$login_page = get_permalink( self::get_login_redirect_page_id() );
					// Add the query
					$url = add_query_arg( $query, $login_page );
				}

				break;
			default:
				return $url;
		}

		return $url;
	}

	/**
	 * @param $current_theme
	 *
	 * @return string|string[]
	 */
	public static function set_ult_login_theme( $current_theme ) {

		if ( 'layout_1' === Config::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', 'default' ) ) {
			$current_theme = str_replace( 'default', 'layout_1', $current_theme );
		}

		return $current_theme;
	}

	/**
	 * Validate and reset user password.
	 *
	 * @since 3.1.2
	 */

	public static function validate_reset_password() {

		$login_page     = self::get_login_redirect_page_id();
		$login_page_url = get_permalink( $login_page );

		if ( strpos( $login_page_url, '?' ) ) {
			$login_page_url = $login_page_url . '&';
		} else {
			$login_page_url = $login_page_url . '?';
		}
		/* Validate Reset Password Information */
		$validate_password_reset = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'validatepasswordreset' === (string) esc_html( $_GET['action'] ) ) {
				$validate_password_reset = true;
			}
		}

		if ( $validate_password_reset ) {
			if ( ! isset( $_GET['issue'] ) ) {
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {

					list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );

					$user = check_password_reset_key( $rp_key, $rp_login );

				} else {
					$user = new \WP_Error( 'invalid_key', __( 'Invalid key.' ) );
				}

				// Password reset cookie was not set OR password rest key check failed
				if ( is_wp_error( $user ) ) {
					if ( $user->get_error_code() === 'expired_key' ) {
						wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=expiredkey' );
						die();
					} else {
						wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=invalidkey' );
						die();
					}
				}

				$errors = new \WP_Error();

				if ( ! isset( $_POST['pass1'] ) || ! isset( $_POST['pass2'] ) ) {

					$errors->add( 'pass', __( '<strong>ERROR</strong>: Password fields cannot be empty.', 'uncanny-learndash-toolkit' ) );

				} elseif ( $_POST['pass1'] !== $_POST['pass2'] ) {
					$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords do not match.', 'uncanny-learndash-toolkit' ) );
				} else {
					$_password_strength = self::get_settings_value( 'uo_frontendloginplus_reset_password_strength', __CLASS__ );

					if ( $_password_strength === 'on' ) {
						$password_ok = self::slt_fsp_password_strength( $_POST['pass1'], $user->user_login );
						if ( $password_ok < 3 ) {
							$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter a password of minimum 8 characters including at least 1 uppercase letter, 1 lowercase letter and 1 number.', 'uncanny-learndash-toolkit' ) );
						}
					}
				}

				if ( ! $errors->has_errors() ) {
					reset_password( $user, $_POST['pass1'] );
					wp_safe_redirect( $login_page_url . 'action=reset&success=true' );
					die();
				}
			}
		}

	}

	/**
	 * Check for password strength - based on JS function in pre-3.7 WP core:
	 * /wp-admin/js/password-strength-meter.js
	 *
	 * @param string $i The password.
	 * @param string $f The user's username.
	 *
	 * @since   1.0
	 * @return  integer 1 = very weak; 2 = weak; 3 = medium; 4 = strong
	 */
	public static function slt_fsp_password_strength( $i, $f ) {
		$h = 1;
		$e = 2;
		$b = 3;
		$a = 4;
		$d = 0;
		$g = null;
		$c = null;
		if ( strlen( $i ) < 4 ) {
			return $h;
		}
		if ( strtolower( $i ) === strtolower( $f ) ) {
			return $e;
		}
		if ( preg_match( '/[0-9]/', $i ) ) {
			$d += 10;
		}
		if ( preg_match( '/[a-z]/', $i ) ) {
			$d += 26;
		}
		if ( preg_match( '/[A-Z]/', $i ) ) {
			$d += 26;
		}
		if ( preg_match( '/[^a-zA-Z0-9]/', $i ) ) {
			$d += 31;
		}

		$g = log( pow( $d, strlen( $i ) ) );
		$c = $g / log( 2 );
		if ( $c < 47 ) {
			return $e;
		}
		if ( $c < 56 ) {
			return $b;
		}

		return $a;
	}

	/**
	 * Insert error box in login form for ajax errors
	 *
	 * @param string $content
	 *
	 * @since   3.3
	 * @return  string
	 */
	public static function ajax_error_message_box( $content ) {
		/*

		ob_start();
		?>

		<div class="ult-form__validation ult-hide" id="ult_error_container">
			<div class="ult-notice ult-notice--error"></div>
		</div>

		<?php

		// End output
		$output = ob_get_clean();

		// Add output to the current content, but at the bottom
		$content .= $output;

		*/

		return $content;

	}

	/**
	 * Insert error box in lost password form for ajax errors
	 *
	 * @param string $content
	 *
	 * @since   3.3
	 * @return  string
	 */
	public static function ajax_lp_error_message_box( $content ) {

		ob_start();
		?>
		<div class="ult-form__validation ult-hide" id="ult_lp_error_container">
			<div class="ult-notice ult-notice--error"></div>
		</div>
		<?php

		// End output
		$output = ob_get_clean();

		// Add output to the current content, but at the bottom
		echo $output;
	}

	/**
	 * Ajax action for login call
	 *
	 * @since   3.3
	 */
	public static function uo_login_action() {

		$response      = array();
		$response_code = 200;
		$secure_cookie = '';

		/* Validate ajax call here.
			if( ! check_ajax_referer('uncannyowl-learndash-toolkit','nonce') ) {
				$response['success'] = false;
				$response['message'] = esc_html__( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' );
				self::wp_send_json( $response, $response_code );
			}
		*/

		// Validate input then.
		if ( ! isset( $_POST['email'] ) || ! isset( $_POST['password'] ) || empty( $_POST['email'] ) || empty( $_POST['password'] ) ) {
			$response['success'] = false;
			$response['message'] = Config::get_settings_value( 'uo_frontend_login_failed_error', 'FrontendLoginPlus', esc_html__( 'Invalid username and/or password.', 'uncanny-learndash-toolkit' ) );
			self::wp_send_json( $response, $response_code );
		}

		// Internal note: This conditional isn't doing anything except increasing the cyclomatic complexity. Please consider removing.
		if ( ! isset( $response['success'] ) ) {
			if ( ! empty( $_POST['email'] ) && ! force_ssl_admin() ) {
				$user_name = sanitize_user( $_POST['email'] );
				$user      = get_user_by( 'login', $user_name );

				if ( ! $user && strpos( $user_name, '@' ) ) {
					$user = get_user_by( 'email', $user_name );
				}

				if ( $user ) {
					if ( get_user_option( 'use_ssl', $user->ID ) ) {
						$secure_cookie = true;
						force_ssl_admin( true );
					}
				}
			}
			$ld_integrity = self::ld_integrity_captcha_enabled();
			// Disable captcha in Toolkit if v3 is enabled
			if ( empty( $ld_integrity ) ) {
				$recaptcha_key         = self::get_catpcha_key();
				$recaptcha_secrete_key = self::get_captcha_secret();

				// check if recaptcha is setup
				if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
					if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
						$response['success'] = false;
						$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
						self::wp_send_json( $response, $response_code );
					}

					// make sure its filled
					$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );

					if ( empty( trim( $recaptcha_response ) ) ) {
						$response['success'] = false;
						$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
						self::wp_send_json( $response, $response_code );
					}

					$result = self::get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response );

					// return if there is an error
					if ( false === $result ) {
						$response['success'] = false;
						$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
						self::wp_send_json( $response, $response_code );
					}
				}
			}
			$credential = array(
				'user_login'    => $_POST['email'],
				'user_password' => $_POST['password'],
			);
			// Removing our old authentication filter here.
			remove_filter(
				'authenticate',
				array(
					__CLASS__,
					'verify_username_password_40',
				),
				40
			);

			/* At this point, we have credentials that "may" be for a valid user. */

			// Instead of directly attempting login, check if only verified accounts are allowed to login.
			$uo_manual_verification = 'no';

			$settings = get_option( 'FrontendLoginPlus', array() );

			if ( false !== $settings ) {

				foreach ( $settings as $setting ) {
					if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
						$uo_manual_verification = 'yes';
					}
				}
			}

			// If manual verification is on.
			if ( 'yes' === $uo_manual_verification ) {

				// Check if there's a user with the supplied email.
				$potential_user = get_user_by( 'email', $credential['user_login'] );

				// If not, also check if there's a user with the username.
				$potential_user = $potential_user ? $potential_user : get_user_by( 'login', $credential['user_login'] );

				// If there is a valid user with the suppied email/ username.
				if ( ( $potential_user ) && ( is_a( $potential_user, 'WP_User' ) ) ) {

					// Check if they are already verified.
					$user_verified_value = get_user_meta( $potential_user->ID, self::$user_meta_key_col, true );

					// Set Admins as verified by default.
					if ( user_can( $potential_user->ID, 'activate_plugins' ) ) {
						$user_verified_value = '1';
					}

					// If the user hasn't been verified yet.
					if ( '0' === $user_verified_value ) {

						// return a response indicating unverified status.
						$response['success'] = false;
						$response['message'] = Config::get_settings_value( 'uo_frontend_login_notverified_error', 'FrontendLoginPlus', esc_html__( 'This account is not verified.', 'uncanny-learndash-toolkit' ) );
						self::wp_send_json( $response, $response_code );
					}
				}
			}

			// At this point, either manual verification is disabled or the user has already been verified.
			do_action( 'uo_toolkit_frontend_login_user_verified_before_signon', $response );

			// Try logging in the user.
			$user = wp_signon( $credential, $secure_cookie );

			if ( is_wp_error( $user ) ) {
				if ( ! empty( $user->get_error_message() ) ) {
					$response['message'] = $user->get_error_message();
				} else {
					$response['message'] = Config::get_settings_value( 'uo_frontend_login_failed_error', 'FrontendLoginPlus', esc_html__( 'Invalid username and/or password.', 'uncanny-learndash-toolkit' ) );
				}
				$response['success'] = false;
				self::wp_send_json( $response, $response_code );
			}
		}

		// Decide the redirection it will only happen when user on login page
		if ( isset( $_REQUEST['redirectTo'] ) ) {
			$redirect_to = $_REQUEST['redirectTo'];
			// Redirect to HTTPS if user wants SSL.
			if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}
		} else {
			$redirect_to = admin_url();
		}

		$response['success'] = true;

		$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

		// Decide if there will be a redirect
		$redirect_ignored = Config::get_settings_value( 'uo_frontend_login_modal_ignore_redirect', 'FrontendLoginPlus', false );

		$redirect_ignored = apply_filters( 'uo_frontend_login_modal_ignore_redirect', $redirect_ignored, $redirect_to, $requested_redirect_to, $user );
		$uo_login_modal   = false;
		if ( isset( $_REQUEST['uo_is_login_modal'] ) && 1 === (int) $_REQUEST['uo_is_login_modal'] ) {
			$uo_login_modal = true;
		}

		if ( 'on' === $redirect_ignored && true === $uo_login_modal ) {
			$response['ignoredRedirectTo'] = true;
			$response['message']           = __( 'You are now logged in' );
		} else {
			$response['redirectTo'] = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
		}

		// Allow modifications.
		$response = apply_filters( 'uo-login-action-response', $response );

		// Allow actions to be perform.
		do_action( 'uo-login-action-before-json-response', $user );

		// Disable external websites redirect.
		$response['redirectTo'] = self::sanitize_redirect_url( $response['redirectTo'] );

		self::wp_send_json( $response, $response_code );

	}

	/**
	 * Returns a clean url. Will default to admin url if external url is passed.
	 *
	 * @param string $url
	 *
	 * @return string The cleaned url.
	 */
	public static function sanitize_redirect_url( $url ) {

		if ( ! empty( $url ) ) {
			if ( false === self::validate_redirect_url( $url ) ) {
				return add_query_arg(
					array( 'forbidden' => 'redirect_not_allowed' ),
					esc_url_raw( site_url() )
				);
			}
		}

		return esc_url_raw( $url );
	}

	/**
	 * Checks if redirect url is internal or external.
	 *
	 * @param string $url
	 *
	 * @return boolean False if external. Otherwise, true.
	 */
	public static function validate_redirect_url( $url = '' ) {

		$is_internal = parse_url( get_site_url(), PHP_URL_HOST ) === parse_url( $url, PHP_URL_HOST );

		return $is_internal || empty( $url );

	}

	/**
	 * Ajax action for lost password call
	 *
	 * @since   3.3
	 */
	public static function uo_lostPass_action() {
		$response      = array();
		$response_code = 200;

		do_action( 'uo_toolkit_frontend_login_lost_password_before' );

		// Validate ajax call here.
		/*
		if( ! check_ajax_referer('uncannyowl-learndash-toolkit','nonce') ) {
			$response['success'] = false;
			$response['message'] = esc_html__( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' );
			self::wp_send_json( $response, $response_code );
		}
		*/

		// validate inputs.
		if ( ! isset( $_POST['email'] ) || empty( $_POST['email'] ) ) {
			$response['success'] = false;
			$response['message'] = Config::get_settings_value( 'uo_login_forgot_pass_invalid_creds', 'FrontendLoginPlus', esc_html__( 'Invalid username/email.', 'uncanny-learndash-toolkit' ) );
			self::wp_send_json( $response, $response_code );
		}

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();
		// check if recaptcha is setup
		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {

			if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}

			// make sure its filled
			$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );

			if ( empty( trim( $recaptcha_response ) ) ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}

			$result = self::get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response );

			// return if there is an error
			if ( false === $result ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}
		}
		$_POST['user_login'] = $_POST['email'];
		// remove old validation
		remove_action(
			'lostpassword_post',
			array(
				__CLASS__,
				'lostpassword_post',
			)
		);
		$errors = self::retrieve_password();

		if ( ! is_wp_error( $errors ) ) {
			$response['success'] = true;
			$template_to_load    = apply_filters( 'uo-login-template', Config::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', 'default' ) );
			if ( 'default' === $template_to_load ) {
				$response['message'] = self::get_settings_value( 'uo_frontend_login_successsendemail', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				ob_start();
				?>
				<p>
					<?php echo $response['message']; ?>
				</p>
				<?php

				$response['message'] = ob_get_clean();
			} else {
				$response['message'] = self::get_settings_value( 'uo_frontend_login_successsendemail', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				ob_start();
				?>
				<?php do_action( 'uo_forgot_before_title' ); ?>

				<div class="ult-form__title">
					<?php echo self::get_settings_value( 'uo_login_forgot_pass_title', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) ); ?>
				</div>

				<?php do_action( 'uo_forgot_before_success' ); ?>

				<div class="ult-form__row ult-form__row--validation">
					<div class="ult-notice ult-notice--success">
						<?php do_action( 'uo_forgot_before_success_message' ); ?>
						<?php echo $response['message']; ?>
						<?php do_action( 'uo_forgot_after_success_message' ); ?>
					</div>
				</div>

				<?php do_action( 'uo_forgot_after_success' ); ?>
				<?php

				$response['message'] = ob_get_clean();
			}

			self::wp_send_json( $response, $response_code );

		} else {

			$response['success'] = false;

			// Iterate errors
			$response['message'] = '';

			foreach ( $errors->errors as $error_key => $error_message ) {
				// Check if it's the Toolkit "invalidcombo" error, and show a custom message
				if ( $error_key == 'invalidcombo' ) {
					$error_message = self::get_settings_value( 'uo_login_forgot_pass_invalid_creds', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				} else {
					$error_message = is_array( $error_message ) ? implode( ' ', $error_message ) : $error_message;
				}

				$response['message'] = '<div class="ult-forgot-password-error ult-forgot-password-error--' . esc_attr( $error_key ) . '">' . $error_message . '</div>';
			}

			self::wp_send_json( $response, $response_code );
		}

		$response['success'] = false;
		self::wp_send_json( $response, $response_code );

	}

	/**
	 * Copy of WordPress retrieve password
	 */
	private static function retrieve_password() {
		$errors = new \WP_Error();

		if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or email address.', 'uncanny-learndash-toolkit' ) );
		} elseif ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
			if ( empty( $user_data ) ) {
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no account with that username or email address.', 'uncanny-learndash-toolkit' ) );
			}
		} else {
			$login     = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		/**
		 * Fires before errors are returned from a password reset request.
		 *
		 * @param WP_Error $errors A WP_Error object containing any errors generated
		 *                         by using invalid credentials.
		 *
		 * @since 4.4.0 Added the `$errors` parameter.
		 *
		 * @since 2.1.0
		 */
		do_action( 'lostpassword_post', $errors );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: There is no account with that username or email address.', 'uncanny-learndash-toolkit' ) );

			return $errors;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key        = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s', 'uncanny-learndash-toolkit' ), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s', 'uncanny-learndash-toolkit' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', 'uncanny-learndash-toolkit' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		/* translators: Password reset notification email subject. %s: Site title */
		$title = sprintf( __( '[%s] Password Reset', 'uncanny-learndash-toolkit' ), $site_name );

		/**
		 * Filters the subject of the password reset email.
		 *
		 * @param string $title Default email title.
		 * @param string $user_login The username for the user.
		 * @param \WP_User $user_data \WP_User object.
		 *
		 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
		 *
		 * @since 2.8.0
		 */
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

		/**
		 * Filters the message body of the password reset mail.
		 *
		 * If the filtered message is empty, the password reset email will not be sent.
		 *
		 * @param string $message Default mail message.
		 * @param string $key The activation key.
		 * @param string $user_login The username for the user.
		 * @param \WP_User $user_data \WP_User object.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
		 */
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			wp_die( __( 'The email could not be sent. Possible reason: your host may have disabled the mail() function.', 'uncanny-learndash-toolkit' ) );
		}

		return true;
	}

	/**
	 * Validate and reset user password.
	 *
	 * @since 3.3
	 */

	public static function uo_reset_password_action() {
		$response      = array();
		$response_code = 200;

		// Validate ajax call here.
		/*
		if( ! check_ajax_referer('uncannyowl-learndash-toolkit','nonce') ) {
			$response['success'] = false;
			$response['message'] = esc_html__( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' );
			self::wp_send_json( $response, $response_code );
		}
		*/

		// validate inputs.
		if ( ! isset( $_POST['password'] ) || empty( $_POST['password'] ) || ! isset( $_POST['passwordRepeat'] ) || empty( $_POST['passwordRepeat'] ) ) {
			$response['success'] = false;
			$response['message'] = esc_html__( 'Password fields cannot be empty.', 'uncanny-learndash-toolkit' );
			self::wp_send_json( $response, $response_code );
		}

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		// check if recaptcha is setup
		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {

			if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}

			// make sure its filled
			$recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );

			if ( empty( trim( $recaptcha_response ) ) ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}

			$result = self::get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response );

			// return if there is an error
			if ( false === $result ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_recaptchafailed_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}
		}

		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {

			list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );

			$user = check_password_reset_key( $rp_key, $rp_login );

		} else {
			$user = new \WP_Error( 'invalid_key', __( 'Invalid key.' ) );
		}

		// Password reset cookie was not set OR password rest key check failed
		if ( is_wp_error( $user ) ) {
			$response['success'] = false;
			$response['message'] = self::get_settings_value( 'uo_frontend_login_invalidresetkey_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
			self::wp_send_json( $response, $response_code );
		}

		if ( ! isset( $_POST['password'] ) || ! isset( $_POST['passwordRepeat'] ) ) {

			$response['success'] = false;
			$response['message'] = esc_html__( 'Password fields cannot be empty.', 'uncanny-learndash-toolkit' );
			self::wp_send_json( $response, $response_code );

		} elseif ( $_POST['password'] !== $_POST['passwordRepeat'] ) {
			$response['success'] = false;
			$response['message'] = self::get_settings_value( 'uo_frontend_login_passwordnotmatch_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
			self::wp_send_json( $response, $response_code );
		}

		// check password strength
		$_password_strength = self::get_settings_value( 'uo_frontendloginplus_reset_password_strength', __CLASS__ );

		if ( $_password_strength === 'on' ) {
			$password_ok = self::slt_fsp_password_strength( $_POST['password'], $user->user_login );
			if ( $password_ok < 3 ) {
				$response['success'] = false;
				$response['message'] = self::get_settings_value( 'uo_frontend_login_passwordstrength_error', __CLASS__, 'Please enter a password of minimum 8 characters including at least 1 uppercase letter, 1 lowercase letter and 1 number.', self::get_class_settings( '', true ) );
				self::wp_send_json( $response, $response_code );
			}
		}

		// Finally reset Password
		reset_password( $user, $_POST['password'] );
		$response['success'] = true;
		$response['message'] = self::get_settings_value( 'uo_frontend_login_reset_successful', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );

		ob_start();

		do_action( 'uo_login_before_reset_success' );
		?>

		<div class="ult-notice ult-notice--success">
			<?php do_action( 'uo_login_before_reset_success_message' ); ?>

			<?php echo $response['message']; ?>

			<?php do_action( 'uo_login_before_reset_success_message' ); ?>
		</div>
		<?php
		$response['message'] = ob_get_clean();
		self::wp_send_json( $response, $response_code );
	}

	/**
	 * Add the default frontend login data to
	 * the main JS variable
	 *
	 * @since   3.3
	 */
	public static function uo_ajax_login_js( $js_data ) {
		global $post;

		// Add the default data
		$js_data['frontendLogin'] = array(
			'hasAjaxEnabled'         => false,
			'currentPageIsLoginPage' => false,
			'ui'                     => array(
				'showAnimationOnSubmit'  => true,
				'buttonDisabledOnSubmit' => true,
			),
			'i18n'                   => array(
				'checkReCaptcha' => self::get_settings_value( 'uo_frontend_login_recaptchaempty_error', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) ),
			),
		);

		// Check if the current page is the Login page
		if ( ! empty( $post ) && $post->ID == self::get_login_redirect_page_id() ) {
			$js_data['frontendLogin']['currentPageIsLoginPage'] = true;
		} else {

		}

		return $js_data;
	}

	/**
	 * Modify the frontend login JS data to know
	 * if the user has the AJAX functionality enabled
	 *
	 * @since   3.3
	 */
	public static function uo_ajax_login_js_ajax( $js_data ) {
		// First, check if the required data is defined
		if ( isset( $js_data['frontendLogin'] ) ) {
			// Overwrite the element that defines if the
			// login is using AJAX
			$js_data['frontendLogin']['hasAjaxEnabled'] = true;
		}

		return $js_data;
	}

	/**
	 *
	 */
	public static function uo_ajax_login_js_recaptcha_handler() {

		$recaptcha_key         = self::get_catpcha_key();
		$recaptcha_secrete_key = self::get_captcha_secret();

		if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
			?>

			<script>

				var UncannyToolkitFrontendLoginReCaptchaInit = function () {
					if (typeof UncannyToolkit !== 'undefined') {
						if (typeof UncannyToolkit.reCaptchaInit !== 'undefined') {
							if (document.readyState == 'complete' || document.readyState == 'interactive') {
								UncannyToolkit.reCaptchaInit();
							} else {
								document.addEventListener('DOMContentLoaded', function () {
									UncannyToolkit.reCaptchaInit();
								});
							}
						}
					}
				}

				var UncannyToolkitFrontendLoginReCaptchaCorrect = function (response) {
					if (typeof UncannyToolkit !== 'undefined') {
						if (typeof UncannyToolkit.frontendLogin !== 'undefined') {
							if (typeof UncannyToolkit.frontendLogin.reCaptcha !== 'undefined') {
								if (typeof UncannyToolkit.frontendLogin.reCaptcha.correct !== 'undefined') {
									UncannyToolkit.frontendLogin.reCaptcha.correct(response);
								}
							}
						}
					}
				}

			</script>

			<script
				src="https://www.google.com/recaptcha/api.js?onload=UncannyToolkitFrontendLoginReCaptchaInit&render=explicit"
				async defer></script>

			<?php
		}
	}

	/**
	 * Check and override if #uo_login added in menu items
	 *
	 * @param array $menu_items From WP Menu items to be displayed.
	 * @param array $menu_args From WP Menu args related to the menu set to be
	 *     displayed.
	 *
	 * @return array $menu_items
	 */
	public static function uo_login_menu_items( $menu_items, $menu_args = array() ) {
		global $post;
		foreach ( $menu_items as $menu_key => &$menu_item ) {
			if ( ( ! isset( $menu_item->url ) ) || ( empty( $menu_item->url ) ) || ( ! isset( $menu_item->classes ) ) || ( ! is_array( $menu_item->classes ) ) || ( empty( $menu_item->classes ) ) ) {
				continue;
			}
			// Replace default wp-login.php with hash
			/*if ( ( strpos( $menu_item->url, 'wp-login.php' ) !== false ) ) {
				$login_page_id = self::get_login_redirect_page_id();
				$login_page    = '#ult-modal-open----ult-login';
				if ( $login_page_id ) {
					$login_page = get_permalink( $login_page_id ) . $login_page;
				} else {
					$login_page = site_url( 'wp-login.php' ) . $login_page;
				}
				$menu_item->url = $login_page;
			}*/
			if ( ( strpos( $menu_item->url, '#ult-modal-open' ) !== false ) ) {
				if ( ! isset( $post->ID ) || $post->ID !== self::get_login_redirect_page_id() ) {
					if ( apply_filters( 'uo_login_menu_item_process', true, $menu_item, $menu_args ) ) {
						add_action(
							'wp_footer',
							array(
								__CLASS__,
								'load_login_modal',
							)
						);
					}
				}
			}
		}

		return $menu_items;
	}

	/**
	 * Inject login modal on front-end
	 *
	 * @since   3.3
	 */
	public static function load_login_modal() {
		global $is_included_ult_login;
		if ( true === $is_included_ult_login ) {
			return false;
		}
		$is_included_ult_login = true;

		ob_start();
		add_filter( 'login_form_bottom', array( __CLASS__, 'uo_login_modal_hidden_field' ), 20, 2 );
		include Config::get_template( 'frontend-login/login-modal.php' );
		remove_filter( 'login_form_bottom', array( __CLASS__, 'uo_login_modal_hidden_field' ), 20 );
		$uo_login_model_html = ob_get_clean();
		if ( ( isset( $uo_login_model_html ) ) && ( ! empty( $uo_login_model_html ) ) ) {
			echo $uo_login_model_html;
		}
	}

	/**
	 * @return false|string
	 */
	public static function uo_login_modal() {
		if ( '' === self::get_settings_value( 'uo_frontendloginplus_enable_ajax_support', __CLASS__, '' ) ) {
			return false;
		}

		$login_page_id = self::get_login_redirect_page_id();
		$login_page    = '#ult-modal-open----ult-login';
		if ( $login_page_id ) {
			$login_page = get_permalink( $login_page_id ) . $login_page;
		} else {
			$login_page = site_url( 'wp-login.php' ) . $login_page;
		}
		$uo_frontend_login_modal_button_title_label = self::get_settings_value( 'uo_frontend_login_modal_button_title_label', __CLASS__, '%placeholder%', self::get_class_settings( '', true ) );
		if ( ! is_user_logged_in() ) {
			ob_start();
			?>
			<a href="<?php echo $login_page; ?>"
			   class="ult-modal-open ult-login-modal-open" data-id="ult-login">
				<?php echo $uo_frontend_login_modal_button_title_label; ?>
			</a>
			<?php
			self::load_login_modal();
			$uo_login_model_html = ob_get_clean();

			return $uo_login_model_html;
		}

	}

	public static function uo_login_modal_hidden_field( $str, $args ) {
		$str .= '<input type="hidden" name="uo_is_login_modal" value="1" />';
		return $str;
	}

	/**
	 * @param $recaptcha_secrete_key
	 * @param $recaptcha_response
	 *
	 * @return bool
	 */
	public static function get_recaptcha_response( $recaptcha_secrete_key, $recaptcha_response ) {
		$post_data = http_build_query(
			array(
				'body' => array(
					'secret'   => $recaptcha_secrete_key,
					'response' => $recaptcha_response,
					'remoteip' => $_SERVER['REMOTE_ADDR'],
				),
			)
		);

		$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $post_data );

		if ( $response instanceof \WP_Error ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body, true );

		// return if there is an error
		if ( 200 === (int) $response_code && false === $result['success'] ) {
			return false;
		}

		return true;

	}

	/**
	 * @param      $response
	 * @param null $status_code
	 */
	public static function wp_send_json( $response, $status_code = null ) {
		@header( 'Content-Type: application/json;' );
		if ( null !== $status_code ) {
			status_header( $status_code );
		}

		echo json_encode( $response );

		die;
	}

	/**
	 * @return array
	 */
	public static function ld_integrity_captcha_enabled() {
		// check if LD integrity is enabled AND recpatcha is sent in LearnDash
		if ( class_exists( '\LearnDash\Integrity\reCaptcha' ) ) {
			$ld_integrity = get_option( 'learndash_settings_ld_integrity' );

			if ( isset( $ld_integrity['recaptcha'] ) && 'yes' === $ld_integrity['recaptcha'] ) {
				return $ld_integrity;
			}
		}

		return array();
	}

	/**
	 * @return mixed|string
	 */
	public static function get_catpcha_key( $v3_disable = true ) {
		$recaptcha_key = Config::get_settings_value( 'uo_frontend_login_recaptcha_key', 'FrontendLoginPlus' );
		$ld_integrity  = self::ld_integrity_captcha_enabled();
		if ( ! empty( $ld_integrity ) && isset( $ld_integrity['v2'] ) && 'yes' === $ld_integrity['v2'] && isset( $ld_integrity['site_key_v2'] ) && ! empty( $ld_integrity['site_key_v2'] ) ) {
			$recaptcha_key = $ld_integrity['site_key_v2'];
		}
		// Disable captcha in Toolkit if v3 is enabled
		if ( $v3_disable && ! empty( $ld_integrity ) && isset( $ld_integrity['v3'] ) && 'yes' === $ld_integrity['v3'] ) {
			$recaptcha_key = '';
		}

		return $recaptcha_key;
	}

	/**
	 * @return mixed|string
	 */
	public static function get_captcha_secret( $v3_disable = true ) {
		$recaptcha_secrete_key = Config::get_settings_value( 'uo_frontend_login_recaptcha_secret_key', 'FrontendLoginPlus' );
		$ld_integrity          = self::ld_integrity_captcha_enabled();
		if ( ! empty( $ld_integrity ) && isset( $ld_integrity['v2'] ) && 'yes' === $ld_integrity['v2'] && isset( $ld_integrity['secret_key_v2'] ) && ! empty( $ld_integrity['secret_key_v2'] ) ) {
			$recaptcha_secrete_key = $ld_integrity['secret_key_v2'];
		}
		// Disable captcha in Toolkit if v3 is enabled
		if ( $v3_disable && ! empty( $ld_integrity ) && isset( $ld_integrity['v3'] ) && 'yes' === $ld_integrity['v3'] ) {
			$recaptcha_secrete_key = '';
		}

		return $recaptcha_secrete_key;
	}

	/**
	 * @return bool
	 */
	public static function is_integrity_concurrent_login_enabled() {
		if ( class_exists( '\LearnDash\Integrity\Prevent_Concurrent_Login' ) ) {
			$ld_integrity = get_option( 'learndash_settings_ld_integrity' );
			if ( isset( $ld_integrity['prevent_concurrent_login'] ) && 'yes' === $ld_integrity['prevent_concurrent_login'] ) {
				return true;
			}
		}

		return false;
	}

}
