<?php
/* Template Name: Uncanny Owl Login Page */
$uo_public_text_domain = \uncanny_learndash_toolkit\Config::get_text_domain();
$login_page = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();

global $user_login;

/* Login */
$login = ( isset( $_GET['login'] ) ) ? $_GET['login'] : 'not-set';


/* Registration */
$register = false;
if( isset( $_GET['action'] ) ){
	if ( 'register' === $_GET['action'] ) {
		//$register = true;
	}
}

/* Lost Password */
$lost_password = false;
if ( isset( $_GET['action'] ) ) {
	if ( 'lostpassword' === $_GET['action'] ) {
		$lost_password = true;
	}
}

/* Sending Reset Password Link */
$reset_password_sent         = false;
$reset_password_sent_success = false;
if ( isset( $_GET['action'] ) ) {
	if ( 'forgot' === $_GET['action'] ) {
		$reset_password_sent = true;
		if ( '1' === $_GET['success'] ) {
			$reset_password_sent_success = true;
		}
	}
}

/* Reset Password */
$reset_password = false;
if ( isset( $_GET['action'] ) ) {
	if ( 'rp' === $_GET['action'] ) {
		$reset_password = true;
	}
}

/* Validate Reset Password Information */
$validate_password_reset = false;
if ( isset( $_GET['action'] ) ) {
	if ( 'validatepasswordreset' === $_GET['action'] ) {
		$validate_password_reset = true;
	}
}

switch ( $login ) {

	case 'failed':
		$message_error   = __( 'Woops!', $uo_public_text_domain );
		$message_warning = __( 'Invalid username and/or password.',  $uo_public_text_domain );
		break;
	case 'empty':
		$message_error   = __( 'Woops!', $uo_public_text_domain );
		$message_warning = __( 'Username and/or Password is empty.', $uo_public_text_domain );
		break;
	case 'false':
		$message_error   = __( '', $uo_public_text_domain );
		$message_warning = __( 'You are logged out.', $uo_public_text_domain );
		break;
	case 'notverified':
		$message_error   = __( 'Woops!', $uo_public_text_domain );
		$message_warning = __( 'This account is not verified.', $uo_public_text_domain );
		break;
	case 'registration-disabled':
		$message_error   = __( 'Woops!', $uo_public_text_domain );
		$message_warning = __( 'We do not allow registrations.', $uo_public_text_domain );
		break;
	default:
		$message_error   = '';
		$message_warning = '';
}
$login_error = '<p class="login-msg"><strong>' . $message_error . '</strong> ' . $message_warning . '</p>';
$login_error = apply_filters( 'uo_frontend_login_error', $login_error, $login, $message_error, $message_warning);

$login_form_args = array(
		'echo'           => true,
		'redirect'       => home_url( '/wp-admin/' ),
		'form_id'        => 'loginform',
		'label_username' => __( 'Username',  $uo_public_text_domain ),
		'label_password' => __( 'Password',  $uo_public_text_domain ),
		'label_remember' => __( 'Remember Me',  $uo_public_text_domain ),
		'label_log_in'   => __( 'Log In',  $uo_public_text_domain ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'remember'       => true,
		'value_username' => null,
		'value_remember' => true,
);

$innerText = Array(
		'Hello'						=>  __( 'Hello', $uo_public_text_domain ),
		'Logged-In-Message'			=>  __( 'You are already logged in', $uo_public_text_domain ),
		'Logout'					=>  __( 'Logout', $uo_public_text_domain ),
		'Password-Recovery-Title'	=>  __( 'Password Recovery', $uo_public_text_domain ),
		'Password-Recovery-Label'	=>  __( 'Username or E-mail:', $uo_public_text_domain ),
		'Success'					=>  __( 'Success!', $uo_public_text_domain ),
		'Success-Email-Sent'		=>  __( 'Check your email for a reset password link.', $uo_public_text_domain ),
		'Woops'						=>  __( 'Woops!', $uo_public_text_domain ),
		'Failed-Send-Email'			=>  __( 'Password reset failed to Send.', $uo_public_text_domain ),
		'Reset-Password-Title'		=>  __( 'Reset Password', $uo_public_text_domain ),
		'New-Password'				=>  __( 'New Password', $uo_public_text_domain ),
		'Confirm-Password'			=>  __( 'Confirm New Password', $uo_public_text_domain ),
		'Password-Indicator-Hint'	=>  __( 'Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; )', $uo_public_text_domain ),
		'Password-Reset-Link-Failed'=>  __( 'Password reset link failed.', $uo_public_text_domain ),
		'Invalid-Reset-Key'			=>  __( 'Your password reset link is invalid.', $uo_public_text_domain ),
		'Expired-Reset-Key'			=>  __( 'Your password reset link is expired.', $uo_public_text_domain ),
		'Password-Not-Match'		=>  __( 'Your Passwords did not match. Please try again.', $uo_public_text_domain ),
		'Reset-Success'				=>  __( 'Your password was reset successfully. Please Log-In.', $uo_public_text_domain ),
		'Login-Title'				=>  __( 'Login', $uo_public_text_domain ),
		'Register-Link'				=>  __( 'Register', $uo_public_text_domain )
);

$innerText = apply_filters( 'uo-login-inner-text', $innerText );


?>
<!-- section -->
<section class="uo_loginForm">
	<div class="uo_error">
		<?php echo $login_error; ?>
	</div>

	<?php
	if ( is_user_logged_in() ) {
		echo '<div class="uo_logout"> ' . $innerText['Hello'] . ',
                            <div class="uo_logout_user">', $user_login, ' ' . $innerText['Logged-In-Message'] . '</div>
                            <a id="wp-submit" href="', wp_logout_url(), '" title="Logout">' . $innerText['Logout'] . '</a>
                        </div>';
	} else if ( $lost_password ) {
		?>
		<h2><?php echo $innerText['Password-Recovery-Title']; ?></h2>
		<form id="lostpasswordform" name="lostpasswordform" action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ?>" method="post">
			<p>
				<label for="user_login"><?php echo $innerText['Password-Recovery-Label']; ?></label>
				<input size="20" type="text" name="user_login" id="user_login" value="">
			</p>

			<input type="hidden" name="redirect_to" value="<?php echo get_permalink( $login_page ); ?>?action=forgot&success=1">
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="Get New Password"/></p>
		</form>
		<?php
	} elseif ( $reset_password_sent ) {
		if ($reset_password_sent_success) {
			?>
			<p class="login-msg">
				<strong><?php echo $innerText['Success']; ?></strong> <?php echo $innerText['Success-Email-Sent']; ?>
			</p>
			<?php
		} else {
			?>
			<p class="login-msg">
				<strong><?php echo $innerText['Woops']; ?></strong> <?php echo $innerText['Failed-Send-Email']; ?></p>
			<p><a href="<?php echo get_permalink($login_page); ?>?action=lostpassword">Try again?</a></p>
			<?php
		}
	}elseif ( $register ){
		?>
		<form name="registerform" id="registerform" action="http://www.uopublicplugin.dev/wp-login.php?action=register" method="post" novalidate="novalidate">
			<p>
				<label for="user_login">Username<br>
					<input type="text" name="user_login" id="user_login" class="input" value="" size="20"></label>
			</p>
			<p>
				<label for="user_email">Email<br>
					<input type="email" name="user_email" id="user_email" class="input" value="" size="25"></label>
			</p>
			<p id="reg_passmail">Registration confirmation will be emailed to you.</p>
			<br class="clear">
			<input type="hidden" name="redirect_to" value="">
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Register"></p>
		</form>
		<?php
	} elseif ( $reset_password ) {

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			$rp_key    = $_GET['key'];
			$rp_login  = $_GET['login'];
			$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
			$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
			setcookie( $rp_cookie, $value, 0, '/'.get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

			?>
			<h2><?php echo $innerText['Reset-Password-Title']; ?></h2>
			<form name="resetpassform" id="resetpassform" action="<?php get_permalink( $login_page ); ?>?action=validatepasswordreset" method="post" autocomplete="off">
				<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>" autocomplete="off"/>

				<div class="user-pass1-wrap">
					<p>
						<label for="pass1"><?php echo $innerText['New-Password']; ?></label>
					</p>
					<div class="wp-pwd">
                            <span class="password-input-wrapper">
                                <input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off"
									   aria-describedby="pass-strength-result"/>
                            </span>
					</div>
				</div>
				<p class="user-pass2-wrap">
					<label for="pass2"><?php echo $innerText['Confirm-Password']; ?></label><br/>
					<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off"/>
				</p>
				<p class="description indicator-hint"><?php echo $innerText['Password-Indicator-Hint'] ; ?></p>
				<br class="clear"/>
				<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Reset Password' ); ?>"/></p>
			</form>
			<?php

		} else {

			?>
			<p class="login-msg"><strong><?php echo $innerText['Woops']; ?></strong> <?php echo $innerText['Password-Reset-Link-Failed']; ?></p>
			<?php
		}
	} elseif ( $validate_password_reset ) {
		if ( isset( $_GET['issue'] ) ) {

			if ( 'invalidkey' === $_GET['issue'] ) {
				echo '<h2>'.$innerText['Invalid-Reset-Key'].'</h2>';
			} elseif ( 'expiredkey' === $_GET['issue'] ) {
				echo '<h2>'.$innerText['Expired-Reset-Key'].'</h2>';
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

				setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );
				if ( $user && $user->get_error_code() === 'expired_key' ) {
					wp_safe_redirect( get_permalink( $login_page ).'?action=validatepasswordreset&issue=expiredkey'  );
				} else {
					wp_safe_redirect( get_permalink( $login_page ).'?action=validatepasswordreset&issue=invalidkey' );
				}
			}

			$errors = new WP_Error();

			if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {

				echo '<h2>' . $innerText['Password-Not-Match'] . '</h2>';

			} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {

				reset_password( $user, $_POST['pass1'] );
				setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );

				echo '<h2>' . $innerText['Reset-Success'] . '</h2>';

				wp_login_form( $login_form_args );
			}
		}
	} else {
		?>
		<h2>Login</h2>
		<?php
		wp_login_form( $login_form_args );

		// Add registration link allowed
		if ( get_option( 'users_can_register' ) ) {
			echo '<a class="register-link" href="'.wp_registration_url().'" >'.$innerText['Register-Link'].'</a>';
		}

	}
	?>

</section>
<!-- /section -->
