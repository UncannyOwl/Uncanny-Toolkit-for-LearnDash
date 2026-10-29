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
	 * Challenge token instance.
	 *
	 * @var Challenge_Token
	 */
	private $challenge_token;

	/**
	 * Redirect manager instance.
	 *
	 * @var Redirect_Manager
	 */
	private $redirect_manager;

	/**
	 * Constructor.
	 *
	 * @param Challenge_Token  $challenge_token Challenge token instance.
	 * @param Redirect_Manager $redirect_manager Redirect manager instance.
	 */
	public function __construct( Challenge_Token $challenge_token, Redirect_Manager $redirect_manager ) {
		$this->challenge_token  = $challenge_token;
		$this->redirect_manager = $redirect_manager;
	}

	/**
	 * Intercepts login attempts to handle 2FA for traditional forms.
	 *
	 * @param \WP_User|\WP_Error $user The user object or error.
	 * @param string             $password The user's password (unused).
	 * @return \WP_User|\WP_Error The user object or error.
	 */
	public function intercept_login_attempt( $user, $username = '', $password = '' ) {
		// This runs on the authenticate filter at PHP_INT_MAX. Act only when core has
		// resolved a real user, i.e. the password has already been verified; a null or
		// WP_Error result (no/failed login) passes straight through.
		if ( ! $user instanceof \WP_User ) {
			return $user;
		}

		// The toolkit AJAX login (ult-login) carries out its own 2FA hand-off in
		// add_2fa_response_data(); skip that one request so the challenge isn't issued
		// twice. Any other AJAX login still gets intercepted here.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Identifies the ult-login request, whose handler verifies the AJAX nonce and runs its own 2FA hand-off in add_2fa_response_data().
		if ( wp_doing_ajax() && isset( $_POST['action'] ) && 'ult-login' === sanitize_key( wp_unslash( $_POST['action'] ) ) ) {
			return $user;
		}

		// Check if user has 2FA enabled.
		$enabled_method = Helper::get_user_2fa_method( $user->ID );

		if ( empty( $enabled_method ) || 'unknown' === $enabled_method ) {
			return $user; // No 2FA required.
		}

		// Non-interactive auth (REST, XML-RPC, and the Application Passwords flow that runs
		// under them) cannot complete a browser challenge, so this must not destroy the
		// user's sessions and emit a 302 the client can't follow. Refuse password auth for a
		// 2FA account over those transports instead; Application Passwords authenticate on
		// their own filter and never reach this point.
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
			return new \WP_Error(
				'uo_2fa_required',
				__( 'Two-factor authentication is required. Please sign in through the website.', 'uncanny-learndash-toolkit' )
			);
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

		// Build the redirect URL; the token carries the pending login to the challenge page.
		$redirect_url = $this->redirect_manager->build_2fa_redirect_url( $this->challenge_token->build( $user->ID, $login_nonce['key'] ) );
		// Redirect to 2FA challenge.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handles primary code submission.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token   The challenge token of the login being completed.
	 * @param string $nonce   The login nonce carried by that token.
	 * @return void
	 */
	public function handle_primary_code_submission( $user_id, $token, $nonce = '' ) {
		if ( ! isset( $_POST['authcode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reached only after the form handler verifies the signed challenge token and the WP 2FA login nonce.
			$this->redirect_manager->redirect_with_error( __( 'Please enter your authentication code.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		$code = sanitize_text_field( wp_unslash( $_POST['authcode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reached only after the form handler verifies the signed challenge token and the WP 2FA login nonce.

		// Validate code format.
		if ( ! preg_match( '/^\d{6}$/', $code ) ) {
			$this->redirect_manager->redirect_with_error( __( 'Please enter a valid 6-digit code.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		$this->complete_login( $user_id, $code, '', $token, $nonce );
	}

	/**
	 * Handles backup code submission.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token   The challenge token of the login being completed.
	 * @param string $nonce   The login nonce carried by that token.
	 * @return void
	 */
	public function handle_backup_code_submission( $user_id, $token, $nonce = '' ) {
		if ( ! isset( $_POST['backupcode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reached only after the form handler verifies the signed challenge token and the WP 2FA login nonce.
			$this->redirect_manager->redirect_with_error( __( 'Please enter your backup code.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		$code = sanitize_text_field( wp_unslash( $_POST['backupcode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reached only after the form handler verifies the signed challenge token and the WP 2FA login nonce.

		// Validate backup code format.
		if ( empty( $code ) || ! preg_match( '/^[a-zA-Z0-9]+$/', $code ) ) {
			$this->redirect_manager->redirect_with_error( __( 'Please enter a valid backup code.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		$this->complete_login( $user_id, $code, 'backup_codes', $token, $nonce );
	}

	/**
	 * Validate a submitted code under WP 2FA's rate limit, then log in on success.
	 *
	 * Mirrors WP 2FA's own REST controller: reserve an attempt before validating, count
	 * failures, and end the challenge once this sign-in's attempts are spent or the account
	 * is locked. The login nonce is consumed exactly once, so the challenge can't be replayed
	 * and the user's other pending logins are left intact.
	 *
	 * @param int    $user_id  The user resolved from the signed challenge token.
	 * @param string $code     The submitted code.
	 * @param string $provider '' for the primary method, 'backup_codes' for a backup code.
	 * @param string $token    The signed challenge token (for error redirects).
	 * @param string $nonce    The login nonce carried by the challenge token.
	 * @return void
	 */
	private function complete_login( $user_id, $code, $provider, $token, $nonce ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			$this->redirect_manager->redirect_with_error( __( 'Authentication error. Please try logging in again.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		// A login transaction must carry a nonce; without one there is nothing to consume
		// and no way to make the challenge single-use, so refuse rather than fail open.
		if ( '' === $nonce ) {
			wp_safe_redirect( $this->redirect_manager->get_login_page_url() );
			exit;
		}

		// Enforce WP 2FA's lockout before spending this attempt.
		if ( Helper::second_factor_locked( $user ) || ! Helper::reserve_second_factor_attempt( $user ) ) {
			Helper::consume_login_nonce( $user_id, $nonce );
			wp_safe_redirect( $this->redirect_manager->get_login_page_url() );
			exit;
		}

		$result = Helper::validate_2fa_token( $user_id, $code, $provider );

		// Failed validation: count the attempt; end the challenge once attempts run out.
		if ( is_wp_error( $result ) || empty( $result['status'] ) ) {
			Helper::increase_login_attempts( $user );
			$spent = ! Helper::attempts_remaining( $user );

			if ( Helper::second_factor_locked( $user ) || $spent ) {
				// Reset only the per-sign-in counter; the lockout window must survive so it
				// accumulates across challenges (the fallback limiter holds the lock here).
				Helper::reset_session_counter_on_lockout( $user );
				Helper::consume_login_nonce( $user_id, $nonce );
				wp_safe_redirect( $this->redirect_manager->get_login_page_url() );
				exit;
			}

			if ( is_wp_error( $result ) ) {
				$error_message = $result->get_error_message();
			} else {
				$error_message = isset( $result['message'] ) ? $result['message'] : __( 'Invalid code. Please try again.', 'uncanny-learndash-toolkit' );
			}
			$this->redirect_manager->redirect_with_error( $error_message, $token );
			return;
		}

		// Success: spend this login's nonce (single-use) before issuing the session.
		if ( ! Helper::consume_login_nonce( $user_id, $nonce ) ) {
			$this->redirect_manager->redirect_with_error( __( 'Security check failed or expired. Please try logging in again.', 'uncanny-learndash-toolkit' ), $token );
			return;
		}

		Helper::clear_login_attempts( $user );
		$this->handle_successful_2fa( $user_id, $result );
	}

	/**
	 * Handles the "Go Back" action by abandoning the pending 2FA login.
	 *
	 * @param array|false $challenge The verified challenge ('user_id' and 'nonce'), or false when there is none.
	 * @return void
	 */
	public function handle_2fa_go_back( $challenge ) {
		// Revoke the login transaction so the abandoned challenge URL stops working.
		if ( is_array( $challenge ) ) {
			Helper::delete_login_nonce( $challenge['user_id'], $challenge['nonce'] );
		}

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

		// Verification gate: a passed code must not log in an account the site hasn't verified.
		if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' )
			&& \uncanny_learndash_toolkit\FrontendLoginPlus::is_user_unverified( $user ) ) {
			wp_safe_redirect( add_query_arg( 'login', 'notverified', $this->redirect_manager->get_login_page_url() ) );
			exit;
		}

		// Actually log the user in.
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		// Trigger login action for other plugins.
		do_action( 'wp_login', $user->user_login, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook fired intentionally so other plugins can hook into this login.

		// Redirect to success page.
		$this->redirect_manager->redirect_to_success( $result );
	}
}
