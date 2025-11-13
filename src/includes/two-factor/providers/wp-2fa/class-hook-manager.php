<?php
/**
 * Hook Manager for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

use uncanny_learndash_toolkit\Includes\Two_Factor\Session_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Manages WordPress hooks for 2FA integration.
 */
class Hook_Manager {

	/**
	 * Authentication handler instance.
	 *
	 * @var Authentication_Handler
	 */
	private $auth_handler;

	/**
	 * Form submission handler instance.
	 *
	 * @var Form_Submission_Handler
	 */
	private $form_handler;

	/**
	 * Integration instance (for legacy AJAX support).
	 *
	 * @var Integration
	 */
	private $integration;

	/**
	 * Constructor.
	 *
	 * @param Authentication_Handler  $auth_handler Authentication handler instance.
	 * @param Form_Submission_Handler $form_handler Form submission handler instance.
	 * @param Integration             $integration Integration instance.
	 */
	public function __construct( Authentication_Handler $auth_handler, Form_Submission_Handler $form_handler, Integration $integration ) {
		$this->auth_handler = $auth_handler;
		$this->form_handler = $form_handler;
		$this->integration  = $integration;
	}

	/**
	 * Removes WP 2FA's default hooks to prevent conflicts.
	 *
	 * @return void
	 */
	public function remove_wp2fa_hooks_once() {
		// Remove 2fa's action hook if the class is found and our hooks are not registered yet.
		if ( $this->is_wp2fa_active() && ! Session_Manager::is_hooks_registered( 'wp2fa_removed' ) ) {
			remove_action( 'wp_login', array( 'WP2FA\Authenticator\Login', 'wp_login' ), 20, 2 );
			Session_Manager::add_registered_hook( 'wp2fa_removed' );
		}
	}

	/**
	 * Register universal 2FA hooks for both AJAX and traditional forms.
	 *
	 * @return void
	 */
	public function register_toolkit_2fa_hooks_once() {
		if ( $this->is_wp2fa_active() && ! Session_Manager::is_hooks_registered( 'toolkit_2fa_registered' ) ) {
			// AJAX form handling (existing).
			add_filter( 'uo_toolkit_frontend_login_allow_signon', '__return_false' );
			add_action( 'uo-login-action-response', array( $this->integration, 'add_2fa_response_data' ) );

			// Universal login interception for traditional forms.
			add_filter( 'wp_authenticate_user', array( $this->auth_handler, 'intercept_login_attempt' ), 10, 2 );

			Session_Manager::add_registered_hook( 'toolkit_2fa_registered' );
		}
	}

	/**
	 * Registers 2FA form submission handlers.
	 *
	 * @return void
	 */
	public function register_2fa_form_handlers() {
		// Handle traditional form submissions for 2FA.
		add_action( 'init', array( $this->form_handler, 'handle_2fa_form_submission' ) );
	}

	/**
	 * Checks if WP 2FA plugin is active.
	 *
	 * @return bool True if WP 2FA is active.
	 */
	private function is_wp2fa_active() {
		return class_exists( 'WP2FA\Authenticator\Login' );
	}
}
