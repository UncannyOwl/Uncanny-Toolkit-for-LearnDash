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
	 * Gets the 2FA endpoint URL.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token The token (optional).
	 * @param bool   $remember_device Whether to remember device.
	 * @return string The endpoint URL.
	 */
	public static function get_2fa_endpoint( $user_id, $token = '', $remember_device = false ) {
		$method = self::get_user_2fa_method( $user_id );

		$endpoint = 'wp-2fa-methods/v1/login/' . $user_id;

		if ( ! empty( $token ) ) {
			$endpoint .= '/' . $token;
		}

		if ( ! empty( $method ) && 'unknown' !== $method ) {
			$endpoint .= '/' . $method;
		}

		if ( $remember_device ) {
			$endpoint .= '/true';
		}

		return $endpoint;
	}

	/**
	 * Validates a 2FA token via the REST API.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token The token to validate.
	 * @param string $provider The provider (optional).
	 * @param bool   $remember_device Whether to remember device.
	 * @return array|WP_Error The validation result.
	 */
	public static function validate_2fa_token( $user_id, $token, $provider = '', $remember_device = false ) {
		$endpoint = self::get_2fa_endpoint( $user_id, $token, $remember_device );

		$response = wp_remote_get( get_rest_url( null, $endpoint ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data;
	}

	/**
	 * Gets the full REST API URL for 2FA validation.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $token The token to validate.
	 * @param bool   $remember_device Whether to remember device.
	 * @return string The full REST API URL.
	 */
	public static function get_2fa_rest_url( $user_id, $token = '', $remember_device = false ) {
		$endpoint = self::get_2fa_endpoint( $user_id, $token, $remember_device );
		return get_rest_url( null, $endpoint );
	}

	/**
	 * Gets the REST API URL template for 2FA validation (with {token} placeholder).
	 *
	 * @param int  $user_id The user ID.
	 * @param bool $remember_device Whether to remember device.
	 * @return string The REST API URL template.
	 */
	public static function get_2fa_rest_url_template( $user_id, $remember_device = false ) {
		$method = self::get_user_2fa_method( $user_id );

		$endpoint = 'wp-2fa-methods/v1/login/' . $user_id . '/{token}';

		if ( ! empty( $method ) && 'unknown' !== $method ) {
			$endpoint .= '/' . $method;
		}

		if ( $remember_device ) {
			$endpoint .= '/true';
		}

		return get_rest_url( null, $endpoint );
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
