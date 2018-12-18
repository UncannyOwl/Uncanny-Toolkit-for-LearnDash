<?php

namespace uncanny_learndash_toolkit;

?>
	<section class="uo_loginForm">
		
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
			uo_generate_default_message_block( $innerText['Hello'], "$user_login, " . $innerText['Logged-In-Message'], wp_logout_url(), $innerText['Logout'] );
		} elseif ( $lost_password ) {
			//If User is requesting a lost password, show form!
			include( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) );
		} elseif ( $reset_password_sent ) {
			$forgot_password_response = (object) [
				'error'   => true,
				'message' => ''
			];

			//When Lost Password Form is submitted, show status!
			if ( $reset_password_sent_success ){
				$forgot_password_response->error = false;
				$forgot_password_response->message = $innerText['Success-Email-Sent'];
			} else {
				$forgot_password_response->message = $innerText['Failed-Send-Email'];
			}

			include( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) );
		} elseif ( $register ) {
			//If registration is open and user is on register page!
			if ( $register_show ) {
				include( apply_filters( 'uo-front-login-register-template', 'frontend-login/' . $template_to_load . '-register.php', $template_to_load ) );
			}
		} elseif ( $reset_password ) {
			//When user clicks reset password link in email!
			if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
				$rp_key    = $_GET['key'];
				$rp_login  = $_GET['login'];
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
				//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );
				include( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
			} else {
				uo_generate_default_message_block( $innerText['Oops'], $innerText['Password-Reset-Link-Failed'] );
			}
		} elseif ( $validate_password_reset ) {
			//When user reset new password!
			if ( isset( $_GET['issue'] ) ) {

				if ( 'invalidkey' === $_GET['issue'] ) {
					echo sprintf( '<h2>%s</h2>', $innerText['Invalid-Reset-Key'] );
				} elseif ( 'expiredkey' === $_GET['issue'] ) {
					echo sprintf( '<h2>%s</h2>', $innerText['Expired-Reset-Key'] );
				}
			} else {
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
					list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
					$user = check_password_reset_key( $rp_key, $rp_login );
					//var_dump($user);
					if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
						$user = false;
					}
				} else {
					$user = false;
				}

				if ( ! $user || is_wp_error( $user ) && ! isset( $_POST['pass1'] ) ) {

					//setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );
					if ( $user && $user->get_error_code() === 'expired_key' ) {
						wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=expiredkey' );
						die();
					} else {
						wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=invalidkey' );
						die();
					}
				}

				$errors = new \WP_Error();

				if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {

					echo '<h2>' . $innerText['Password-Not-Match'] . '</h2>';
					$rp_key    = $_POST['rp_key'];
					$rp_login  = $_POST['rp_login'];
					$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
					$value     = sprintf( '%s:%s', wp_unslash( $rp_key ), wp_unslash( $rp_login ) );
					//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );
					include( apply_filters( 'uo-front-login-reset-template', 'frontend-login/' . $template_to_load . '-reset-pwd.php', $template_to_load ) );
				} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {

					reset_password( $user, $_POST['pass1'] );
					//setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );

					echo sprintf( '<h2>%s</h2>', $innerText['Reset-Success'] );
					include( apply_filters( 'uo-front-login-login-template', 'frontend-login/' . $template_to_load . '-login.php', $template_to_load ) );
				}
			}
		} else {
			//Nothing, default, show login form!
			include( apply_filters( 'uo-front-login-login-template', 'frontend-login/' . $template_to_load . '-login.php', $template_to_load ) );
		}

		/*
		 * after_uo_login_ui hook
		 *
		 * @arg bool $lost_password
		 * @arg bool $reset_password_sent
		 * @arg bool $reset_password_sent_success
		 * @arg bool $register
		 * @arg bool $reset_password
		 * @arg bool $validate_password_reset
		 */
		do_action( 'after_uo_login_ui', $lost_password, $reset_password_sent, $reset_password_sent_success, $register, $reset_password, $validate_password_reset );
		?>
	</section>

	<?php
/**
 * Generate default message block!
 *
 * @param string $heading
 * @param string $message
 * @param string $link
 * @param string $link_text
 */
function uo_generate_default_message_block( $heading = '', $message = '', $link = '', $link_text = '' ) {
	?>
	<div class="uo-default-message-block">
		<div class="uo-default-heading-text"><h3><?php echo $heading ?></h3></div>
		<div class="uo-default-message-text">
			<?php echo $message; ?>
			<?php if ( ! empty( $link ) && ! empty( $link_text ) ) {
				?>
				<div class="uo-default-link">
					<a href="<?php echo $link ?>" title="<?php echo $link_text ?>"><?php echo $link_text ?></a>
				</div>
				<?php
			} ?>
		</div>
	</div>
	<?php
}
