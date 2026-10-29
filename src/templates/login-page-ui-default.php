<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside FrontendLoginPlus::uo_login_ui(); variables are local to that method.

?>
	<section class="uo_loginForm">
		<script>
			<?php $json_data = wp_json_encode( apply_filters( 'uo_toolkit_frontend_login_form_data', array() ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
			let UOToolkitFrontEndLoginFormData = <?php echo $json_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON produced by wp_json_encode() with JSON_HEX_* flags on the line above, so it cannot break out of the script block. ?>;
		</script>
		<?php
		/*
		 * before_uo_login_ui hook
		 *
		 * @arg bool $lost_password
		 * @arg bool $reset_password_sent
		 * @arg bool $reset_password_sent_success
		 * @arg bool $register
		 * @arg bool $reset_password
		 * @arg bool $validate_password_reset
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
		do_action( 'before_uo_login_ui', $lost_password, $reset_password_sent, $reset_password_sent_success, $register, $reset_password, $validate_password_reset );

		if ( is_user_logged_in() ) {
			//If User is Logged In, we don't need to show any form!

			?>

			<div class="uo-default-message-block">

				<div class="uo-default-message-text">
					<?php echo wp_kses_post( $innerText['Logged-In-Message'] ); ?>

					<div class="uo-default-link">
						<a href="<?php echo esc_url( wp_logout_url() ); ?>" title="<?php echo esc_attr( $innerText['Logout'] ); ?>">
							<?php echo wp_kses_post( $innerText['Logout'] ); ?>
						</a>
					</div>
				</div>

			</div>

			<?php

		} elseif ( $lost_password ) {
			//If User is requesting a lost password, show form!
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
			include Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) );
		} elseif ( $reset_password_sent ) {
			$forgot_password_response = (object) array(
				'error'   => true,
				'message' => '',
			);

			//When Lost Password Form is submitted, show status!
			if ( true === $reset_password_sent_success ) {
				$forgot_password_response->error   = false;
				$forgot_password_response->message = $innerText['Success-Email-Sent'];
			} else {
				if ( 'recaptchaempty' === $reset_password_sent_success ) {
					$forgot_password_response->message = $innerText['recaptchaempty'];
				} elseif ( 'recaptchafailed' === $reset_password_sent_success ) {
					$forgot_password_response->message = $innerText['recaptchafailed'];
				} elseif ( 'invalidcredentials' === $reset_password_sent_success ) {
					$forgot_password_response->message = $innerText['Failed-Pass-Creds'];
				} else {
					$forgot_password_response->message = $innerText['Failed-Send-Email'];
				}
			}

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
			$forgot_password_response->message = apply_filters( 'uo_toolkit_reset_password_errors', $forgot_password_response->message );

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
			include Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) );
		} elseif ( $register ) {
			//If registration is open and user is on register page!
			if ( $register_show ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
				include Config::get_template( apply_filters( 'uo-front-login-register-template', 'frontend-login/' . $template_to_load . '-register.php', $template_to_load ) );
			}
		} elseif ( $reset_password ) {
			//When user clicks reset password link in email!
			if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Password-reset link from the reset email; like core, it has no nonce (the reset key is the credential).
				$rp_key    = sanitize_text_field( wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Password-reset link from the reset email; like core, it has no nonce (the reset key is the credential).
				$rp_login  = sanitize_text_field( wp_unslash( $_GET['login'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Password-reset link from the reset email; like core, it has no nonce (the reset key is the credential).
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value     = sprintf( '%s:%s', $rp_login, $rp_key );
				//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
				include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
			} else {
				?>

				<div class="uo-default-message-block">
					<div class="uo-default-message-text">
						<?php echo wp_kses_post( $innerText['Password-Reset-Link-Failed'] ); ?>
					</div>
				</div>

				<?php
			}
		} elseif ( $validate_password_reset ) {
			//When user reset new password!
			if ( isset( $_GET['issue'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
				$error = '';

				if ( 'invalidkey' === $_GET['issue'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
					$error = sprintf( '<p>%s</p>', $innerText['Invalid-Reset-Key'] );
				} elseif ( 'expiredkey' === $_GET['issue'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
					$error = sprintf( '<p>%s</p>', $innerText['Expired-Reset-Key'] );
				}

				echo wp_kses_post( $error );

			} else {
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( wp_unslash( $_COOKIE[ $rp_cookie ] ), ':' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reset cookie is passed unchanged to check_password_reset_key(), which validates it (as core wp-login.php does).
					list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reset cookie is passed unchanged to check_password_reset_key(), which validates it (as core wp-login.php does).
					$user                      = check_password_reset_key( $rp_key, $rp_login );
					//var_dump($user);
					$posted_rp_key = isset( $_POST['rp_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
					if ( isset( $_POST['pass1'] ) && ( '' === $posted_rp_key || ! hash_equals( $rp_key, $posted_rp_key ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
						$user = false;
					}
				} else {
					$user = false;
				}

				$errors = new \WP_Error();

				$_password_strength = Config::get_settings_value( 'uo_frontendloginplus_reset_password_strength', 'FrontendLoginPlus' );

				if ( isset( $_POST['pass1'] ) && $_password_strength === 'on' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
					$password_ok = self::slt_fsp_password_strength( $_POST['pass1'], $user->user_login ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password is used raw on purpose, as core does; it is only scored for strength, never output or stored here.
					if ( $password_ok < 3 ) {
						$error     = $innerText['Password-Not-Strong'];
						$rp_key    = isset( $_POST['rp_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
						$rp_login  = isset( $_POST['rp_login'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
						$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
						$value     = sprintf( '%s:%s', $rp_key, $rp_login );
						//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
						include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
					}
				}

				if ( isset( $_POST['pass1'] ) && ( ! isset( $_POST['pass2'] ) || $_POST['pass1'] != $_POST['pass2'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).

					$error     = $innerText['Password-Not-Match'];
					$rp_key    = isset( $_POST['rp_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
					$rp_login  = isset( $_POST['rp_login'] ) ? sanitize_text_field( wp_unslash( $_POST['rp_login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Front-end reset-password form; like core, it has no nonce (the reset cookie/key is the credential, checked with check_password_reset_key()).
					$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
					$value     = sprintf( '%s:%s', $rp_key, $rp_login );
					//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
					include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
				}
			}
		} else {
			if ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
				$reset_password_sucess = $innerText['Reset-Success'];
			}
			//Nothing, default, show login form!
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
			include Config::get_template( apply_filters( 'uo-front-login-login-template', 'frontend-login/' . $template_to_load . '-login.php', $template_to_load ) );
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
		do_action( 'after_uo_login_ui', $lost_password, $reset_password_sent, $reset_password_sent_success, $register, $reset_password, $validate_password_reset );
		?>
	</section>
