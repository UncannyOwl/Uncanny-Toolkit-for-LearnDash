<?php
/**
 * Form Renderer for WP 2FA Integration
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\HTML\HTML_Helper;
use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Renders 2FA forms and handles form data preparation.
 */
class Form_Renderer {

	/**
	 * Gets the 2FA form data for rendering.
	 *
	 * @param array $challenge_data The validated challenge data.
	 * @return array The 2FA form data.
	 */
	public function get_2fa_form_data( $challenge_data ) {
		$user_id = $challenge_data['user_id'];
		$method  = $challenge_data['method'];

		return array(
			'_meta'          => array(
				'enabled'  => true,
				'provider' => 'wp2fa',
				'method'   => $method,
				'user_id'  => $user_id,
			),
			'params'         => array(
				'user_id'        => $user_id,
				'endpoint'       => Helper::get_2fa_endpoint( $user_id ),
				'rest_url'       => Helper::get_2fa_rest_url_template( $user_id ),
				'requires_token' => true,
				'nonce'          => $challenge_data['nonce'],
			),
			'instructions'   => HTML_Helper::get_instructions_text( $method ),
			'strings'        => $this->get_2fa_strings(),
			'backup_methods' => Helper::get_user_backup_methods( $user_id ),
		);
	}

	/**
	 * Renders the universal 2FA form.
	 *
	 * @param array $form_data The 2FA form data.
	 * @return string The rendered form HTML.
	 */
	public function render_2fa_form( $form_data ) {
		$template_path = HTML_Helper::get_form_template_path();

		if ( ! file_exists( $template_path ) ) {
			return '<div class="ult-notice ult-notice--error"><span class="ult-notice-text">' .
					esc_html__( '2FA form template not found.', 'uncanny-learndash-toolkit' ) .
					'</span></div>';
		}

		ob_start();
		include $template_path;
		return ob_get_clean();
	}

	/**
	 * Gets the 2FA strings for translation.
	 *
	 * @return array The 2FA strings.
	 */
	private function get_2fa_strings() {
		return array(
			'twoFactorTitle'        => __( 'Two-Factor Authentication', 'uncanny-learndash-toolkit' ),
			'enterCode'             => __( 'Enter code', 'uncanny-learndash-toolkit' ),
			'codePlaceholder'       => __( '6-digit code', 'uncanny-learndash-toolkit' ),
			'verifyCode'            => __( 'Verify Code', 'uncanny-learndash-toolkit' ),
			'backupCodeLabel'       => __( 'Backup Code', 'uncanny-learndash-toolkit' ),
			'backupCodePlaceholder' => __( 'Enter backup code', 'uncanny-learndash-toolkit' ),
			'useBackupCode'         => __( 'Use Backup Code', 'uncanny-learndash-toolkit' ),
			'usePrimaryMethod'      => __( 'Use Primary Method', 'uncanny-learndash-toolkit' ),
			'goBack'                => __( 'Go Back', 'uncanny-learndash-toolkit' ),
			'invalidCode'           => __( 'Invalid code. Please try again.', 'uncanny-learndash-toolkit' ),
			'systemError'           => __( 'A system error occurred. Please refresh the page and try again.', 'uncanny-learndash-toolkit' ),
			'verifying'             => __( 'Verifying...', 'uncanny-learndash-toolkit' ),
		);
	}
}
