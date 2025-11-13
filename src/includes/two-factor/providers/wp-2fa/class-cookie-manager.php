<?php
/**
 * Cookie Manager for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

defined( 'ABSPATH' ) || exit;

/**
 * Manages secure cookies for 2FA authentication flow.
 */
class Cookie_Manager {

	/**
	 * Cookie name for 2FA authentication.
	 */
	const COOKIE_NAME = 'ult_2fa_auth';

	/**
	 * Cookie expiry time in seconds (15 minutes).
	 */
	const COOKIE_EXPIRY = 900; // 15 * 60

	/**
	 * Creates a secure cookie for 2FA challenge.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $nonce The nonce.
	 * @return bool True if cookie was set successfully.
	 */
	public function create_secure_2fa_cookie( $user_id, $nonce ) {
		// Create cookie payload with user data.
		$cookie_data = array(
			'user_id'         => $user_id,
			'nonce'           => $nonce,
			'timestamp'       => time(),
			'user_agent_hash' => $this->get_user_agent_hash(), // Browser fingerprinting.
		);

		// Encode the payload.
		// Note: base64 encoding is used here only for encoding, not for security. The security of the cookie comes from the HMAC signature below.
		$cookie_payload = base64_encode( wp_json_encode( $cookie_data ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		// Create HMAC signature to prevent tampering.
		$cookie_signature = $this->create_hmac_signature( $cookie_payload );

		// Combine payload and signature.
		$secure_cookie = $cookie_payload . '.' . $cookie_signature;

		// Set secure, HTTP-only cookie.
		return setcookie(
			self::COOKIE_NAME,
			$secure_cookie,
			time() + self::COOKIE_EXPIRY,
			'/',
			'',
			is_ssl(),
			true // HTTP-only.
		);
	}

	/**
	 * Validates and decodes the secure 2FA cookie.
	 *
	 * @return array|false The decoded cookie data or false if invalid.
	 */
	public function validate_secure_2fa_cookie() {
		// Check if cookie exists.
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return false;
		}

		$cookie_value = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );

		// Split cookie into payload and signature.
		$parts = explode( '.', $cookie_value );
		if ( 2 !== count( $parts ) ) {
			return false;
		}

		list( $cookie_payload, $provided_signature ) = $parts;

		// Verify the HMAC signature.
		$expected_signature = $this->create_hmac_signature( $cookie_payload );
		if ( ! hash_equals( $expected_signature, $provided_signature ) ) {
			return false;
		}

		// Decode the payload.
		$payload_json = base64_decode( $cookie_payload ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( ! $payload_json ) {
			return false;
		}

		$cookie_data = json_decode( $payload_json, true );
		if ( ! $cookie_data || ! is_array( $cookie_data ) ) {
			return false;
		}

		// Validate required fields.
		if ( ! $this->validate_cookie_structure( $cookie_data ) ) {
			return false;
		}

		// Check if cookie is expired.
		if ( $this->is_cookie_expired( $cookie_data['timestamp'] ) ) {
			return false;
		}

		// Validate browser fingerprint for extra security.
		if ( ! $this->validate_browser_fingerprint( $cookie_data ) ) {
			return false; // Different browser/user agent.
		}

		return $cookie_data;
	}

	/**
	 * Clears the 2FA authentication cookie.
	 *
	 * @return void
	 */
	public function clear_2fa_cookie() {
		setcookie( self::COOKIE_NAME, '', time() - 3600, '/', '', is_ssl(), true );
	}

	/**
	 * Creates HMAC signature for cookie payload.
	 *
	 * @param string $payload The cookie payload.
	 * @return string The HMAC signature.
	 */
	private function create_hmac_signature( $payload ) {
		return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}

	/**
	 * Gets the user agent hash for browser fingerprinting.
	 *
	 * @return string The user agent hash.
	 */
	private function get_user_agent_hash() {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return hash( 'sha256', $user_agent );
	}

	/**
	 * Validates cookie data structure.
	 *
	 * @param array $cookie_data The cookie data.
	 * @return bool True if valid structure.
	 */
	private function validate_cookie_structure( $cookie_data ) {
		return isset( $cookie_data['user_id'] ) &&
				isset( $cookie_data['nonce'] ) &&
				isset( $cookie_data['timestamp'] );
	}

	/**
	 * Checks if cookie is expired.
	 *
	 * @param int $timestamp The cookie timestamp.
	 * @return bool True if expired.
	 */
	private function is_cookie_expired( $timestamp ) {
		return ( time() - $timestamp ) > self::COOKIE_EXPIRY;
	}

	/**
	 * Validates browser fingerprint.
	 *
	 * @param array $cookie_data The cookie data.
	 * @return bool True if fingerprint matches.
	 */
	private function validate_browser_fingerprint( $cookie_data ) {
		if ( ! isset( $cookie_data['user_agent_hash'] ) ) {
			return true; // No fingerprint to validate.
		}

		$current_user_agent_hash = $this->get_user_agent_hash();
		return $cookie_data['user_agent_hash'] === $current_user_agent_hash;
	}
}
