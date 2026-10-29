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
		require_once UNCANNY_TOOLKIT_DIR . '/src/includes/two-factor/providers/wp-2fa/class-challenge-token.php';
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
		$this->challenge_token  = new Challenge_Token();
		$this->redirect_manager = new Redirect_Manager();
		$this->form_renderer    = new Form_Renderer();

		// Initialize handlers with dependencies.
		$this->auth_handler = new Authentication_Handler( $this->challenge_token, $this->redirect_manager );
		$this->form_handler = new Form_Submission_Handler( $this->challenge_token, $this->auth_handler, $this->form_renderer );

		// Initialize hook manager.
		$this->hook_manager = new Hook_Manager( $this->auth_handler, $this->form_handler, $this );
	}

	/**
	 * Setup WordPress hooks.
	 *
	 * @return void
	 */
	private function setup_hooks() {

		// Only enable if front.end login module is active.
		if ( false === \uncanny_learndash_toolkit\Config::is_toolkit_module_active( 'uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
			return;
		}

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
	 * Validates the 2FA challenge carried by the request token.
	 *
	 * @return array|false Array with user_id, method, nonce and token, or false if invalid.
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

		// Use the user wp_signon() actually authenticated (set by uo_login_action() as
		// $response['user_id']), never a login re-read from raw request data. Otherwise a
		// mismatched 'log'/'email' field could point the 2FA check at a different account
		// while the signed-in user's session stands.
		$user_id = isset( $response['user_id'] ) ? absint( $response['user_id'] ) : 0;
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

		// Build redirect URL to 2FA challenge (same as traditional flow). The token carries the
		// pending login; a cookie cannot, because page caches strip it from the challenge request.
		$redirect_manager = new Redirect_Manager();
		$redirect_url     = $redirect_manager->build_2fa_redirect_url( $this->challenge_token->build( $user_id, $login_nonce['key'] ) );

		// Convert successful login into redirect response.
		$response['success']                   = false; // Prevent normal login success.
		$response['message']                   = __( 'Redirecting to two-factor authentication...', 'uncanny-learndash-toolkit' );
		$response['data']['redirect_url']      = $redirect_url;
		$response['data']['requires_redirect'] = true;

		return $response;
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
