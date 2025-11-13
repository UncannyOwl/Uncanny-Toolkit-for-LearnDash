<?php
/**
 * Redirect Manager for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

defined( 'ABSPATH' ) || exit;

/**
 * Manages URL redirects for 2FA authentication flow.
 */
class Redirect_Manager {

	/**
	 * Builds the redirect URL for 2FA challenge.
	 *
	 * @return string The redirect URL.
	 */
	public function build_2fa_redirect_url() {
		// Get the login page URL from settings.
		$login_page_url = $this->get_login_page_url();

		// Add only the 2FA challenge flag (no sensitive data).
		$redirect_url = add_query_arg(
			array(
				'2fa_challenge' => '1',
			),
			$login_page_url
		);

		return $redirect_url;
	}

	/**
	 * Gets the login page URL from settings.
	 *
	 * @return string The login page URL.
	 */
	public function get_login_page_url() {
		$login_page_url = '';

		// Try to get login page from Frontend Login Plus settings.
		if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
			$login_page_id = $this->get_frontend_login_page_id();

			if ( $login_page_id > 0 ) {
				$login_page_url = get_permalink( $login_page_id );
			}
		}

		// Fallback to current URL if no specific login page is configured.
		if ( empty( $login_page_url ) ) {
			$login_page_url = $this->get_current_url();
		}

		// Clean up 2FA-related parameters from URL.
		$login_page_url = remove_query_arg(
			array(
				'2fa_challenge',
				'2fa_error',
			),
			$login_page_url
		);

		return $login_page_url;
	}

	/**
	 * Gets the Frontend Login Plus page ID from settings.
	 *
	 * @return int The login page ID, or 0 if not set.
	 */
	private function get_frontend_login_page_id() {
		$settings = get_option( 'FrontendLoginPlus', array() );

		if ( ! is_array( $settings ) ) {
			return 0;
		}

		foreach ( $settings as $setting ) {
			if ( isset( $setting['name'] ) && 'login_page' === $setting['name'] ) {
				$page_id = isset( $setting['value'] ) ? intval( $setting['value'] ) : 0;

				// Verify the page exists and is published.
				if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
					return $page_id;
				}
			}
		}

		return 0;
	}

	/**
	 * Redirects back to 2FA form with error message.
	 *
	 * @param string $error_message The error message.
	 * @return void
	 */
	public function redirect_with_error( $error_message ) {
		// Get the login page URL from settings.
		$login_page_url = $this->get_login_page_url();

		// Build redirect URL with error (cookie contains the auth data).
		$redirect_url = add_query_arg(
			array(
				'2fa_challenge' => '1',
				'2fa_error'     => rawurlencode( $error_message ),
			),
			$login_page_url
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Redirects to success page after successful 2FA.
	 *
	 * @param array $result The authentication result.
	 * @return void
	 */
	public function redirect_to_success( $result ) {
		// Determine redirect URL with proper fallbacks.
		$redirect_url = $this->determine_success_redirect_url( $result );

		// Validate and sanitize the URL.
		$redirect_url = wp_validate_redirect( $redirect_url, admin_url() );

		// Redirect to success page.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Determines the success redirect URL with fallbacks.
	 *
	 * @param array $result The authentication result.
	 * @return string The redirect URL.
	 */
	private function determine_success_redirect_url( $result ) {
		// First, try the result redirect_to.
		if ( isset( $result['redirect_to'] ) && ! empty( $result['redirect_to'] ) ) {
			return $result['redirect_to'];
		}

		// Fallback to $_REQUEST redirect_to (from login form).
		if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_url( wp_unslash( $_REQUEST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Final fallback to admin dashboard.
		return admin_url();
	}

	/**
	 * Gets the current URL.
	 *
	 * @return string The current URL.
	 */
	private function get_current_url() {
		$protocol    = is_ssl() ? 'https://' : 'http://';
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		return $protocol . $host . $request_uri;
	}
}
