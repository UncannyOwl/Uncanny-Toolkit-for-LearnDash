<?php

namespace uncanny_learndash_toolkit;

?>
	<section class="uo_loginForm">
		<script>
			<?php $json_data = wp_json_encode( apply_filters( 'uo_toolkit_frontend_login_form_data', array() ) ); ?>
			let UOToolkitFrontEndLoginFormData = JSON.parse('<?php echo $json_data; ?>');
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
		do_action( 'before_uo_login_ui', $lost_password, $reset_password_sent, $reset_password_sent_success, $register, $reset_password, $validate_password_reset );

		if ( is_user_logged_in() ) {
			//If User is Logged In, we don't need to show any form!

			?>

			<div class="uo-default-message-block">

				<div class="uo-default-message-text">
					<?php echo $innerText['Logged-In-Message']; ?>

					<div class="uo-default-link">
						<a href="<?php echo wp_logout_url(); ?>" title="<?php echo $innerText['Logout']; ?>">
							<?php echo $innerText['Logout']; ?>
						</a>
					</div>
				</div>

			</div>

			<?php

		} elseif ( $lost_password ) {
			//If User is requesting a lost password, show form!
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

			$forgot_password_response->message = apply_filters( 'uo_toolkit_reset_password_errors', $forgot_password_response->message );

			include Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) );
		} elseif ( $register ) {
			//If registration is open and user is on register page!
			if ( $register_show ) {
				include Config::get_template( apply_filters( 'uo-front-login-register-template', 'frontend-login/' . $template_to_load . '-register.php', $template_to_load ) );
			}
		} elseif ( $reset_password ) {
			//When user clicks reset password link in email!
			if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
				$rp_key    = $_GET['key'];
				$rp_login  = $_GET['login'];
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
				//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );
				include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
			} else {
				?>

				<div class="uo-default-message-block">
					<div class="uo-default-message-text">
						<?php echo $innerText['Password-Reset-Link-Failed']; ?>
					</div>
				</div>

				<?php
			}
		} elseif ( $validate_password_reset ) {
			//When user reset new password!
			if ( isset( $_GET['issue'] ) ) {
				$error = '';

				if ( 'invalidkey' === $_GET['issue'] ) {
					$error = sprintf( '<p>%s</p>', $innerText['Invalid-Reset-Key'] );
				} elseif ( 'expiredkey' === $_GET['issue'] ) {
					$error = sprintf( '<p>%s</p>', $innerText['Expired-Reset-Key'] );
				}

				echo $error;

			} else {
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
					list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
					$user                      = check_password_reset_key( $rp_key, $rp_login );
					//var_dump($user);
					if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
						$user = false;
					}
				} else {
					$user = false;
				}

				$errors = new \WP_Error();

				$_password_strength = Config::get_settings_value( 'uo_frontendloginplus_reset_password_strength', 'FrontendLoginPlus' );

				if ( isset( $_POST['pass1'] ) && $_password_strength === 'on' ) {
					$password_ok = self::slt_fsp_password_strength( $_POST['pass1'], $user->user_login );
					if ( $password_ok < 3 ) {
						$error     = $innerText['Password-Not-Strong'];
						$rp_key    = $_POST['rp_key'];
						$rp_login  = $_POST['rp_login'];
						$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
						$value     = sprintf( '%s:%s', wp_unslash( $rp_key ), wp_unslash( $rp_login ) );
						//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

						include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
					}
				}

				if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {

					$error     = $innerText['Password-Not-Match'];
					$rp_key    = $_POST['rp_key'];
					$rp_login  = $_POST['rp_login'];
					$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
					$value     = sprintf( '%s:%s', wp_unslash( $rp_key ), wp_unslash( $rp_login ) );
					//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

					include Config::get_template( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
				}
			}
		} else {
			if ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) {
				$reset_password_sucess = $innerText['Reset-Success'];
			}
			//Nothing, default, show login form!
			include Config::get_template( apply_filters( 'uo-front-login-login-template', 'frontend-login/' . $template_to_load . '-login.php', $template_to_load ) );
		}

		do_action( 'after_uo_login_ui', $lost_password, $reset_password_sent, $reset_password_sent_success, $register, $reset_password, $validate_password_reset );
		?>
	</section>
