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
			return self::without_ajax_context(
				static function () use ( $user_id ) {
					return \WP2FA\Admin\Setup_Wizard::send_authentication_setup_email( $user_id );
				}
			);
		}

		return false;
	}

	/**
	 * Runs a callback while wp_doing_ajax() reports false.
	 *
	 * Since 4.2.0, WP 2FA's Setup_Wizard::send_authentication_setup_email() treats every
	 * admin-ajax request as its own "send setup email" AJAX handler: it requires a
	 * 'wp-2fa-send-setup-email' nonce (wp_send_json_error() and die otherwise) and discards the
	 * user ID it was given. The toolkit sends the login code from inside its own `ult-login`
	 * admin-ajax request, where that handler must not engage, so the call is made as WP 2FA
	 * makes it on a native login: a regular, non-AJAX request for the given user.
	 *
	 * @param callable $callback The callback to run.
	 * @return mixed The callback's return value.
	 */
	private static function without_ajax_context( callable $callback ) {
		add_filter( 'wp_doing_ajax', '__return_false', PHP_INT_MAX );

		try {
			return $callback();
		} finally {
			remove_filter( 'wp_doing_ajax', '__return_false', PHP_INT_MAX );
		}
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

		$response = apply_filters( 'wp_2fa_validate_login_api', array( 'valid' => false ), $user_id, $token, $provider ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP 2FA hook fired intentionally for compatibility with that plugin.

		if ( ! empty( $response['valid'] ) ) {
			// Nonce consumption is the caller's responsibility (single-use, this login
			// transaction only), so a success can't invalidate the user's other pending logins.
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
	 * Deletes a pending login nonce.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $nonce   The login nonce to revoke. WP 2FA 4.2.0+ removes only that login transaction;
	 *                        older releases hold one nonce per user and remove it.
	 * @return void
	 */
	public static function delete_login_nonce( $user_id, $nonce = '' ) {
		if ( ! class_exists( '\WP2FA\Authenticator\Login' ) ) {
			return;
		}

		if ( method_exists( '\WP2FA\Authenticator\Login', 'delete_login_nonce' ) ) {
			\WP2FA\Authenticator\Login::delete_login_nonce( $user_id, $nonce );
			return;
		}

		delete_user_meta( $user_id, \WP2FA\Authenticator\Login::USER_META_NONCE_KEY );
	}

	/**
	 * Consume exactly one login transaction's nonce (single-use).
	 *
	 * @param int    $user_id The user ID.
	 * @param string $nonce   The login nonce to consume.
	 * @return bool True when the nonce was valid and has now been spent.
	 */
	public static function consume_login_nonce( $user_id, $nonce ) {
		if ( class_exists( '\WP2FA\Authenticator\Login' )
			&& method_exists( '\WP2FA\Authenticator\Login', 'consume_login_nonce' ) ) {
			return (bool) \WP2FA\Authenticator\Login::consume_login_nonce( $user_id, $nonce );
		}

		// Older WP 2FA: no atomic consume; remove just this nonce.
		self::delete_login_nonce( $user_id, $nonce );
		return true;
	}

	/**
	 * Attempts allowed per sign-in before lockout when the fallback limiter is used.
	 */
	const FALLBACK_ATTEMPT_LIMIT = 5;

	/**
	 * How long (seconds) the fallback attempt counter is retained.
	 */
	const FALLBACK_ATTEMPT_WINDOW = 900;

	/**
	 * Whether WP 2FA's own rate-limit API is fully available.
	 *
	 * Gated on every method we rely on, so a partial API never leaves the limiter
	 * half-wired; when it isn't all present we fall back to a self-contained counter
	 * rather than silently allowing unlimited attempts.
	 *
	 * @return bool
	 */
	private static function native_lockout_available() {
		if ( ! class_exists( '\WP2FA\Authenticator\Authentication' ) ) {
			return false;
		}

		foreach ( array( 'second_factor_locked', 'reserve_second_factor_attempt', 'increase_login_attempts', 'check_number_of_attempts', 'clear_login_attempts' ) as $method ) {
			if ( ! method_exists( '\WP2FA\Authenticator\Authentication', $method ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Transient key for the fallback attempt counter.
	 *
	 * @param int $user_id The user ID.
	 * @return string
	 */
	private static function fallback_attempts_key( $user_id ) {
		return 'uo_2fa_attempts_' . (int) $user_id;
	}

	/**
	 * Whether this user is currently locked out of second-factor attempts.
	 *
	 * @param \WP_User $user The user object.
	 * @return bool
	 */
	public static function second_factor_locked( $user ) {
		if ( self::native_lockout_available() ) {
			return \WP2FA\Authenticator\Authentication::second_factor_locked( $user );
		}

		return (int) get_transient( self::fallback_attempts_key( $user->ID ) ) >= self::FALLBACK_ATTEMPT_LIMIT;
	}

	/**
	 * Reserve a single second-factor attempt. False when the window has no attempts left.
	 *
	 * @param \WP_User $user The user object.
	 * @return bool
	 */
	public static function reserve_second_factor_attempt( $user ) {
		if ( self::native_lockout_available() ) {
			return (bool) \WP2FA\Authenticator\Authentication::reserve_second_factor_attempt( $user );
		}

		return (int) get_transient( self::fallback_attempts_key( $user->ID ) ) < self::FALLBACK_ATTEMPT_LIMIT;
	}

	/**
	 * Record one failed second-factor attempt.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	public static function increase_login_attempts( $user ) {
		if ( self::native_lockout_available() ) {
			\WP2FA\Authenticator\Authentication::increase_login_attempts( $user );
			return;
		}

		$key = self::fallback_attempts_key( $user->ID );
		set_transient( $key, (int) get_transient( $key ) + 1, self::FALLBACK_ATTEMPT_WINDOW );
	}

	/**
	 * Whether this sign-in still has second-factor attempts left.
	 *
	 * @param \WP_User $user The user object.
	 * @return bool True when attempts remain.
	 */
	public static function attempts_remaining( $user ) {
		if ( self::native_lockout_available() ) {
			return (bool) \WP2FA\Authenticator\Authentication::check_number_of_attempts( $user );
		}

		return (int) get_transient( self::fallback_attempts_key( $user->ID ) ) < self::FALLBACK_ATTEMPT_LIMIT;
	}

	/**
	 * Clear the recorded second-factor attempts for this user. Called on success.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	public static function clear_login_attempts( $user ) {
		if ( self::native_lockout_available() ) {
			\WP2FA\Authenticator\Authentication::clear_login_attempts( $user );
		}

		// Always clear the fallback counter too, in case it was ever used for this user.
		delete_transient( self::fallback_attempts_key( $user->ID ) );
	}

	/**
	 * Reset only the per-sign-in attempt counter when a challenge is abandoned at lockout.
	 *
	 * Native WP 2FA keeps the account-wide lock in a separate store, so clearing the
	 * per-sign-in counter there matches its own flow. The fallback limiter holds the count
	 * and the lock in one window counter, so it must NOT be cleared here, or the lockout
	 * would reset the moment it trips and never accumulate across challenges.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	public static function reset_session_counter_on_lockout( $user ) {
		if ( self::native_lockout_available() ) {
			\WP2FA\Authenticator\Authentication::clear_login_attempts( $user );
		}
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
