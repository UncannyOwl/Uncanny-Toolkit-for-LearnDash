<?php
namespace uncanny_learndash_toolkit;
$has_recaptcha = ! empty( trim( $recaptcha_key ) );
?>

<?php do_action( 'uo_forgot_before_container' ); ?>
		
<div id="ult-forgot-password">
	<div class="ult-box">

		<?php do_action( 'uo_forgot_before_title' ); ?>

		<div class="ult-form__title">
			<?php echo $innerText[ 'Password-Recovery-Title' ]; ?>
		</div>

		<?php do_action( 'uo_forgot_before_description' ); ?>

		<div class="ult-form__description">
			<?php _e( "Enter the email address you used when you joined and we'll send you instructions to reset your password.", 'uncanny-learndash-toolkit' ); ?>
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
						<input type="text" name="user_login" id="ult-forgot-email" class="ult-form__input" placeholder="">
					</div>
				</div>

				<?php do_action( 'uo_forgot_before_captcha' ); ?>

				<?php if ( $has_recaptcha ){ ?>

				<div class="ult-form__row ult-form__row--recaptcha">
					<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha" data-expired-callback="expiredCaptcha"></div>
				</div>

				<?php } ?>

				<?php do_action( 'uo_forgot_before_validation' ); ?>

				<div class="ult-form__row ult-form__row--validation">
					<div class="ult-notice ult-notice--success">
						<?php do_action( 'uo_forgot_before_validation_message' ); ?>

						Show messages here. Error and success

						<?php do_action( 'uo_forgot_after_validation_message' ); ?>
					</div>
				</div>

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
	</div>
</div>

<?php do_action( 'uo_forgot_after_container' ); ?>