<?php
/**
 * Authentication Handler for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Handles 2FA authentication flow and code validation.
 */
class Authentication_Handler {

	/**
	 * Cookie manager instance.
	 *
	 * @var Cookie_Manager
	 */
	private $cookie_manager;

	/**
	 * Redirect manager instance.
	 *
	 * @var Redirect_Manager
	 */
	private $redirect_manager;

	/**
	 * Constructor.
	 *
	 * @param Cookie_Manager   $cookie_manager Cookie manager instance.
	 * @param Redirect_Manager $redirect_manager Redirect manager instance.
	 */
	public function __construct( Cookie_Manager $cookie_manager, Redirect_Manager $redirect_manager ) {
		$this->cookie_manager   = $cookie_manager;
		$this->redirect_manager = $redirect_manager;
	}

	/**
	 * Intercepts login attempts to handle 2FA for traditional forms.
	 *
	 * @param \WP_User|\WP_Error $user The user object or error.
	 * @param string             $password The user's password (unused).
	 * @return \WP_User|\WP_Error The user object or error.
	 */
	public function intercept_login_attempt( $user, $password ) {
		// Skip if this is an error or AJAX request.
		if ( is_wp_error( $user ) || wp_doing_ajax() ) {
			return $user;
		}

		// Skip if we're already in a 2FA challenge.
		if ( isset( $_GET['2fa_challenge'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $user;
		}

		// Check if user has 2FA enabled.
		$enabled_method = Helper::get_user_2fa_method( $user->ID );

		if ( empty( $enabled_method ) || 'unknown' === $enabled_method ) {
			return $user; // No 2FA required.
		}

		// Destroy current session and create login nonce.
		Helper::destroy_user_session( $user );
		$login_nonce = Helper::create_login_nonce( $user->ID );

		if ( ! $login_nonce ) {
			return new \WP_Error( 'wp2fa_nonce_failed', __( 'Authentication failed. Please try again.', 'uncanny-learndash-toolkit' ) );
		}

		// Send email OTP if needed (for email 2FA).
		if ( 'email' === $enabled_method ) {
			Helper::send_email_otp( $user->ID );
		}

		// Create secure 2FA cookie.
		$cookie_set = $this->cookie_manager->create_secure_2fa_cookie( $user->ID, $login_nonce['key'] );

		if ( ! $cookie_set ) {
			return new \WP_Error( 'wp2fa_cookie_failed', __( 'Authentication failed. Please try again.', 'uncanny-learndash-toolkit' ) );
		}

		// Build clean redirect URL (no sensitive data).
		$redirect_url = $this->redirect_manager->build_2fa_redirect_url();
		// Redirect to 2FA challenge.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handles primary code submission.
	 *
	 * @param int $user_id The user ID.
	 * @return void
	 */
	public function handle_primary_code_submission( $user_id ) {
		if ( ! isset( $_POST['authcode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->redirect_manager->redirect_with_error( __( 'Please enter your authentication code.', 'uncanny-learndash-toolkit' ) );
			return;
		}

		$code = sanitize_text_field( wp_unslash( $_POST['authcode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Validate code format.
		if ( ! preg_match( '/^\d{6}$/', $code ) ) {
			$this->redirect_manager->redirect_with_error( __( 'Please enter a valid 6-digit code.', 'uncanny-learndash-toolkit' ) );
			return;
		}

		// Validate via REST API.
		$result = Helper::validate_2fa_token( $user_id, $code );

		if ( is_wp_error( $result ) ) {
			$this->redirect_manager->redirect_with_error( $result->get_error_message() );
			return;
		}

		// Early exit for failed validation.
		if ( ! isset( $result['status'] ) || ! $result['status'] ) {
			$error_message = isset( $result['message'] ) ? $result['message'] : __( 'Invalid code. Please try again.', 'uncanny-learndash-toolkit' );
			$this->redirect_manager->redirect_with_error( $error_message );
			return;
		}

		// Success - handle authentication.
		$this->handle_successful_2fa( $user_id, $result );
	}

	/**
	 * Handles backup code submission.
	 *
	 * @param int $user_id The user ID.
	 * @return void
	 */
	public function handle_backup_code_submission( $user_id ) {
		if ( ! isset( $_POST['backupcode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->redirect_manager->redirect_with_error( __( 'Please enter your backup code.', 'uncanny-learndash-toolkit' ) );
			return;
		}

		$code = sanitize_text_field( wp_unslash( $_POST['backupcode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Validate backup code format.
		if ( empty( $code ) || ! preg_match( '/^[a-zA-Z0-9]+$/', $code ) ) {
			$this->redirect_manager->redirect_with_error( __( 'Please enter a valid backup code.', 'uncanny-learndash-toolkit' ) );
			return;
		}

		// For backup codes, we need to use the backup_codes endpoint.
		$endpoint = 'wp-2fa-methods/v1/login/' . $user_id . '/' . $code . '/backup_codes';
		$response = wp_remote_get( get_rest_url( null, $endpoint ) );

		if ( is_wp_error( $response ) ) {
			$this->redirect_manager->redirect_with_error( $response->get_error_message() );
			return;
		}

		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );

		// Early exit for failed validation.
		if ( ! isset( $result['status'] ) || ! $result['status'] ) {
			$error_message = isset( $result['message'] ) ? $result['message'] : __( 'Invalid backup code. Please try again.', 'uncanny-learndash-toolkit' );
			$this->redirect_manager->redirect_with_error( $error_message );
			return;
		}

		// Success - handle authentication.
		$this->handle_successful_2fa( $user_id, $result );
	}

	/**
	 * Handles the "Go Back" action by clearing the 2FA cookie.
	 *
	 * @return void
	 */
	public function handle_2fa_go_back() {
		// Clear the 2FA cookie.
		$this->cookie_manager->clear_2fa_cookie();

		// Redirect back to login page.
		wp_safe_redirect( $this->redirect_manager->get_login_page_url() );
		exit;
	}

	/**
	 * Handles successful 2FA authentication.
	 *
	 * @param int   $user_id The user ID.
	 * @param array $result The successful authentication result.
	 * @return void
	 */
	private function handle_successful_2fa( $user_id, $result ) {
		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			wp_die( esc_html__( 'Authentication error. Please try logging in again.', 'uncanny-learndash-toolkit' ) );
		}

		// Actually log the user in.
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		// Clear the 2FA cookie since authentication is complete.
		$this->cookie_manager->clear_2fa_cookie();

		// Trigger login action for other plugins.
		do_action( 'wp_login', $user->user_login, $user );

		// Redirect to success page.
		$this->redirect_manager->redirect_to_success( $result );
	}
}
