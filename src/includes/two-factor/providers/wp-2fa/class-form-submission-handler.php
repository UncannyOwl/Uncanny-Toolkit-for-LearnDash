<?php
/**
 * Form Submission Handler for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Handles 2FA form submissions and validation.
 */
class Form_Submission_Handler {

	/**
	 * Cookie manager instance.
	 *
	 * @var Cookie_Manager
	 */
	private $cookie_manager;

	/**
	 * Authentication handler instance.
	 *
	 * @var Authentication_Handler
	 */
	private $auth_handler;

	/**
	 * Form renderer instance.
	 *
	 * @var Form_Renderer
	 */
	private $form_renderer;

	/**
	 * Constructor.
	 *
	 * @param Cookie_Manager         $cookie_manager Cookie manager instance.
	 * @param Authentication_Handler $auth_handler Authentication handler instance.
	 * @param Form_Renderer          $form_renderer Form renderer instance.
	 */
	public function __construct( Cookie_Manager $cookie_manager, Authentication_Handler $auth_handler, Form_Renderer $form_renderer ) {
		$this->cookie_manager = $cookie_manager;
		$this->auth_handler   = $auth_handler;
		$this->form_renderer  = $form_renderer;
	}

	/**
	 * Handles traditional 2FA form submissions.
	 *
	 * @return void
	 */
	public function handle_2fa_form_submission() {
		// Only process POST requests with 2FA action.
		if ( ! isset( $_POST['2fa_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$action = sanitize_text_field( wp_unslash( $_POST['2fa_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Handle go_back without cookie validation (user is abandoning 2FA).
		if ( 'go_back' === $action ) {
			$this->auth_handler->handle_2fa_go_back();
			return;
		}

		// For other actions, validate and decode the secure cookie.
		$cookie_data = $this->cookie_manager->validate_secure_2fa_cookie();
		if ( ! $cookie_data ) {
			wp_die( esc_html__( 'Security check failed or expired. Please try logging in again.', 'uncanny-learndash-toolkit' ) );
		}

		$user_id = intval( $cookie_data['user_id'] );
		$nonce   = $cookie_data['nonce'];

		// Validate nonce.
		if ( ! $this->validate_2fa_nonce( $user_id, $nonce ) ) {
			wp_die( esc_html__( 'Security check failed or expired. Please try logging in again.', 'uncanny-learndash-toolkit' ) );
		}

		// Handle different 2FA actions.
		switch ( $action ) {
			case 'verify_primary':
				$this->auth_handler->handle_primary_code_submission( $user_id );
				break;
			case 'verify_backup':
				$this->auth_handler->handle_backup_code_submission( $user_id );
				break;
			default:
				wp_die( esc_html__( 'Invalid action. Please try again.', 'uncanny-learndash-toolkit' ) );
		}
	}

	/**
	 * Checks if we should display the 2FA form instead of login form.
	 *
	 * @return bool True if 2FA form should be displayed.
	 */
	public function should_display_2fa_form() {
		return isset( $_GET['2fa_challenge'] ) && '1' === $_GET['2fa_challenge']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Validates the 2FA challenge using secure cookie.
	 *
	 * @return array|false Array with user_id and method, or false if invalid.
	 */
	public function validate_2fa_challenge() {
		// Check required parameters.
		if ( ! $this->should_display_2fa_form() ) {
			return false;
		}

		// Validate and decode the secure cookie.
		$cookie_data = $this->cookie_manager->validate_secure_2fa_cookie();
		if ( ! $cookie_data ) {
			return false;
		}

		$user_id = intval( $cookie_data['user_id'] );
		$nonce   = $cookie_data['nonce'];

		// Validate user exists.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		// Validate nonce.
		if ( ! $this->validate_2fa_nonce( $user_id, $nonce ) ) {
			return false;
		}

		// Get user's 2FA method.
		$enabled_method = Helper::get_user_2fa_method( $user_id );
		if ( empty( $enabled_method ) || 'unknown' === $enabled_method ) {
			return false;
		}

		return array(
			'user_id' => $user_id,
			'method'  => $enabled_method,
			'nonce'   => $nonce,
		);
	}

	/**
	 * Validates the 2FA nonce.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $nonce The nonce to validate.
	 * @return bool True if valid.
	 */
	private function validate_2fa_nonce( $user_id, $nonce ) {
		// Get stored nonce from user meta.
		$stored_nonce_data = get_user_meta( $user_id, \WP2FA\Authenticator\Login::USER_META_NONCE_KEY, true );

		if ( empty( $stored_nonce_data ) ) {
			return false;
		}

		// Extract the nonce key from the stored data.
		$stored_nonce_key = '';
		if ( is_array( $stored_nonce_data ) && isset( $stored_nonce_data['key'] ) ) {
			$stored_nonce_key = $stored_nonce_data['key'];
		} elseif ( is_string( $stored_nonce_data ) ) {
			$stored_nonce_key = $stored_nonce_data;
		} else {
			return false;
		}

		// Validate nonce by direct comparison (WP 2FA uses direct comparison, not wp_verify_nonce).
		$is_valid = hash_equals( $nonce, $stored_nonce_key );

		if ( ! $is_valid ) {
			// Clean up invalid nonce.
			delete_user_meta( $user_id, \WP2FA\Authenticator\Login::USER_META_NONCE_KEY );
		}

		return $is_valid;
	}
}
