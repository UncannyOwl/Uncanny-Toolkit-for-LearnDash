<?php
namespace uncanny_learndash_toolkit;

?>
<h2><?php echo $innerText['Password-Recovery-Title']; ?></h2>
<?php if ( ! isset( $forgot_password_response ) ) { ?>
		<p><?php echo $innerText['Password-Recovery-Desc']; ?></p>
	<?php
}

/**
 * If the user submitted the form and the response was
 * successful then we shouldn't show the fields again, only
 * the success message
 */

if ( isset( $forgot_password_response ) && ! $forgot_password_response->error ) {
	?>
	<?php if ( isset( $forgot_password_response ) ) { ?>
		<p>
			<?php echo $forgot_password_response->message; ?>
		</p>
	<?php } ?>
<?php } else { ?>
	<form id="lostpasswordform" name="lostpasswordform" action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ); ?>" method="post">
		<?php if ( isset( $forgot_password_response ) ) { ?>
			<p>
				<?php echo $forgot_password_response->message; ?>
			</p>
		<?php } ?>

		<p>
			<label for="user_login"><?php echo $innerText['Password-Recovery-Label']; ?></label><br>
			<input required size="20" type="text" name="user_login" id="user_login" value="">
		</p>
		<p class="ult-form__validation ult-hide" id="ult_lp_error_container">
			<span class="ult-notice ult-notice--error"></span>
		</p>
		<input type="hidden" name="redirect_to" value="<?php echo $login_page_url . ( strpos( $login_page_url, '?' ) ? '&' : '?' ); ?>action=forgot&success=1">
		<?php if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) { ?>
			<div class="ult-form-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
		<?php } ?>
		
		<?php do_action( 'uo_forgot_before_submit' ); ?>
		
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php echo $innerText['Get-New-Password']; ?>"/>
		</p>
	</form>
<?php } ?>
