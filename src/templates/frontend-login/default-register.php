<?php
namespace uncanny_learndash_toolkit;
?>
<form name="registerform" id="registerform" action="<?php echo wp_login_url(); ?>?action=register"
      method="post" novalidate="novalidate">
	<p>
		<label for="user_login">Username<br>
			<input type="text" name="user_login" id="user_login" class="input" value="" size="20" required></label>
	</p>

	<p>
		<label for="user_email">Email<br>
			<input type="email" name="user_email" id="user_email" class="input" value="" size="25" required></label>
	</p>

	<?php do_action( 'register_form' ); ?>

	<p id="reg_passmail">Registration confirmation will be emailed to you.</p>
	<br class="clear">
	<input type="hidden" name="redirect_to" value="">
	<?php if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) { ?>
		<div class="ult-form-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
	<?php } ?>
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
	                         class="button button-primary button-large" value="Register"></p>
</form>