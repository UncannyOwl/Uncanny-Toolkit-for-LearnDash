<?php
namespace uncanny_learndash_toolkit;

use WP2FA\Authenticator\Login as Two_Factor_Core;
use WP2FA\Authenticator\BackupCodes as BackupCodes;
use WP2FA\Admin\SettingsPage;

/**
 * This class handles the integration between Uncanny Front-end Login and Two-Factor.
 *
 * @category Frontend Login 2 Factor
 * @package  UncannyToolkit
 * @since    3.5
 */
class Frontend_Login_Plus_2fa {


	/**
	 * The login page.
	 *
	 * @var $login_uri The url of the login page selected in the settings module.
	 */
	private $login_uri = '';


	/**
	 * The two factor instance.
	 *
	 * @var $two_factor The instance of \Two_Factor_Core.
	 */
	private $two_factor = '';

	private $two_factor_class_name = '';

	/**
	 * Our class construct. Set-up Two-factor object and register the actions.
	 *
	 * @param Two_Factor_Core $two_factor The instance of Two-Factor core.
	 *
	 * @return boolean True. Always.
	 */
	public function __construct( Two_Factor_Core $two_factor ) {

		// Two-factor object.
		$this->two_factor = $two_factor;

		$this->two_factor_class_name = get_class( $this->two_factor );

		// Register the actions.
		$this->register_actions();

		return true;

	}

	/**
	 * Setups our actions once.
	 *
	 * @return boolean True. Always.
	 */
	public function register_actions() {

		if ( ! did_action( 'uo_toolkit_two_factor_started' ) ) {
			// Set-up the action hooks.
			add_action( 'init', array( $this, 'setup' ) );
			do_action( 'uo_toolkit_two_factor_started' );
		}

		return true;

	}

	/**
	 * Checks if the login module is enabled.
	 *
	 * @return boolean True if front-end login module is enabled. Otherwise, false.
	 */
	public function frontend_login_enabled() {

		return array_key_exists(
			'uncanny_learndash_toolkit\FrontendLoginPlus',
			Config::get_active_classes()
		);

	}

	/**
	 * Replaces default Two-Factor actions with our own.
	 *
	 * @return boolean True. Always.
	 */
	public function setup() {

		// Check to see if front-end login is enabled.
		// Must have a login page for our integration to work.
		$login_page = FrontendLoginPlus::get_settings_value( 'login_page', 'FrontendLoginPlus', '' );

		if ( $this->frontend_login_enabled() && ! empty( $login_page ) ) {

			if ( ! class_exists('\WP2FA\WP2FA') || ! method_exists( '\WP2FA\WP2FA', 'get_instance')) {
				return;
			}

			$this->login_uri = get_permalink( FrontendLoginPlus::get_login_redirect_page_id() );

			$wp2fa = \WP2FA\WP2FA::get_instance();

			remove_action( 'wp_login', array( $wp2fa->login, 'wp_login' ), 20, 2 );
			remove_action( 'login_form_validate_2fa', array( $wp2fa->login, 'login_form_validate_2fa' ) );

			if ( ! wp_doing_ajax() ) {

				add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
				add_action( 'login_form_validate_2fa', array( $this, 'login_form_validate_2fa' ), 10, 2 );
			}

			add_filter( 'uo-login-action-response', array( $this, 'uo_login_action_response' ), 99, 1 );
			add_action( 'uo-login-action-before-json-response', array( $this, 'uo_login_action_before_json_response' ), 99, 1 );

			add_filter( 'uo-redirect-login-page', array( $this, 'uo_redirect_login_page' ), 99, 1 );

			add_action( 'wp_logout', array( $this, 'uo_clear_cookie_logout' ) );

		}

		return true;

	}

	/**
	 * The callback method to `uo-redirect-login-page`.
	 *
	 * @param string $login_uri The login url.
	 *
	 * @return string the url of the login page.
	 */
	public function uo_redirect_login_page( $login_uri ) {

		return $this->login_uri;

	}

	/**
	 * The callback method to `uo-login-action-before-json-response`.
	 *
	 * Destroys the user session on successful authentication via http request.
	 *
	 * @return boolean True.
	 */
	public function uo_login_action_before_json_response( $user ) {

		// Skip if user has not enabled 2factor in his/her profile.
		if ( ! $this->two_factor::is_user_using_two_factor( $user->ID ) ) {
			return;
		}

		// Invalidate the current login session to prevent from being re-used.
		$this->two_factor::destroy_current_session_for_user( $user );

		// Also clear the cookies which are no longer valid.
		wp_clear_auth_cookie();

		return true;

	}

	/**
	 * Callback method to `uo-login-action-response`.
	 *
	 * Modifies the ajax redirect so it goes into authentication form instead of throwing an error.
	 *
	 * @return array $response The response key value.
	 */
	public function uo_login_action_response( $response ) {

		$user_identity = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );

		// Do check by login.
		$user = get_user_by( 'login', $user_identity );

		if ( ! $this->two_factor::is_user_using_two_factor( $user->ID ) ) {
			return $response;
		}

		// If there is no user with that login. Try email.
		if ( ! $user && strpos( $user_identity, '@' ) ) {
			$user = get_user_by( 'email', $user_identity );
		}

		$nonce = wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user->ID ) );

		$this->set_form_cookie( wp_hash_password( $nonce ) );

		// Invalidate the current login session to prevent from being re-used.
		$this->two_factor::destroy_current_session_for_user( $user );

		wp_clear_auth_cookie();

		$redirect_from_ajax = filter_input( INPUT_POST, 'redirectTo', FILTER_SANITIZE_STRING );

		$http_args = array(
			'user'               => $user->ID,
			'2fa_authentication' => 1,
			'rememberme'         => isset( $_REQUEST['rememberMe'] ) ? $_REQUEST['rememberMe'] : '',
			'_wpnonce'           => $nonce,
			'ukey'               => uniqid(),
			'redirect_to'        => ! empty( $redirect_from_ajax ) ? $redirect_from_ajax : $this->get_redirect_to(),
		);

		$two_factor_login_form_url = add_query_arg(
			$http_args,
			$this->login_uri
		);

		$response['redirectTo'] = $two_factor_login_form_url;

		return $response;

	}

	/**
	 * Callback method to `login_form_validate_2fa`.
	 *
	 * @return mixed Redirects and exits upon valid transaction. Otherwise, returns void.
	 */
	public function login_form_validate_2fa() {

		// The nonces are processed via `verify_login_nonce`.
		if ( ! isset( $_POST['wp-auth-id'], $_POST['wp-auth-nonce'] ) ) { //phpcs:ignore
			return;
		}

		$auth_id = (int) $_POST['wp-auth-id']; //phpcs:ignore
		$user    = get_userdata( $auth_id );

		// Destroy the cookie.
		$this->delete_form_cookie();

		// Set the form cookie verification.
		$cookie_nonce = wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user->ID ) );
		$this->set_form_cookie( wp_hash_password( $cookie_nonce ) );

		if ( ! $user ) {
			return;
		}

		$nonce = ( isset( $_POST['wp-auth-nonce'] ) ) ? sanitize_textarea_field( wp_unslash( $_POST['wp-auth-nonce'] ) ) : ''; //phpcs:ignore

		if ( true !== $this->two_factor::verify_login_nonce( $user->ID, $nonce ) ) {

			$redirect_args = array(
				'user'               => $user->ID,
				'2fa_authentication' => 1,
				'rememberme'         => isset( $_REQUEST['rememberme'] ) ? $_REQUEST['rememberme'] : '',
				'error'              => '2fa-invalid-user-keys',
				'ukey'               => uniqid(),
				'redirect_to'        => $this->get_redirect_to(),
			);

			$this->delete_form_cookie();

			wp_safe_redirect(
				add_query_arg(
					$redirect_args,
					$this->login_uri
				)
			);

			exit;

		}

		if ( isset( $_POST['provider'] ) ) { //phpcs:ignore
			$provider  = sanitize_textarea_field( wp_unslash( $_POST['provider'] ) ); //phpcs:ignore
			$providers = $this->two_factor::get_available_providers_for_user( $user );
			if ( isset( $providers[ $provider ] ) ) {
				$provider = $providers[ $provider ];
			} elseif ( isset( $provider ) ) {
				$provider = $provider;
			} else {
				$provider = $provider;
			}
		}

		$redirect_args = array(
			'user'               => $user->ID,
			'ukey'               => uniqid(),
			'2fa_authentication' => 1,
			'rememberme'         => isset( $_REQUEST['rememberme'] ) ? $_REQUEST['rememberme'] : '',
			'error'              => '2fa-incorrect',
			'provider'           => $provider,
			'_wpnonce'           => wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user->ID ) ),
			'redirect_to'        => $this->get_redirect_to(),
		);

		// If this is an email login, or if the user failed validation previously, lets send the code to the user.
		if ( 'email' === $provider && true !== $this->two_factor::pre_process_email_authentication( $user ) ) {
			$login_nonce = $this->two_factor::create_login_nonce( $user->ID );
			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'uncanny-learndash-toolkit' ) );
			}
		}

		// Validate TOTP.
		if ( 'totp' === $provider && true !== $this->two_factor::validate_totp_authentication( $user ) ) {

			$login_nonce = $this->two_factor::create_login_nonce( $user->ID );

			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'uncanny-learndash-toolkit' ) );
			}

			$redirect_args['w2-2fa-key'] = $login_nonce['key'];

			wp_safe_redirect(
				add_query_arg(
					$redirect_args,
					$this->login_uri
				)
			);

			exit;
		}

		// Backup Codes.
		if ( 'backup_codes' === $provider && true !== $this->two_factor::validate_backup_codes( $user ) ) {

			$login_nonce = $this->two_factor::create_login_nonce( $user->ID );

			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'uncanny-learndash-toolkit' ) );
			}

			wp_safe_redirect(
				add_query_arg(
					$redirect_args,
					$this->login_uri
				)
			);

			exit;
		}

		// Validate Email.
		if ( 'email' === $provider && true !== $this->two_factor::validate_email_authentication( $user ) ) {

			$login_nonce = $this->two_factor::create_login_nonce( $user->ID );

			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'uncanny-learndash-toolkit' ) );
			}

			if ( isset( $_REQUEST['wp-2fa-email-code-resend'] ) ) { //phpcs:ignore
				unset( $redirect_args['error'] );
				$redirect_args['w2-2fa-key'] = $login_nonce['key'];
				$redirect_args['2fa-action'] = 'wp-2fa-email-code-resend';
				$redirect_args['error']      = esc_html__( 'A new code has been sent.', 'uncanny-learndash-toolkit' );
			}

			wp_safe_redirect(
				add_query_arg(
					$redirect_args,
					$this->login_uri
				)
			);

			exit;
		}

		$this->two_factor::delete_login_nonce( $user->ID );

		$rememberme = false;
		$remember   = ( isset( $_REQUEST['rememberme'] ) ) ? filter_var( $_REQUEST['rememberme'], FILTER_VALIDATE_BOOLEAN ) : ''; //phpcs:ignore
		if ( ! empty( $remember ) ) {
			$rememberme = true;
		}

		wp_set_auth_cookie( $user->ID, $rememberme );

		do_action( 'two_factor_user_authenticated', $user );

		// Check if user has any roles/caps set - if they dont, we know its a "network" user.
		if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && empty( $user->caps ) && empty( $user->caps ) ) {
			$redirect_to = user_admin_url();
		} else {
			$redirect_to = apply_filters( 'login_redirect', esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ), esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ), $user ); //phpcs:ignore
		}

		// Catch empty redirect url.
		if ( empty( $redirect_to ) ) {
			$redirect_to = apply_filters( 'login_redirect', admin_url(), admin_url(), $user );
		}

		wp_safe_redirect( $redirect_to );

		exit;

	}

	/**
	 * Callback method to `wp_login`.
	 *
	 * @param string $user_login The user login.
	 * @param object $user       The user object on successful authentication.
	 *
	 * @return mixed Returns void if use is not using any Two-Factor method. Otherwsie, redirect and exits (die).
	 */
	public function wp_login( $user_login, $user ) {

		if ( ! $this->two_factor::is_user_using_two_factor( $user->ID ) ) {
			return;
		}

		$nonce = wp_create_nonce( sprintf( 'uo-toolkit-2fa-user-%d-authentication', $user->ID ) );

		// Set the form cookie verification.
		$this->set_form_cookie( wp_hash_password( $nonce ) );

		// Invalidate the current login session to prevent from being re-used.
		$this->two_factor::destroy_current_session_for_user( $user );

		wp_clear_auth_cookie();

		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : ''; // phpcs:ignore

		$redirect = add_query_arg(
			array(
				'user'               => $user->ID,
				'2fa_authentication' => 2,
				'rememberme'         => isset( $_REQUEST['rememberme'] ) ? $_REQUEST['rememberme'] : '',
				'_wpnonce'           => $nonce,
				'ukey'               => uniqid(),
				'redirect_to'        => $redirect_to,
			),
			$this->login_uri
		);

		wp_safe_redirect( $redirect );

		die;

	}

	/**
	 * Displays the two factor authentication form to the user.
	 *
	 * @param object $user The WordPress' user object
	 *
	 * @return void
	 */
	public function show_two_factor_login( $user ) {

		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		$error_message = '';
		$login_nonce   = $this->two_factor::create_login_nonce( $user->ID );

		if ( ! $login_nonce ) {
			?>
			<div class="ult-notice ult-notice--error two-factor-error-notice">
				<span class="ult-notice-text">
					<strong>
						<?php esc_html_e( 'ERROR: Failed to create a login nonce.', 'uncanny-learndash-toolkit' ); ?>
					</strong>
				</span>
			</div>
			<?php
			return;
		}

		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : ''; // phpcs:ignore

		$wp_auth_id = filter_input( INPUT_GET, 'wp-auth-id', FILTER_SANITIZE_NUMBER_INT );
		$nonce      = filter_input( INPUT_GET, 'wp-auth-nonce', FILTER_SANITIZE_STRING );
		$provider   = filter_input( INPUT_GET, 'provider', FILTER_SANITIZE_STRING );
		$error      = filter_input( INPUT_GET, 'error', FILTER_SANITIZE_STRING );

		if ( ! empty( $error ) && '2fa-incorrect' === $error ) {
			$error_message = esc_html__( 'ERROR: Invalid verification code.', 'uncanny-learndash-toolkit' );
		}

		$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		$form_cookie = filter_input( INPUT_COOKIE, 'uo-toolkit-guard', FILTER_SANITIZE_STRING );

		// Verify the cookie.
		if ( $this->verify_cookie( $nonce, $form_cookie ) ) {
			$this->login_html( $user, $login_nonce['key'], $redirect_to, $error_message, $provider );
		} else {
			?>
			<div class="ult-notice ult-notice--error two-factor-error-notice">
				<span class="ult-notice-text">
					<strong>
						<?php $error_type = filter_input( INPUT_GET, 'error', FILTER_SANITIZE_STRING ); ?>
						<?php if ( '2fa-invalid-user-keys' === $error_type ) : ?>
							<?php esc_html_e( 'ERROR: Session is invalid or has expired.', 'uncanny-learndash-toolkit' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'ERROR: Cannot display authentication form.', 'uncanny-learndash-toolkit' ); ?>
						<?php endif; ?>
					</strong>
				</span>
			</div>
			<div class="uo-toolkit-2fa-footer">
				<a href="<?php echo esc_url( wp_login_url() ); ?>" 
					title="<?php esc_attr_e( 'Return to login form', 'uncanny-learndash-toolkit' ); ?>">
					&larr;
					<?php esc_attr_e( 'Go back to login form', 'uncanny-learndash-toolkit' ); ?>
				</a>    
			</div>

			<?php
		}

	}

	/**
	 * Renders the two factor login form.
	 *
	 * @return void
	 */
	public function login_form() {

		$user_id    = filter_input( INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT );
		$wp_auth_id = filter_input( INPUT_GET, 'wp-auth-id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! empty( $wp_auth_id ) ) {
			$user_id = $wp_auth_id;
		}

		$user = get_user_by( 'ID', $user_id ); // @Todo: Change the `1` to something secure and dynamic.

		// Show the 2factor login for the user.
		$this->show_two_factor_login( $user );

	}

	/**
	 * Renders the 2 factor login form html.
	 *
	 * @param object $user The WordPress user object.
	 * @param string $login_nonce The constructed login nonce.
	 * @param string $redirect_to Optional redirect params.
	 * @param string $error_msg The optional error message.
	 * @param string $provider The provider.
	 *
	 * @return void
	 */
	protected function login_html( $user, $login_nonce, $redirect_to, $error_msg = '', $provider = null ) {

		if ( ! $provider || ( 'backup_codes' === $provider && ! SettingsPage::are_backup_codes_enabled( $user->roles[0] ) ) ) {
			$provider = $this->two_factor::get_available_providers_for_user( $user );
		}

		$codes_remaining = BackupCodes::codes_remaining_for_user( $user );
		$interim_login = ( isset( $_REQUEST['interim-login'] ) ) ? filter_var( wp_unslash( $_REQUEST['interim-login'] ), FILTER_VALIDATE_BOOLEAN ) : false; //phpcs:ignore
		$rememberme      = intval( $this->two_factor::rememberme() );

		if ( ! empty( $error_msg ) ) {
			?>
			<div class="ult-notice ult-notice--error two-factor-error-notice">
				<span class="ult-notice-text">
					<strong>
						<?php echo esc_html( $error_msg ); ?>
					</strong>    
				</span>
			</div>
			<?php
		}

		?>
		<style>form[name='validate_2fa_form'] br {display: none; }</style>

		<form name="validate_2fa_form" 
			id="<?php echo esc_attr( uo_toolkit_2fa_get_form_id() ); ?>" 
			action="<?php echo esc_url( $this->two_factor::login_url( array( 'action' => 'validate_2fa' ), 'login_post' ) ); ?>" 
			method="post" autocomplete="off" >

			<input type="hidden" name="provider"      id="provider"      value="<?php echo esc_attr( $provider ); ?>" />
			<input type="hidden" name="wp-auth-id"    id="wp-auth-id"    value="<?php echo esc_attr( $user->ID ); ?>" />
			<input type="hidden" name="wp-auth-nonce" id="wp-auth-nonce" value="<?php echo esc_attr( $login_nonce ); ?>" />

			<?php if ( $interim_login ) : ?>
				<input type="hidden" name="interim-login" value="1" />
			<?php else : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
			<?php endif; ?>

			<input type="hidden" name="rememberme" id="rememberme" value="<?php echo esc_attr( $rememberme ); ?>" />

			<?php $resend_email = filter_input( INPUT_GET, '2fa-action', FILTER_SANITIZE_STRING ); ?>

			<?php if ( ! empty( $resend_email ) ) : ?>
				<div class="ult-notice ult-notice--success" style="margin-top: 20px;">
					<span class="ult-notice-text">
						<strong>
							<?php esc_html_e( 'A new verification code has been sent to your email.', 'uncanny-learndash-toolkit' ); ?>
						</strong>    
					</span>
				</div>
			<?php endif; ?>

			<?php
			// Check to see what provider is set and give the relevant authentication page.
			if ( 'totp' === $provider ) {
				$this->two_factor::totp_authentication_page( $user );
			} elseif ( 'email' === $provider ) {
				$this->two_factor::email_authentication_page( $user );
			} elseif ( 'backup_codes' === $provider ) {
				$this->two_factor::backup_codes_authentication_page( $user );
			} else {
				do_action( 'wp_2fa_login_form', $user, $provider );
			}
			?>

			<?php $submit_button_disabled = apply_filters( 'wp_2fa_login_disable_submit_button', false, $user, $provider ); ?>

			<?php if ( ! $submit_button_disabled ) : ?>
				<?php do_action( 'wp_2fa_login_before_submit_button', $user, $provider ); ?>
				<p>
					<?php
					if ( function_exists( 'submit_button' ) ) {
						$button_text = apply_filters( 'wp_2fa_login_button_text', esc_html__( 'Log In', 'wp-2fa' ) );
						submit_button( $button_text );
						?>
						<script type="text/javascript">
							setTimeout(function () {
								var d
								try {
									d = document.getElementById('authcode')
									d.value = ''
									d.focus()
								} catch (e) {}
							}, 200)
						</script>
					<?php } ?>
				</p>

				<?php
				if ( 'email' === $provider ) :
					?>
					<p class="2fa-email-resend">
						<input type="submit" class="button"
							name="<?php echo esc_attr( $this->two_factor::INPUT_NAME_RESEND_CODE ); ?>"
							value="<?php esc_attr_e( 'Resend Code', 'wp-2fa' ); ?>"/>
					</p>
					<?php
				endif;
			endif; // submit button not disabled

			do_action( 'wp_2fa_login_html_before_end', $user, $provider );
			?>

		</form>

		<div class="uo-toolkit-2fa-footer">

		<?php

		if ( 'backup_codes' !== $provider && SettingsPage::are_backup_codes_enabled( $user->roles[0] ) && isset( $codes_remaining ) && $codes_remaining > 0 ) :

			$login_url = $this->two_factor::login_url(
				array(
					'action'        => 'backup_2fa',
					'provider'      => 'backup_codes',
					'wp-auth-id'    => $user->ID,
					'wp-auth-nonce' => $login_nonce,
					'redirect_to'   => $redirect_to,
					'rememberme'    => $rememberme,
				)
			);

			?>

			<?php if ( 'backup_codes' !== filter_input( INPUT_GET, 'provider' ) ) : ?>
				<div class="uo-toolkit-2fa-footer__backup-codes">
					<a href="<?php echo esc_url( $login_url ); ?>">
						<?php esc_html_e( 'Or, use a backup code.', 'uncanny-learndash-toolkit' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="uo-toolkit-2fa-footer__backup-codes">
					<a href="<?php echo esc_url( wp_login_url() ); ?>">
						<?php esc_html_e( 'Or, go back to login form.', 'uncanny-learndash-toolkit' ); ?>
					</a>
				</div>
			<?php endif; ?>

		<?php endif; ?>

			<?php do_action( 'wp_2fa_login_html_after_backup_providers', $user, $provider, $login_nonce, $redirect_to, $rememberme ); ?>

			<div class="uo-toolkit-2fa-footer__backto-home">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( 'Are you lost?', 'uncanny-learndash-toolkit' ); ?>">
					<?php
					echo esc_html(
						sprintf(
						// translators: %s: site name.
							__( '&larr; Back to %s', 'uncanny-learndash-toolkit' ),
							get_bloginfo( 'title', 'display' )
						)
					);
					?>
				</a>
			</div>

		</div>

		<div class="clear"></div>

		<?php
	}

	/**
	 * Sets a form cookie for key `uo-toolkit-guard` that will be used to very the nonce.
	 *
	 * @param string $cookie_value The value of the cookie.
	 *
	 * @return boolean True.
	 */
	protected function set_form_cookie( $cookie_value ) {

		// Setting zero for the expire time. Expires when user close the browser.
		setcookie( 'uo-toolkit-guard', $cookie_value, 0, COOKIEPATH, COOKIE_DOMAIN );

		return true;

	}

	/**
	 * Removes `uo-toolkit-guard` cookie value.
	 *
	 * @return boolean True.
	 */
	protected function delete_form_cookie() {

		// Setting zero for the expire time. Expires when user close the browser.
		setcookie( 'uo-toolkit-guard', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );

		return true;

	}

	/**
	 * Verify if the cookie is valid or not.
	 *
	 * @param string $nonce The nonce value.
	 * @param string $cookie The cookie value.
	 *
	 * @return boolean True if cookie is valid. Otherwise false.
	 */
	protected function verify_cookie( $nonce, $cookie ) {

		if ( ! class_exists( '\PasswordHash' ) ) {
			include_once ABSPATH . WPINC . '/class-phpass.php';
		}

		$wp_hasher = new \PasswordHash( 8, true );

		if ( $wp_hasher->CheckPassword( $nonce, $cookie ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Reads the redirect_to argument from url query and return it.
	 *
	 * @return string The filtered redirect_to value. Defaults to admin_url() if empty.
	 */
	protected function get_redirect_to() {

		$http_request_redirect_to =  isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to']: ''; //phpcs:ignore

		$redirect_to = filter_var( $http_request_redirect_to, FILTER_SANITIZE_URL );

		// Default to admin url when redirect to parameter is empty.
		if ( empty( $redirect_to ) ) {
			// Only apply filter when redirect is empty.
			$redirect_to = apply_filters( 'login_redirect', admin_url(), admin_url(), wp_get_current_user() );
		}

		return $redirect_to;

	}

	/**
	 * Delete the form cookie when logging out.
	 *
	 * @return void
	 */
	public function uo_clear_cookie_logout() {
		$this->delete_form_cookie();
	}

}
