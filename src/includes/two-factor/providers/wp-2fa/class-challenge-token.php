<?php
/**
 * Challenge Token for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

defined( 'ABSPATH' ) || exit;

/**
 * Carries a pending 2FA login from the AJAX login request to the challenge page and back.
 *
 * The challenge page is an ordinary front-end page viewed by a logged-out visitor. Page caches and
 * reverse proxies routinely strip the Cookie header from exactly those requests, so a cookie cannot
 * carry the hand-off. The token travels in the URL (and in a hidden field of every challenge form).
 *
 * The token is the WP 2FA login transaction itself, signed so that it cannot be forged:
 * `<user id>.<login nonce>.<signature>`. WP 2FA owns everything else: it stores the nonce, expires
 * it, revokes it when the password changes, deletes it once the second factor passes and locks the
 * second factor after repeated failures. Holding the token does not log anyone in; a valid second
 * factor is still required.
 *
 * The signature is checked before the nonce ever reaches WP 2FA. Releases before 4.2.0 delete the
 * stored nonce whenever a submitted one does not match, which would let anyone cancel another
 * user's pending login with a made-up token.
 */
class Challenge_Token {

	/**
	 * Query and form parameter that carries the token.
	 */
	const PARAM = '2fa_request';

	/**
	 * Builds the token for a login transaction.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $nonce   The WP 2FA login nonce key.
	 * @return string The signed token.
	 */
	public function build( $user_id, $nonce ) {
		$payload = absint( $user_id ) . '.' . $nonce;

		return $payload . '.' . $this->sign( $payload );
	}

	/**
	 * Resolves the token carried by the current request.
	 *
	 * @return array|false array( 'user_id' => int, 'nonce' => string, 'token' => string ), or false when
	 *                     the request carries no token or the token is malformed or not ours.
	 */
	public function resolve() {
		$token = $this->get_request_token();

		if ( 1 !== preg_match( '/^(\d+)\.([A-Za-z0-9]{8,128})\.([a-f0-9]{64})$/', $token, $parts ) ) {
			return false;
		}

		$user_id = (int) $parts[1];
		if ( 0 >= $user_id ) {
			return false;
		}

		if ( ! hash_equals( $this->sign( $parts[1] . '.' . $parts[2] ), $parts[3] ) ) {
			return false;
		}

		return array(
			'user_id' => $user_id,
			'nonce'   => $parts[2],
			'token'   => $token,
		);
	}

	/**
	 * Reads the raw token from the request. A submitted form carries it as a hidden field and wins
	 * over the query string.
	 *
	 * @return string The raw token, or an empty string.
	 */
	private function get_request_token() {
		// The login nonce inside the token is verified by WP 2FA, so there is no WordPress nonce to check here.
		if ( isset( $_POST[ self::PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return sanitize_text_field( wp_unslash( $_POST[ self::PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $_GET[ self::PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_text_field( wp_unslash( $_GET[ self::PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return '';
	}

	/**
	 * Signs a token payload.
	 *
	 * @param string $payload `<user id>.<login nonce>`.
	 * @return string The HMAC-SHA256 signature as 64 hex characters.
	 */
	private function sign( $payload ) {
		return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}
}
