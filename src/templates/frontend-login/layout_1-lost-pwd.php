<?php
namespace uncanny_learndash_toolkit;
if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
	$has_recaptcha =true;
}else{
	$has_recaptcha = false;
}
?>

<?php do_action( 'uo_forgot_before_container' ); ?>
		
<div id="ult-forgot-password">
	<div class="ult-box">

		<?php

		/**
		 * If the user submitted the form and the response was
		 * successful then we shouldn't show the fields again, only
		 * the success message
		 */
		
		if ( isset( $forgot_password_response ) && ! $forgot_password_response->error ){ ?>

			<?php do_action( 'uo_forgot_before_title' ); ?>

			<div class="ult-form__title">
				<?php echo $innerText[ 'Password-Recovery-Title' ]; ?>
			</div>

			<?php do_action( 'uo_forgot_before_success' ); ?>

			<div class="ult-form__row ult-form__row--validation">
				<div class="ult-notice ult-notice--success">
					<?php do_action( 'uo_forgot_before_success_message' ); ?>
					<?php echo $forgot_password_response->message; ?>
					<?php do_action( 'uo_forgot_after_success_message' ); ?>
				</div>
			</div>

			<?php do_action( 'uo_forgot_after_success' ); ?>

		<?php } else { ?>

			<?php do_action( 'uo_forgot_before_title' ); ?>

			<div class="ult-form__title">
				<?php echo $innerText[ 'Password-Recovery-Title' ]; ?>
			</div>

			<?php do_action( 'uo_forgot_before_description' ); ?>

			<div class="ult-form__description">
				<?php echo $innerText[ 'Password-Recovery-Desc' ]; ?>
			</div>

			<?php do_action( 'uo_forgot_before_form' ); ?>

			<div class="ult-form__container">

				<form id="ult-forgot-password-form" name="lostpasswordform" action="<?php echo site_url( 'wp-login.php?action=lostpassword', 'login_post' ); ?>" method="POST">

					<input type="hidden" name="redirect_to" value="<?php echo $login_page_url ?>action=forgot&success=1">

					<?php do_action( 'uo_forgot_before_email' ); ?>

					<div class="ult-form__row ult-form__row--email">
						<div class="ult-form-field__header">
							<div class="ult-form-field__label-container">
								<label for="ult-forgot-email" class="ult-form-field__label">
									<?php echo $innerText[ 'Password-Recovery-Label' ]; ?>
								</label>
							</div>
							<div class="ult-form-field__actions">
								<?php do_action( 'uo_forgot_email_actions' ); ?>
							</div>
						</div>

						<div class="ult-form__field">
							<input required type="text" name="user_login" id="ult-forgot-email" class="ult-form__input" placeholder="">
						</div>
					</div>

					<?php do_action( 'uo_forgot_before_captcha' ); ?>

					<?php if ( $has_recaptcha ){ ?>

					<div class="ult-form__row ult-form__row--recaptcha">
						<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha" data-expired-callback="expiredCaptcha"></div>
					</div>

					<?php } ?>

					<?php if ( isset( $forgot_password_response ) ){ ?>

						<?php do_action( 'uo_forgot_before_error' ); ?>

						<div class="ult-form__row ult-form__row--validation">
							<div class="ult-notice ult-notice--error">
								<?php do_action( 'uo_forgot_before_error_message' ); ?>
								<?php echo $forgot_password_response->message; ?>
								<?php do_action( 'uo_forgot_after_error_message' ); ?>
							</div>
						</div>

					<?php } ?>

					<?php do_action( 'uo_forgot_before_submit' ); ?>

					<div class="ult-form__row ult-form__row--submit">
						<button type="submit" id="ult-forgot-password-submit-btn" class="ult-form__submit-btn">
							<?php echo $innerText[ 'Get-New-Password' ]; ?>
						</button>
					</div>

					<?php do_action( 'uo_forgot_after_submit' ); ?>
				</form>

			</div>

			<?php do_action( 'uo_forgot_after_form' ); ?>

		<?php } ?>

	</div>
</div>

<?php do_action( 'uo_forgot_after_container' ); ?>