<?php
/**
 * Integration for the WordPress.org "Two Factor" plugin (Two_Factor_Core).
 *
 * The toolkit logs users in over AJAX. The Two Factor plugin enforces 2FA by
 * printing its challenge page and exiting on the `wp_login` action, which breaks
 * the AJAX JSON response ("Something went wrong"). And its native challenge form
 * posts to `wp-login.php`, which this toolkit (and typical site hardening)
 * redirects/blocks, so the validation never runs.
 *
 * This integration keeps the ENTIRE 2FA exchange inside admin-ajax, which is not
 * page-cached and not intercepted by the login-page redirect:
 *   1. Suppress Two Factor's wp_login dump during the toolkit AJAX login.
 *   2. On a 2FA user, stash a one-time hand-off and redirect to an admin-ajax
 *      endpoint that renders Two Factor's own challenge form, with the form's
 *      POST target rewritten from wp-login.php to a second admin-ajax endpoint.
 *   3. That endpoint runs Two Factor's native validation
 *      (Two_Factor_Core::_login_form_validate_2fa), which sets the auth cookie
 *      and redirects on success, or re-renders the form on a bad code.
 *
 * wp-login.php / wp-admin logins keep using the Two Factor plugin natively, and
 * the integration only loads when WP 2FA (Melapress) is absent.
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit\Includes\Two_Factor\Providers\Core;

use uncanny_learndash_toolkit\Config;
use uncanny_learndash_toolkit\FrontendLoginPlus;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the toolkit's frontend login into the Two Factor plugin.
 */
class Integration {

	/**
	 * Prefix for the hand-off transient.
	 */
	const TRANSIENT_PREFIX = 'ult_2fa_tf_';

	/**
	 * admin-ajax action that renders the challenge form.
	 */
	const RENDER_ACTION = 'ult_2fa_challenge';

	/**
	 * admin-ajax action that validates the submitted code.
	 */
	const VALIDATE_ACTION = 'ult_2fa_validate';

	/**
	 * Hand-off lifetime, in seconds. Matches the Two Factor login nonce lifetime.
	 */
	const CHALLENGE_EXPIRY = 600;

	/**
	 * Guard so the hooks are only registered once per request.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Registers the hooks (only when the frontend login module is active).
	 */
	public function __construct() {

		if ( self::$booted ) {
			return;
		}

		if ( false === Config::is_toolkit_module_active( 'uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
			return;
		}

		self::$booted = true;

		// Stop Two Factor from printing its challenge page into the AJAX response.
		add_action( 'uo_toolkit_frontend_login_user_verified_before_signon', array( $this, 'suppress_native_challenge' ) );

		// Turn a successful AJAX login of a 2FA user into a redirect to the challenge.
		add_filter( 'uo-login-action-response', array( $this, 'maybe_redirect_to_challenge' ) );

		// Render and validate the challenge entirely within admin-ajax.
		add_action( 'wp_ajax_nopriv_' . self::RENDER_ACTION, array( $this, 'render_challenge' ) );
		add_action( 'wp_ajax_' . self::RENDER_ACTION, array( $this, 'render_challenge' ) );
		add_action( 'wp_ajax_nopriv_' . self::VALIDATE_ACTION, array( $this, 'validate_challenge' ) );
		add_action( 'wp_ajax_' . self::VALIDATE_ACTION, array( $this, 'validate_challenge' ) );
	}

	/**
	 * Removes the Two Factor handler that would otherwise print its challenge
	 * page (and exit) during wp_signon inside the AJAX request.
	 *
	 * Runs only on the toolkit AJAX login, so wp-login.php / wp-admin logins are
	 * unaffected. The auth cookie is still withheld for 2FA users by Two Factor's
	 * `filter_authenticate`, so the user is not logged in until the challenge is
	 * passed.
	 *
	 * @return void
	 */
	public function suppress_native_challenge() {
		remove_action( 'wp_login', array( 'Two_Factor_Core', 'wp_login' ), PHP_INT_MAX );
	}

	/**
	 * Converts a successful login response into a 2FA challenge redirect.
	 *
	 * @param array $response The toolkit login response.
	 * @return array The (possibly modified) response.
	 */
	public function maybe_redirect_to_challenge( $response ) {

		if ( empty( $response['success'] ) || ! class_exists( '\Two_Factor_Core' ) ) {
			return $response;
		}

		$user = $this->get_attempted_user();
		if ( ! $user || ! \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			return $response; // No 2FA required.
		}

		// Match Two Factor's native wp_login handler before starting the challenge.
		\Two_Factor_Core::destroy_current_session_for_user( $user );
		wp_clear_auth_cookie();

		$login_nonce = \Two_Factor_Core::create_login_nonce( $user->ID );
		if ( ! $login_nonce ) {
			$response['success'] = false;
			$response['message'] = __( 'Unable to start two-factor authentication. Please try again.', 'uncanny-learndash-toolkit' );
			return $response;
		}

		$redirect_to = isset( $response['redirectTo'] ) && ! empty( $response['redirectTo'] ) ? $response['redirectTo'] : admin_url();

		$token = $this->store_handoff( $user->ID, $login_nonce['key'], $redirect_to );
		if ( ! $token ) {
			$response['success'] = false;
			$response['message'] = __( 'Unable to start two-factor authentication. Please try again.', 'uncanny-learndash-toolkit' );
			return $response;
		}

		$response['success']                   = false; // Prevent the normal "logged in" path.
		$response['message']                   = __( 'Redirecting to two-factor authentication…', 'uncanny-learndash-toolkit' );
		$response['data']['requires_redirect'] = true;
		$response['data']['redirect_url']      = add_query_arg(
			array(
				'action' => self::RENDER_ACTION,
				'tf'     => $token,
			),
			admin_url( 'admin-ajax.php' )
		);

		return $response;
	}

	/**
	 * Renders Two Factor's native challenge form (admin-ajax), with its POST
	 * target rewritten to our validation endpoint.
	 *
	 * @return void
	 */
	public function render_challenge() {

		if ( ! class_exists( '\Two_Factor_Core' ) ) {
			$this->bail_to_login();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time token is the authenticated reference.
		$token = isset( $_GET['tf'] ) ? sanitize_text_field( wp_unslash( $_GET['tf'] ) ) : '';
		if ( empty( $token ) ) {
			$this->bail_to_login();
		}

		$handoff = $this->get_handoff( $token );
		if ( ! $handoff ) {
			$this->bail_to_login(); // No/expired hand-off: send them back to log in.
		}

		// Single-use; the rendered form carries the nonce + redirect forward.
		$this->delete_handoff( $token );

		$user = get_user_by( 'id', $handoff['user_id'] );
		if ( ! $user || ! \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			$this->bail_to_login();
		}

		$this->output_challenge_form( $user, $handoff['nonce'], $handoff['redirect_to'] );
	}

	/**
	 * Validates the submitted code using Two Factor's own validator, inside
	 * admin-ajax. On success it sets the auth cookie and redirects; on a bad
	 * code it re-renders the form (target rewritten back to this endpoint).
	 *
	 * @return void
	 */
	public function validate_challenge() {

		if ( ! class_exists( '\Two_Factor_Core' ) ) {
			$this->bail_to_login();
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Two Factor verifies the login nonce below.
		$uid         = isset( $_REQUEST['wp-auth-id'] ) ? (int) $_REQUEST['wp-auth-id'] : 0;
		$nonce       = isset( $_REQUEST['wp-auth-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wp-auth-nonce'] ) ) : '';
		$provider    = isset( $_REQUEST['provider'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['provider'] ) ) : '';
		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url();
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$user = $uid ? get_user_by( 'id', $uid ) : false;
		if ( ! $user ) {
			$this->bail_to_login();
		}

		// Two Factor re-renders its own retry form on a bad code; stop the
		// toolkit's wp_login_failed redirect from bouncing the user out.
		remove_action( 'wp_login_failed', array( 'uncanny_learndash_toolkit\FrontendLoginPlus', 'login_failed' ) );

		$is_post = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) );

		// On success this sets the auth cookie, redirects and exits. On failure
		// it re-renders the challenge form (which we capture and re-target).
		ob_start();
		\Two_Factor_Core::_login_form_validate_2fa( $user, $nonce, $provider, $redirect_to, $is_post );
		$html = ob_get_clean();

		echo $this->retarget_form( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Two Factor output, already escaped.
		exit;
	}

	/**
	 * Outputs Two Factor's challenge form with the POST target re-pointed at our
	 * admin-ajax validation endpoint.
	 *
	 * @param \WP_User $user        The user.
	 * @param string   $nonce       The login nonce.
	 * @param string   $redirect_to The post-login redirect.
	 * @return void
	 */
	private function output_challenge_form( $user, $nonce, $redirect_to ) {

		nocache_headers();
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

		ob_start();
		\Two_Factor_Core::login_html( $user, $nonce, $redirect_to );
		$html = ob_get_clean();

		echo $this->retarget_form( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Two Factor output, already escaped.
		exit;
	}

	/**
	 * Rewrites Two Factor's wp-login.php validate_2fa targets to our admin-ajax
	 * validation endpoint.
	 *
	 * @param string $html The rendered Two Factor HTML.
	 * @return string The re-targeted HTML.
	 */
	private function retarget_form( $html ) {

		$ours = add_query_arg( 'action', self::VALIDATE_ACTION, admin_url( 'admin-ajax.php' ) );

		// The main form uses the 'login_post' scheme; backup-method links use 'login'.
		foreach ( array( 'login_post', 'login' ) as $scheme ) {
			$native = \Two_Factor_Core::login_url( array( 'action' => 'validate_2fa' ), $scheme );
			$html   = str_replace( esc_url( $native ), esc_url( $ours ), $html );
		}

		return $html;
	}

	/**
	 * Redirects to the login page (used when the hand-off is missing or invalid).
	 *
	 * @return void
	 */
	private function bail_to_login() {
		wp_safe_redirect( $this->get_login_page_url() );
		exit;
	}

	/**
	 * Resolves the user from the submitted credentials.
	 *
	 * @return \WP_User|false The user, or false if not found.
	 */
	private function get_attempted_user() {

		$username = '';
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Runs after authentication; only identifies the user.
		if ( isset( $_POST['email'] ) ) {
			$username = sanitize_user( wp_unslash( $_POST['email'] ) );
		} elseif ( isset( $_POST['log'] ) ) {
			$username = sanitize_user( wp_unslash( $_POST['log'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( empty( $username ) ) {
			return false;
		}

		$user = get_user_by( 'login', $username );
		if ( ! $user && false !== strpos( $username, '@' ) ) {
			$user = get_user_by( 'email', $username );
		}

		return $user ? $user : false;
	}

	/**
	 * Returns the toolkit login page URL.
	 *
	 * @return string The login page URL.
	 */
	private function get_login_page_url() {

		$page_id = (int) FrontendLoginPlus::get_login_redirect_page_id();
		$url     = $page_id > 0 ? get_permalink( $page_id ) : '';

		return empty( $url ) ? home_url( '/' ) : $url;
	}

	/**
	 * Stores the hand-off payload in a transient and returns its one-time token.
	 *
	 * @param int    $user_id     The user ID.
	 * @param string $nonce       The Two Factor login nonce key.
	 * @param string $redirect_to The post-login redirect URL.
	 * @return string|false The token, or false on failure.
	 */
	private function store_handoff( $user_id, $nonce, $redirect_to ) {

		$token = wp_generate_password( 40, false );

		$stored = set_transient(
			self::TRANSIENT_PREFIX . $token,
			array(
				'user_id'     => (int) $user_id,
				'nonce'       => $nonce,
				'redirect_to' => $redirect_to,
			),
			self::CHALLENGE_EXPIRY
		);

		return $stored ? $token : false;
	}

	/**
	 * Reads and validates the hand-off payload for a token.
	 *
	 * @param string $token The one-time token.
	 * @return array|false Hand-off data, or false if invalid.
	 */
	private function get_handoff( $token ) {

		$data = get_transient( self::TRANSIENT_PREFIX . $token );

		if ( ! is_array( $data ) || empty( $data['user_id'] ) || empty( $data['nonce'] ) ) {
			return false;
		}

		return array(
			'user_id'     => (int) $data['user_id'],
			'nonce'       => (string) $data['nonce'],
			'redirect_to' => isset( $data['redirect_to'] ) ? $data['redirect_to'] : admin_url(),
		);
	}

	/**
	 * Deletes the hand-off transient.
	 *
	 * @param string $token The one-time token.
	 * @return void
	 */
	private function delete_handoff( $token ) {
		delete_transient( self::TRANSIENT_PREFIX . $token );
	}
}
