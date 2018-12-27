<?php

/* Template Name: Uncanny Owl Login Page */
$login_page     = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();
$login_page_url = get_permalink( $login_page );
$recaptcha_key  = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_recaptcha_key', 'FrontendLoginPlus' );
if ( '' !== trim( $recaptcha_key ) ) {
	wp_enqueue_script( 'FrontendLoginPlus', 'https://www.google.com/recaptcha/api.js' );
}
if ( strpos( $login_page_url, '?' ) ) {
	$login_page_url = $login_page_url . '&';
} else {
	$login_page_url = $login_page_url . '?';
}
$user_name_label = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_login_username_label_text', 'FrontendLoginPlus' );
$message_warning = '';

global $user_login;

/* Login */
$login = ( isset( $_GET['login'] ) ) ? $_GET['login'] : 'not-set';

/* Registration */
$register = false;
if ( '' !== \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_registration', 'FrontendLoginPlus' ) ) {

	$register_show = true;
	if ( isset( $_GET['action'] ) ) {

		if ( 'register' === $_GET['action'] ) {

			$register = true;
			$error    = false;

			if ( isset( $_GET['wp-error'] ) ) {

				$error = $_GET['wp-error'];

				switch ( $error ) {

					case 'empty_username':
						$message_warning = esc_html__( 'Please enter a username.', 'uncanny-learndash-toolkit' );
						break;
					case 'invalid_username':
						$message_warning = esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'uncanny-learndash-toolkit' );
						break;
					case 'username_exists':
						$message_warning = esc_html__( 'This username is already registered. Please choose another one.', 'uncanny-learndash-toolkit' );
						break;
					case 'empty_email':
						$message_warning = esc_html__( 'Please type your email address.', 'uncanny-learndash-toolkit' );
						break;
					case 'invalid_email':
						$message_warning = esc_html__( 'The email address is not correct.', 'uncanny-learndash-toolkit' );
						break;
					case 'email_exists':
						$message_warning = esc_html__( 'This email is already registered, please choose another one.', 'uncanny-learndash-toolkit' );
						break;
					case 'registration-disabled':
						$message_warning = esc_html__( 'We do not allow registrations.', 'uncanny-learndash-toolkit' );
						$register_show   = false;
						break;
					case 'registration-success':
						$message_warning = esc_html__( 'Registration complete. Registration confirmation has been emailed to you.', 'uncanny-learndash-toolkit' );
						$register_show   = false;
						break;
				}

			}

		}
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
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_failed_error', 'FrontendLoginPlus', esc_html__( 'Invalid username and/or password.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'empty':
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_empty_error', 'FrontendLoginPlus', esc_html__( 'Username and/or Password is empty.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'false':
		// Empty msgid. It is reserved by GNU gettext: gettext("") returns the header entry with meta information, not the empty string.
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_false_error', 'FrontendLoginPlus', esc_html__( 'You are logged out.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'notverified':
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_notverified_error', 'FrontendLoginPlus', esc_html__( 'This account is not verified.', 'uncanny-learndash-toolkit' ) );
		break;
}

$login_error = '';
if ( $message_warning ) {
	if( 'false' === $login){
		$login_error = '<p class="login-msg loggedout"><strong>' . '</strong> ' . $message_warning . '</p>';
	}else{
		$login_error = '<p class="login-msg"><strong>' . '</strong> ' . $message_warning . '</p>';
	}
}

$login_error = apply_filters( 'uo_frontend_login_error', $login_error, $login, '', $message_warning );

$login_form_args = array(
	'echo'           => true,
	'redirect'       => home_url( '/wp-admin/' ),
	'form_id'        => 'loginform',
	'label_username' => ( ! empty( $user_name_label ) ) ? $user_name_label : esc_html__( 'Username', 'uncanny-learndash-toolkit' ),
	'label_password' => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_password_label', 'FrontendLoginPlus', esc_html__( 'Password', 'uncanny-learndash-toolkit' ) ),
	'label_remember' => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_rememberme_label', 'FrontendLoginPlus', esc_html__( 'Remember Me', 'uncanny-learndash-toolkit' ) ),
	'label_log_in'   => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_button_label', 'FrontendLoginPlus', esc_html__( 'Log In', 'uncanny-learndash-toolkit' ) ),
	'id_username'    => 'user_login',
	'id_password'    => 'user_pass',
	'id_remember'    => 'rememberme',
	'id_submit'      => 'wp-submit',
	'remember'       => true,
	'value_username' => null,
	'value_remember' => true,
);

$login_form_args = apply_filters( 'uo_frontend_login_args', $login_form_args );

$innerText = Array(
	'Logged-In-Message'          => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_logged_in_message', 'FrontendLoginPlus', __( 'You are already logged in.', 'uncanny-learndash-toolkit' ) ),
	'Logout'                     => esc_html__( 'Logout', 'uncanny-learndash-toolkit' ),
	'Password-Recovery-Title'    => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_forgetpass_label', 'FrontendLoginPlus', esc_html__( 'Forgot password', 'uncanny-learndash-toolkit' ) ),
	'Password-Recovery-Label'    => ( ! empty( $user_name_label ) ) ? $user_name_label : esc_html__( 'Username', 'uncanny-learndash-toolkit' ),
	'Success'                    => esc_html__( 'Success!', 'uncanny-learndash-toolkit' ),
	'Success-Email-Sent'         => esc_html__( 'Check your email for a reset password link.', 'uncanny-learndash-toolkit' ),
	'Failed-Send-Email'          => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_failedsendemail_error', 'FrontendLoginPlus', esc_html__( 'Password reset email failed to send.', 'uncanny-learndash-toolkit' ) ),
	'Reset-Password-Title'       => esc_html__( 'Reset Password', 'uncanny-learndash-toolkit' ),
	'New-Password'               => esc_html__( 'New Password', 'uncanny-learndash-toolkit' ),
	'Confirm-Password'           => esc_html__( 'Confirm New Password', 'uncanny-learndash-toolkit' ),
	'Password-Indicator-Hint'    => esc_html__( 'Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; )', 'uncanny-learndash-toolkit' ),
	'Password-Reset-Link-Failed' => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_passwordresetfailed_error', 'FrontendLoginPlus', esc_html__( 'Password reset link failed.', 'uncanny-learndash-toolkit' ) ),
	'Invalid-Reset-Key'          => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_invalidresetkey_error', 'FrontendLoginPlus', esc_html__( 'Your password reset link is invalid.', 'uncanny-learndash-toolkit' ) ),
	'Expired-Reset-Key'          => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_expiredresetkey_error', 'FrontendLoginPlus', esc_html__( 'Your password reset link is expired.', 'uncanny-learndash-toolkit' ) ),
	'Password-Not-Match'         => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_passwordnotmatch_error', 'FrontendLoginPlus', esc_html__( 'The password values do not match.', 'uncanny-learndash-toolkit' ) ),
	'Reset-Success'              => esc_html__( 'Your password was successfully reset. Please log in.', 'uncanny-learndash-toolkit' ),
	'Login-Title'                => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_title_label', 'FrontendLoginPlus', esc_html__( 'Login', 'uncanny-learndash-toolkit' ) ),
	'Login-Description'          => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_description', 'FrontendLoginPlus', esc_html__( 'Login to your account to access your courses.', 'uncanny-learndash-toolkit' ) ),
	'Register-Link'              => \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_register_link_label', 'FrontendLoginPlus', esc_html__( 'Register', 'uncanny-learndash-toolkit' ) ),
	'Try-again'                  => esc_html__( 'Try again?', 'uncanny-learndash-toolkit' ),
	'Get-New-Password'           => esc_html__( 'Get New Password', 'uncanny-learndash-toolkit' )
);

$innerText = apply_filters( 'uo-login-inner-text', $innerText, $login );

?>
<!-- section -->

<style>

	#loginform label[for="user_login"],
	#loginform label[for="user_pass"] {
		display: block;
	}
	.login-msg{color:red;} .login-msg.loggedout{color:red;}

</style>
<?php if ( '' !== trim( $recaptcha_key ) ) { ?>
	<script>
        jQuery(document).ready(function () {
            jQuery("form").each(function () {
                jQuery(this).find(':input[type="submit"]').prop('disabled', true);
            });
        });

        function correctCaptcha() {
            jQuery("form").each(function () {
                jQuery(this).find(':input[type="submit"]').prop('disabled', false);
            });
        }

        function expiredCaptcha() {
            jQuery("form").each(function () {
                jQuery(this).find(':input[type="submit"]').prop('disabled', true);
            });
        }
	</script>
<?php } ?>
<section class="uo_loginForm">
	<div class="uo_error">
		<?php echo $login_error; ?>
	</div>

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
		echo '<div class="uo_logout">
                    <div class="uo_logout_user">' . $innerText['Logged-In-Message'] . '</div>
                    <a id="wp-submit" href="', wp_logout_url(), '" title="Logout">' . $innerText['Logout'] . '</a>
                </div>';
	} else if ( $lost_password ) {
		?>
		<h2><?php echo $innerText['Password-Recovery-Title']; ?></h2>
		<form id="lostpasswordform" name="lostpasswordform"
			  action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ?>" method="post">
			<p>
				<label for="user_login"><?php echo $innerText['Password-Recovery-Label']; ?></label>
				<input size="20" type="text" name="user_login" id="user_login" value="">
			</p>

			<input type="hidden" name="redirect_to"
				   value="<?php echo $login_page_url ?>action=forgot&success=1">
			<?php if ( '' !== trim( $recaptcha_key ) ) { ?>
				<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha"
					 data-expired-callback="expiredCaptcha"></div>
			<?php } ?>
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
									 value="<?php echo $innerText['Get-New-Password']; ?>"/></p>
		</form>
		<?php
	} elseif ( $reset_password_sent ) {
		if ( $reset_password_sent_success ) {
			?>
			<p class="login-msg">
				<strong><?php echo $innerText['Success']; ?></strong> <?php echo $innerText['Success-Email-Sent']; ?>
			</p>
			<?php
		} else {
			?>
			<p class="login-msg">
				<?php echo $innerText['Failed-Send-Email']; ?></p>
			<p>
				<a href="<?php echo $login_page_url ?>action=lostpassword"><?php echo $innerText['Try-again']; ?></a>
			</p>
			<?php
		}
	} elseif ( $register ) {
		if ( $register_show ) {
			?>
			<form name="registerform" id="registerform" action="<?php echo wp_login_url(); ?>?action=register"
				  method="post" novalidate="novalidate">
				<p>
					<label for="user_login">Username<br>
						<input type="text" name="user_login" id="user_login" class="input" value="" size="20"></label>
				</p>

				<p>
					<label for="user_email">Email<br>
						<input type="email" name="user_email" id="user_email" class="input" value="" size="25"></label>
				</p>

				<?php do_action( 'register_form' ); ?>

				<p id="reg_passmail">Registration confirmation will be emailed to you.</p>
				<br class="clear">
				<input type="hidden" name="redirect_to" value="">
				<?php if ( '' !== trim( $recaptcha_key ) ) { ?>
					<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha"
						 data-expired-callback="expiredCaptcha"></div>
				<?php } ?>
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
										 class="button button-primary button-large" value="Register"></p>
			</form>
			<?php
		}
	} elseif ( $reset_password ) {

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			$rp_key    = $_GET['key'];
			$rp_login  = $_GET['login'];
			$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
			$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
			//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

			?>
			<h2><?php echo $innerText['Reset-Password-Title']; ?></h2>
			<form name="resetpassform" id="resetpassform"
				  action="?action=validatepasswordreset" method="post"
				  autocomplete="off">
				<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"
					   autocomplete="off"/>

				<div class="user-pass1-wrap">
					<p>
						<label for="pass1"><?php echo $innerText['New-Password']; ?></label>
					</p>

					<div class="wp-pwd">
                            <span class="password-input-wrapper">
                                <input type="password" data-reveal="1"
									   data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1"
									   id="pass1" class="input" size="20" value="" autocomplete="off"
									   aria-describedby="pass-strength-result" required/>
                            </span>
					</div>
				</div>
				<p class="user-pass2-wrap">
					<label for="pass2"><?php echo $innerText['Confirm-Password']; ?></label><br/>
					<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off"
						   required/>
				</p>

				<p class="description indicator-hint"><?php echo $innerText['Password-Indicator-Hint']; ?></p>
				<br class="clear"/>
				<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
				<?php if ( '' !== trim( $recaptcha_key ) ) { ?>
					<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha"
						 data-expired-callback="expiredCaptcha"></div>
				<?php } ?>
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
										 class="button button-primary button-large"
										 value="<?php esc_attr_e( 'Reset Password' ); ?>"/></p>
			</form>
			<?php

		} else {

			?>
			<p class="login-msg">
				<?php echo $innerText['Password-Reset-Link-Failed']; ?>
			</p>
			<?php
		}
	} elseif ( $validate_password_reset ) {
		if ( isset( $_GET['issue'] ) ) {

			if ( 'invalidkey' === $_GET['issue'] ) {
				echo '<h2>' . $innerText['Invalid-Reset-Key'] . '</h2>';
			} elseif ( 'expiredkey' === $_GET['issue'] ) {
				echo '<h2>' . $innerText['Expired-Reset-Key'] . '</h2>';
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
				} else {
					wp_safe_redirect( $login_page_url . 'action=validatepasswordreset&issue=invalidkey' );
				}
			}

			$errors = new WP_Error();

			if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {

				echo '<h2>' . $innerText['Password-Not-Match'] . '</h2>';
				$rp_key    = $_POST['rp_key'];
				$rp_login  = $_POST['rp_login'];
				$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
				$value     = sprintf( '%s:%s', wp_unslash( $rp_key ), wp_unslash( $rp_login ) );
				//setcookie( $rp_cookie, $value, 0, '/' . get_post_field( 'post_name', $login_page ), COOKIE_DOMAIN, is_ssl(), true );

				?>
				<h2><?php echo $innerText['Reset-Password-Title']; ?></h2>
				<form name="resetpassform" id="resetpassform"
					  action="?action=validatepasswordreset" method="post"
					  autocomplete="off">
					<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"
						   autocomplete="off"/>

					<div class="user-pass1-wrap">
						<p>
							<label for="pass1"><?php echo $innerText['New-Password']; ?></label>
						</p>

						<div class="wp-pwd">
                            <span class="password-input-wrapper">
                                <input type="password" data-reveal="1"
									   data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1"
									   id="pass1" class="input" size="20" value="" autocomplete="off"
									   aria-describedby="pass-strength-result" required/>
                            </span>
						</div>
					</div>
					<p class="user-pass2-wrap">
						<label for="pass2"><?php echo $innerText['Confirm-Password']; ?></label><br/>
						<input type="password" name="pass2" id="pass2" class="input" size="20" value=""
							   autocomplete="off" required/>
					</p>

					<p class="description indicator-hint"><?php echo $innerText['Password-Indicator-Hint']; ?></p>
					<br class="clear"/>
					<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
					<?php if ( '' !== trim( $recaptcha_key ) ) { ?>
						<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>"
							 data-callback="correctCaptcha" data-expired-callback="expiredCaptcha"></div>
					<?php } ?>
					<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
											 class="button button-primary button-large"
											 value="<?php esc_attr_e( 'Reset Password' ); ?>"/></p>
				</form>
				<?php

			} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {

				reset_password( $user, $_POST['pass1'] );
				//setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );

				echo '<h2>' . $innerText['Reset-Success'] . '</h2>';

				wp_login_form( $login_form_args );
			}
		}
	} else {
		$show_title = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' );
		if ( 'on' !== $show_title ) { ?>
			<h2><?php echo $innerText['Login-Title']; ?></h2>
			<?php
		}

		$show_description = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontendloginplus_hide_description', 'FrontendLoginPlus' );

		if ( 'on' !== $show_description ) { ?>

			<p class="uo-login-description">
				<?php echo $innerText['Login-Description']; ?>
			</p>

		<?php }

		wp_login_form( $login_form_args );

		// Add registration link allowed
		if ( get_option( 'users_can_register' ) ) {
			$show_register = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' );
			if ( 'on' === $show_register ) {
				echo '<a class="register-link" href="' . \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ) . '" >' . $innerText['Register-Link'] . '</a>';
			}

		}

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
<!-- /section -->