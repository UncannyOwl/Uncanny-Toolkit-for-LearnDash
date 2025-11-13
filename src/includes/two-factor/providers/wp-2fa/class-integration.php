<?php
/**
 * Two-Factor Authentication Integration for Uncanny LearnDash Toolkit
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA;

use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\Helper;
use uncanny_learndash_toolkit\Includes\Two_Factor\Providers\WP2FA\HTML\HTML_Helper;
use uncanny_learndash_toolkit\Includes\Two_Factor\Session_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Main integration class for WP 2FA and Uncanny Toolkit.
 *
 * Orchestrates the various components to provide unified 2FA functionality
 * for both AJAX and traditional login forms.
 */
class Integration {

	/**
	 * Cookie manager instance.
	 *
	 * @var Cookie_Manager
	 */
	private $cookie_manager;

	/**
	 * Redirect manager instance.
	 *
	 * @var Redirect_Manager
	 */
	private $redirect_manager;

	/**
	 * Form renderer instance.
	 *
	 * @var Form_Renderer
	 */
	private $form_renderer;

	/**
	 * Authentication handler instance.
	 *
	 * @var Authentication_Handler
	 */
	private $auth_handler;

	/**
	 * Form submission handler instance.
	 *
	 * @var Form_Submission_Handler
	 */
	private $form_handler;

	/**
	 * Hook manager instance.
	 *
	 * @var Hook_Manager
	 */
	private $hook_manager;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->require_dependencies();
		$this->initialize_components();
		$this->setup_hooks();
	}

	/**
	 * Require dependencies
	 *
	 * @return void
	 */
	private function require_dependencies() {
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/class-session-manager.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-helper.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/html/class-html-helper.php';

		// Load new component classes.
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-cookie-manager.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-redirect-manager.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-form-renderer.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-authentication-handler.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-form-submission-handler.php';
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-hook-manager.php';
	}

	/**
	 * Initialize component instances.
	 *
	 * @return void
	 */
	private function initialize_components() {
		// Initialize core components.
		$this->cookie_manager   = new Cookie_Manager();
		$this->redirect_manager = new Redirect_Manager();
		$this->form_renderer    = new Form_Renderer();

		// Initialize handlers with dependencies.
		$this->auth_handler = new Authentication_Handler( $this->cookie_manager, $this->redirect_manager );
		$this->form_handler = new Form_Submission_Handler( $this->cookie_manager, $this->auth_handler, $this->form_renderer );

		// Initialize hook manager.
		$this->hook_manager = new Hook_Manager( $this->auth_handler, $this->form_handler, $this );
	}

	/**
	 * Setup WordPress hooks.
	 *
	 * @return void
	 */
	private function setup_hooks() {
		$this->hook_manager->remove_wp2fa_hooks_once();
		$this->hook_manager->register_toolkit_2fa_hooks_once();
		$this->hook_manager->register_2fa_form_handlers();
	}

	/**
	 * Checks if we should display the 2FA form instead of login form.
	 *
	 * @return bool True if 2FA form should be displayed.
	 */
	public function should_display_2fa_form() {
		return $this->form_handler->should_display_2fa_form();
	}

	/**
	 * Validates the 2FA challenge using secure cookie.
	 *
	 * @return array|false Array with user_id and method, or false if invalid.
	 */
	public function validate_2fa_challenge() {
		return $this->form_handler->validate_2fa_challenge();
	}

	/**
	 * Gets the 2FA form data for rendering.
	 *
	 * @param array $challenge_data The validated challenge data.
	 * @return array The 2FA form data.
	 */
	public function get_2fa_form_data( $challenge_data ) {
		return $this->form_renderer->get_2fa_form_data( $challenge_data );
	}

	/**
	 * Renders the universal 2FA form.
	 *
	 * @param array $form_data The 2FA form data.
	 * @return string The rendered form HTML.
	 */
	public function render_2fa_form( $form_data ) {
		return $this->form_renderer->render_2fa_form( $form_data );
	}

	/**
	 * Adds 2FA response data to the login response.
	 *
	 * @param array $response The login response.
	 * @return array The modified response.
	 */
	public function add_2fa_response_data( $response ) {

		// Only intercept successful logins for 2FA.
		if ( ! isset( $response['success'] ) || ! $response['success'] ) {

			return $response;
		}

		// Check if user has 2FA enabled.
		$user_id = $this->get_user_id_from_login_attempt();
		if ( ! $user_id ) {
			return $response;
		}

		$enabled_method = Helper::get_user_2fa_method( $user_id );

		if ( empty( $enabled_method ) || 'unknown' === $enabled_method ) {

			return $response; // No 2FA required.
		}

		// Get the user object for session destruction.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {

			return $response;
		}

		// Destroy current session and create login nonce (same as traditional flow).
		Helper::destroy_user_session( $user );
		$login_nonce = Helper::create_login_nonce( $user_id );

		if ( ! $login_nonce ) {

			return $response;
		}

		// Send email OTP if needed.
		if ( 'email' === $enabled_method ) {
			Helper::send_email_otp( $user_id );
		}

		// Create secure 2FA cookie (same as traditional flow).
		$cookie_manager = new Cookie_Manager();
		$cookie_set     = $cookie_manager->create_secure_2fa_cookie( $user_id, $login_nonce['key'] );

		if ( ! $cookie_set ) {

			return $response;
		}

		// Build redirect URL to 2FA challenge (same as traditional flow).
		$redirect_manager = new Redirect_Manager();
		$redirect_url     = $redirect_manager->build_2fa_redirect_url();

		// Convert successful login into redirect response.
		$response['success']                   = false; // Prevent normal login success.
		$response['message']                   = __( 'Redirecting to two-factor authentication...', 'uncanny-learndash-toolkit' );
		$response['data']['redirect_url']      = $redirect_url;
		$response['data']['requires_redirect'] = true;

		return $response;
	}

	/**
	 * Gets user ID from login attempt data.
	 *
	 * @return int|false User ID or false if not found.
	 */
	private function get_user_id_from_login_attempt() {
		// Traditional forms use 'log', AJAX forms use 'email'.
		// No need to verify nonce here as this is called after successful login attempt.
		// We just need to identify the user.
		$username = '';
		if ( isset( $_POST['log'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$username = sanitize_user( wp_unslash( $_POST['log'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_POST['email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$username = sanitize_user( wp_unslash( $_POST['email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			return false;
		}

		// Try by login first.
		$user = get_user_by( 'login', $username );

		// If not found and looks like email, try by email.
		if ( ! $user && strpos( $username, '@' ) !== false ) {
			$user = get_user_by( 'email', $username );
		}

		if ( $user ) {
			return $user->ID;
		} else {
			return false;
		}
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
