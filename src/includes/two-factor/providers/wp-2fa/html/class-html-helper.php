<?php
/**
 * HTML Helper for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * HTML Helper class for WP 2FA instructions.
 */
class HTML_Helper {

	/**
	 * Gets the text instructions for a specific 2FA method.
	 *
	 * @param string $method The 2FA method.
	 * @return string Text instructions.
	 */
	public static function get_instructions_text( $method ) {
		switch ( $method ) {
			case 'totp':
				return __( 'Enter the 6-digit code from your authenticator app (Google Authenticator, Authy, etc.)', 'uncanny-learndash-toolkit' );
			case 'email':
				return __( 'Check your email for a verification code and enter it below', 'uncanny-learndash-toolkit' );
			case 'backup_codes':
				return __( 'Enter one of your backup codes', 'uncanny-learndash-toolkit' );
			default:
				return __( 'Enter your 2FA verification code', 'uncanny-learndash-toolkit' );
		}
	}

	/**
	 * Gets the 2FA form template path.
	 *
	 * @return string The template file path.
	 */
	public static function get_form_template_path() {
		return UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/html/2fa-form.php';
	}
}
