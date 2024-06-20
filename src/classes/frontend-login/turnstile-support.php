<?php
namespace UncannyOwl\Toolkit\FrontendLogin;

use Error;
use uncanny_learndash_toolkit\Config;
use uncanny_learndash_toolkit\FrontendLoginPlus as Frontend_Login_Plus;

use Exception;
use WP_Error;

/**
 * Turnstile Support for Uncanny Toolkit Frontend Login.
 *
 * @package UncannyOwl\Toolkit\FrontendLogin
 */
final class Turnstile_Support {

	/**
	 * Register the hooks required for Turnstile support.
	 *
	 * @return true
	 */
	public static function register_hooks() {

		/**
		 * ! Login form related action hooks and filters.
		 */
		// Renders the recaptcha in the login form.
		add_filter( 'login_form_middle', array( self::class, 'login_form_recaptcha' ), 20, 2 );
		// Verifies the token for ajax login form.
		add_action( 'uo_toolkit_frontend_login_user_verified_before_signon', array( self::class, 'verify_token' ), 10, 1 );
		// Verifies the token for non ajax login form.
		add_filter( 'authenticate', array( self::class, 'non_ajax_login_verify_token' ), 99, 3 );
		// Adds support for turnstile specific error messages.
		add_filter( 'uo_toolkit_frontend_login_error_messages', array( self::class, 'non_ajax_recaptcha_error_message' ), 10, 2 );

		/**
		 * ! Lost password form related action hooks and filters.
		 */
		// Renders the recaptcha in the lost password form.
		add_filter( 'uo_forgot_before_submit', array( self::class, 'lost_password_form_recaptcha' ), 20, 1 );
		// Verifies the token for ajax lost password form.
		add_action( 'uo_toolkit_frontend_login_lost_password_before', array( self::class, 'verify_token' ), 20, 1 );
		// Verifies the token for non ajax lost password form.
		add_action( 'lostpassword_post', array( self::class, 'non_ajax_lost_password_verify_token' ), 20, 1 );
		// Adds support for turnstile specific error messages for lost password form.
		add_filter( 'uo_toolkit_reset_password_errors', array( self::class, 'lost_password_recaptcha_errors' ), 10, 1 );

		// ! Recaptcha form data support.
		add_filter( 'uo_toolkit_frontend_login_form_data', array( self::class, 'insert_recaptcha_token' ), 10, 1 );

		// ! Disables Google recaptcha if Cloudflare turnstile is active.
		add_action( 'uo_toolkit_settings_config_value', array( self::class, 'disable_google_recatpcha' ), 10, 3 );

		return true;

	}

	/**
	 * Disables Google Recaptcha if Turnstile is active.
	 *
	 * @param string $value
	 * @param string $key
	 * @param string[] $args
	 *
	 * @return string
	 */
	public static function disable_google_recatpcha( $value, $key, $args ) {

		$google_recatpcha_settings_fields = array(
			'uo_frontend_login_recaptcha_key',
			'uo_frontend_login_recaptcha_secret_key',
		);

		if ( in_array( $key, $google_recatpcha_settings_fields, true ) ) {
			// Only check after making sure this runs on this specific config key only to prevent infinite loop.
			if ( self::turnstile_is_active() ) {
				return ''; // Disables Google recaptcha if turnstile is active.
			}
			return $value;
		}

		return $value;

	}

	/**
	 * Verifies Turnstile token.
	 *
	 * @param mixed $response
	 * @return bool|void - Returns false if Turnstile is not active. True if token has been successfully validated. Otherwise sends a JSON response if doing wp_ajax.
	 *
	 * @throws Error When validation of token fails and is not doing wp_ajax request.
	 */
	public static function verify_token( $response = '' ) {

		// Bail if turnstile is not active.
		if ( ! self::turnstile_is_active() ) {
			return false; // Just return false if turnstile is not active. Since this is an action, it does not affect execution.
		}

		$turnstile_token = filter_input( INPUT_POST, 'turnstileRecaptcha' );

		try {

			// Validate the token.
			self::validate_token( $turnstile_token );
			return true;

		} catch ( Exception $e ) {

			if ( ! wp_doing_ajax() ) {
				throw new Error( $e->getMessage(), $e->getCode() );
			}

			Frontend_Login_Plus::wp_send_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				),
				200 // Send 200 error for now since toolkit considers other HTTP headers to be 'invalid'.
			);
		}

	}
	/**
	 * Support for non-ajax recaptcha error message in login form.
	 *
	 * This method adds a support for error message from turnstile recaptcha.
	 *
	 * @param mixed $error
	 * @param mixed $key
	 * @return mixed
	 */
	public static function non_ajax_recaptcha_error_message( $error, $key ) {

		if ( 'turnstile_recaptcha_error' === $key ) {
			$error['warning'] = self::get_error_message();
		}

		return $error;

	}

	/**
	 * Support for non-ajax recaptch error in login form.
	 *
	 * @param mixed $user
	 * @param mixed $username
	 * @param mixed $password
	 * @return mixed
	 */
	public static function non_ajax_login_verify_token( $user, $username, $password ) {

		if ( ! self::turnstile_is_active() ) {
			return $user;
		}

		$turnstile_token = filter_input( INPUT_POST, 'cf-turnstile-response' );

		// Disable for ajax requests.
		if ( wp_doing_ajax() ) {
			return $user;
		}

		try {
			self::validate_token( $turnstile_token );
			return $user;
		} catch ( Exception $e ) {
			// We need to redirect here since toolkit depends on 'login' query parameter to display various messages.
			$url = self::construct_redirect_url( wp_get_referer(), 'turnstile_recaptcha_error', 'login' );
			wp_safe_redirect( $url, 302 );
			exit;
		}

	}

	/**
	 * Support for non-ajax lost password form.
	 *
	 * @param string $errors
	 *
	 * @return void
	 */
	public static function non_ajax_lost_password_verify_token( $errors ) {

		$turnstile_token = filter_input( INPUT_POST, 'cf-turnstile-response' );

		// Bail if turnstile is not active.
		if ( ! self::turnstile_is_active() ) {
			return;
		}

		// Disable for ajax requests.
		if ( wp_doing_ajax() ) {
			return;
		}

		try {
			self::validate_token( $turnstile_token );
			return;
		} catch ( Exception $e ) {
			// We need to redirect here since toolkit depends on 'success' query parameter to display various messages.
			$url = self::construct_redirect_url( wp_get_referer(), 'turnstile_recaptcha_error', 'success' );
			wp_safe_redirect( $url, 302 );
			exit;
		}

	}

	/**
	 * Constructs redirect URL for handling error redirection.
	 *
	 * @param string $url The URL.
	 * @param string $login_query_param_value The value for param#3.
	 * @param string $key The query parameter key.
	 *
	 * @return string
	 */
	private static function construct_redirect_url( $url = '', $login_query_param_value = '', $key = '' ) {

		// Parse the URL
		$url_components = wp_parse_url( $url );

		// Parse the query string into an array
		parse_str( $url_components['query'], $query_params );

		// Modify the value of the 'login' parameter
		$query_params[ $key ] = $login_query_param_value;

		// Rebuild the query string from the modified array
		$new_query_string = http_build_query( $query_params );

		// Reconstruct the URL with the modified query string
		return $url_components['scheme'] . '://' . $url_components['host'] . $url_components['path'] . '?' . $new_query_string;

	}

	/**
	 * Validates token from turnstile endpoint.
	 *
	 * @param string $token
	 * @return true
	 *
	 * @throws Exception
	 */
	public static function validate_token( $token ) {

		$token = apply_filters( 'uo_toolkit_frontend_login_turnstile_validate_token_value', $token );

		if ( empty( $token ) || 'false' === $token ) { // Token is getting cast as string.
			throw new Exception( self::get_error_message_unchecked(), 400 );
		}

		$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

		// Data to be sent
		$data = array(
			'secret'   => self::get_site_secret(),
			'response' => $token,
		);

		// Send POST request
		$response = wp_remote_post(
			$url,
			array(
				'body' => $data,
			)
		);

		// Check for errors
		if ( is_wp_error( $response ) ) {
			// Throw 422 errors for WordPress specific errors.
			throw new Exception( $response->get_error_message(), 422 );
		}

		// Success.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $data['success'] ) {
			return true;
		}

		// Throw 400 error message if nothing else.
		throw new Exception( self::get_error_message(), 400 );

	}

	/**
	 * Inserts the recaptcha token on form data.
	 *
	 * @param mixed[] $form_data
	 *
	 * @return mixed[]
	 */
	public static function insert_recaptcha_token( $form_data ) {
		// Initialize turnstile recaptch as false.
		$form_data['turnstileRecaptcha'] = false;
		return $form_data;
	}

	/**
	 * Callback function to 'login_form_middle'.
	 *
	 * @param string $content
	 * @param mixed[] $args
	 *
	 * @return string
	 */
	public static function login_form_recaptcha( $content = '', $args = array() ) {

		if ( ! self::turnstile_is_active() ) {
			return $content;
		}

		return self::recaptcha_element( $content );

	}

	/**
	 * Renders the recaptcha.
	 *
	 * @return void
	 */
	public static function lost_password_form_recaptcha() {

		if ( ! self::turnstile_is_active() ) {
			return;
		}

		// Dont escape.
		echo self::recaptcha_element(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Supports turnstile recaptcha error messages.
	 *
	 * @param string $error
	 *
	 * @return string
	 */
	public static function lost_password_recaptcha_errors( $error ) {

		$is_lost_password_form = isset( $_GET['action'] ) && 'forgot' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_recaptcha_error    = isset( $_GET['success'] ) && 'turnstile_recaptcha_error' === $_GET['success']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $is_lost_password_form && $is_recaptcha_error ) {
			return self::get_error_message();
		}

		return $error;

	}

	/**
	 * Returns the recaptcha client side code and appending any supplied parameter.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function recaptcha_element( $content = '' ) {

		// Output buffer since we're including a file template and appending the existing content to it before returning.
		ob_start();

		$params = array(
			'site_key'                => self::get_site_key(),
			'turnstile_response_null' => self::get_render_error_message(),
		);

		include Frontend_Login_Plus::get_template( '/frontend-login/cloudflare-turnstile/recaptcha-frontend.php' );

		return ob_get_clean() . $content;
	}

	/**
	 * Retrieve turnstile key.
	 *
	 * @return string
	 */
	public static function get_site_key() {

		$key = Config::get_settings_value( 'uo_frontend_login_turnstile_recaptcha_key', 'FrontendLoginPlus' );

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_key', $key );
	}

	/**
	 * Retrieve turnstile secret key.
	 *
	 * @return string
	 */
	public static function get_site_secret() {

		$secret = Config::get_settings_value( 'uo_frontend_login_turnstile_recaptcha_secret', 'FrontendLoginPlus' );

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_secret', $secret );

	}

	/**
	 * Retrieve the turnstile error message settings.
	 *
	 * @return string
	 */
	public static function get_error_message() {

		$error_message = Config::get_settings_value( 'uo_frontend_login_turnstile_error_message', 'FrontendLoginPlus' );

		if ( empty( $error_message ) ) {
			$error_message = __( 'There was an error validating the form . Please contact the site administrator.', 'FrontendLoginPlus' );
		}

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_error_message', $error_message );

	}

	/**
	 * Retrieve the turnstile error message settings.
	 *
	 * @return string
	 */
	public static function get_render_error_message() {

		$error_message = Config::get_settings_value( 'uo_frontend_login_turnstile_error_message', 'FrontendLoginPlus' );

		if ( empty( $error_message ) ) {
			$error_message = __( 'An error has occurred while displaying reCaptcha. Please contact the site administrator.', 'FrontendLoginPlus' );
		}

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_render_error_message', $error_message );

	}

	/**
	 * Retrieve the turnstile error message settings.
	 *
	 * @return string
	 */
	public static function get_error_message_unchecked() {

		$error_message = Config::get_settings_value( 'uo_frontend_login_turnstile_recaptcha_empty_error', 'FrontendLoginPlus' );

		if ( empty( $error_message ) ) {
			$error_message = __( 'Please verify that you are not a robot.', 'FrontendLoginPlus' );
		}

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_error_message_unchecked', $error_message );

	}

	/**
	 * Determines whether Turnstile is active or not. To make Turnstile active, the site key and secret must be filled-in.
	 *
	 * @return bool
	 */
	public static function turnstile_is_active() {

		$is_active = ! empty( self::get_site_key() ) && ! empty( self::get_site_secret() );

		return apply_filters( 'uo_toolkit_frontend_login_turnstile_is_active', $is_active );

	}
}
