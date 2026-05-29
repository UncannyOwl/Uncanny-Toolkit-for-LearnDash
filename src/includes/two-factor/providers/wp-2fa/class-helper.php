<?php
/**
 * Helper Class for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class for WP 2FA integration.
 */
class Helper {

	/**
	 * Gets the user's enabled 2FA method.
	 *
	 * @param int $user_id The user ID.
	 * @return string The 2FA method or 'unknown'.
	 */
	public static function get_user_2fa_method( $user_id ) {
		if ( class_exists( '\WP2FA\Admin\Helpers\User_Helper' )
			&& method_exists( '\WP2FA\Admin\Helpers\User_Helper', 'get_enabled_method_for_user' ) ) {
			return \WP2FA\Admin\Helpers\User_Helper::get_enabled_method_for_user( $user_id );
		}
		return 'unknown';
	}

	/**
	 * Gets the user's backup 2FA methods.
	 *
	 * @param int $user_id The user ID.
	 * @return array Backup methods.
	 */
	public static function get_user_backup_methods( $user_id ) {
		if ( class_exists( '\WP2FA\Admin\Helpers\User_Helper' )
			&& method_exists( '\WP2FA\Admin\Helpers\User_Helper', 'get_enabled_backup_methods_for_user' ) ) {
			return \WP2FA\Admin\Helpers\User_Helper::get_enabled_backup_methods_for_user( $user_id );
		}
		return array();
	}

	/**
	 * Sends email OTP to the user.
	 *
	 * @param int $user_id The user ID.
	 * @return bool Success status.
	 */
	public static function send_email_otp( $user_id ) {
		// Try premium email backup method first.
		if ( class_exists( '\WP2FA\Methods\Email_Backup' )
			&& method_exists( '\WP2FA\Methods\Email_Backup', 'send_user_authentication_email' ) ) {
			return \WP2FA\Methods\Email_Backup::send_user_authentication_email( $user_id );
		}

		// Fallback to standard email method.
		if ( class_exists( '\WP2FA\Admin\Setup_Wizard' )
			&& method_exists( '\WP2FA\Admin\Setup_Wizard', 'send_authentication_setup_email' ) ) {
			return \WP2FA\Admin\Setup_Wizard::send_authentication_setup_email( $user_id );
		}

		return false;
	}

	/**
	 * Validates a 2FA token by invoking WP 2FA's internal filter directly.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token The token to validate.
	 * @param string $provider The 2FA method ('totp', 'email', 'backup_codes'). Auto-resolved from user meta if empty.
	 * @return array The validation result with 'status' (bool) and 'message' (string) keys.
	 */
	public static function validate_2fa_token( $user_id, $token, $provider = '' ) {
		if ( empty( $provider ) ) {
			$provider = self::get_user_2fa_method( $user_id );
		}

		// Prime WP 2FA's user context before validating.
		//
		// WP 2FA resolves the TOTP secret through User_Helper::get_user(), which ignores
		// any argument and returns the static current/primed user. Because the front-end
		// login flow destroys the session before the 2FA challenge, no current user exists
		// during code submission. Without priming, WP 2FA reads the secret for user 0 and
		// every code is rejected. Setting the user explicitly makes validation independent
		// of WP 2FA's internal side-effect ordering.
		if ( class_exists( '\WP2FA\Admin\Helpers\User_Helper' )
			&& method_exists( '\WP2FA\Admin\Helpers\User_Helper', 'set_user' ) ) {
			\WP2FA\Admin\Helpers\User_Helper::set_user( (int) $user_id );
		}

		$response = apply_filters( 'wp_2fa_validate_login_api', array( 'valid' => false ), $user_id, $token, $provider );

		if ( ! empty( $response['valid'] ) ) {
			// Clean up the login nonce on success, replicating what the REST handler did.
			if ( class_exists( '\WP2FA\Authenticator\Login' )
				&& method_exists( '\WP2FA\Authenticator\Login', 'delete_login_nonce' ) ) {
				\WP2FA\Authenticator\Login::delete_login_nonce( $user_id );
			}

			return array(
				'status'  => true,
				'message' => __( 'Authentication successful.', 'uncanny-learndash-toolkit' ),
			);
		}

		// Extract error message from the response if available.
		$error_message = __( 'Invalid code. Please try again.', 'uncanny-learndash-toolkit' );

		// WP 2FA callbacks return errors keyed by method name, e.g. ['totp' => ['error' => '...']].
		foreach ( array( 'totp', 'email', 'backup_codes' ) as $method_key ) {
			if ( isset( $response[ $method_key ]['error'] ) ) {
				$error_message = $response[ $method_key ]['error'];
				break;
			}
		}

		return array(
			'status'  => false,
			'message' => $error_message,
		);
	}

	/**
	 * Destroys the user session to force 2FA authentication.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	public static function destroy_user_session( $user ) {
		// Use WP 2FA's built-in session destruction if available.
		if ( class_exists( '\WP2FA\Authenticator\Login' )
			&& method_exists( '\WP2FA\Authenticator\Login', 'destroy_current_session_for_user' ) ) {
			\WP2FA\Authenticator\Login::destroy_current_session_for_user( $user );
		} else {
			// Manual session destruction as fallback.
			self::manual_destroy_user_session( $user );
		}

		self::clear_user_session();
	}

	/**
	 * Clears the user session.
	 *
	 * @return void
	 */
	public static function clear_user_session() {
		// Clear authentication cookies.
		wp_clear_auth_cookie();
		// Clear any existing user data.
		wp_set_current_user( 0 );
	}

	/**
	 * Creates a login nonce for 2FA authentication.
	 *
	 * @param int $user_id The user ID.
	 * @return array|false The login nonce array or false on failure.
	 */
	public static function create_login_nonce( $user_id ) {
		if ( class_exists( '\WP2FA\Authenticator\Login' )
			&& method_exists( '\WP2FA\Authenticator\Login', 'create_login_nonce' ) ) {
			return \WP2FA\Authenticator\Login::create_login_nonce( $user_id );
		}

		// Fallback: create a simple nonce.
		$nonce = array(
			'key'        => wp_create_nonce( 'wp_2fa_login_' . $user_id ),
			'expiration' => time() + ( 5 * MINUTE_IN_SECONDS ), // 5 minutes.
		);

		// Store the nonce in user meta.
		update_user_meta( $user_id, \WP2FA\Authenticator\Login::USER_META_NONCE_KEY, $nonce );

		return $nonce;
	}

	/**
	 * Manual session destruction as fallback.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	private static function manual_destroy_user_session( $user ) {
		// Get session tokens manager.
		$session_manager = \WP_Session_Tokens::get_instance( $user->ID );

		if ( $session_manager ) {
			// Destroy all sessions for this user.
			$session_manager->destroy_all();
		}

		// Also clear any user meta that might indicate active sessions.
		delete_user_meta( $user->ID, 'session_tokens' );
	}
}
