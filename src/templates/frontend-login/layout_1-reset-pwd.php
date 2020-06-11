<?php
namespace uncanny_learndash_toolkit;
if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
	$has_recaptcha =true;
}else{
	$has_recaptcha = false;
}

$login_page      = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();
$login_page_url  = get_permalink( $login_page );

?>

<?php

// Validation classes
$notice_css_classes = [];

// Check if there are errors
$has_errors = ! empty( $error );

// Add a CSS class if it has an error
if ( $has_errors ){
	$notice_css_classes[] = 'ult-form__validation--has-error';
}

?>

<?php do_action( 'uo_reset_before_container' ); ?>
		
<div id="ult-reset-password">
	<div class="ult-box">

		<div class="ult-form__content">

			<?php do_action( 'uo_reset_before_title' ); ?>

			<div class="ult-form__title">
				<?php echo $innerText[ 'Reset-Password-Title' ]; ?>
			</div>

			<?php do_action( 'uo_reset_before_description' ); ?>

			<div class="ult-form__description">
				<?php echo $innerText[ 'Reset-Password-Desc' ]; ?>
			</div>

			<?php do_action( 'uo_reset_before_form' ); ?>

			<div class="ult-form__container">

				<form id="ult-reset-password-form" name="resetpassform" action="?action=validatepasswordreset" method="POST">

					<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"/>

					<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>

					<?php do_action( 'uo_reset_before_password' ); ?>

					<div class="ult-form__row ult-form__row--password">
						<div class="ult-form-field__header">
							<div class="ult-form-field__label-container">
								<label for="ult-reset-new-password" class="ult-form-field__label">
									<?php echo $innerText[ 'New-Password' ]; ?>
								</label>
							</div>
							<div class="ult-form-field__actions">
								<?php do_action( 'uo_reset_password_actions' ); ?>
							</div>
						</div>

						<div class="ult-form__field">
							<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="ult-reset-new-password" class="ult-form__input" placeholder="" value="" autocomplete="off" aria-describedby="pass-strength-result" required>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_confirm_password' ); ?>

					<div class="ult-form__row ult-form__row--confirm-password">
						<div class="ult-form-field__header">
							<div class="ult-form-field__label-container">
								<label for="ult-reset-confirm-password" class="ult-form-field__label">
									<?php echo $innerText[ 'Confirm-Password' ]; ?>
								</label>
							</div>
							<div class="ult-form-field__actions">
								<?php do_action( 'uo_reset_confirm_password_actions' ); ?>
							</div>
						</div>

						<div class="ult-form__field">
							<input type="password" name="pass2" id="ult-reset-confirm-password" class="ult-form__input" placeholder="" required>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_captcha' ); ?>

					<?php if ( $has_recaptcha ){ ?>

                        <div class="ult-form__row ult-form__row--recaptcha">
                            <div class="ult-form-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
                        </div>
					
					<?php } ?>

					<?php do_action( 'uo_reset_before_error' ); ?>

					<div class="ult-form__validation <?php echo implode( ' ', $notice_css_classes ); ?>">
						<div class="ult-notice ult-notice--error">
							<?php do_action( 'uo_reset_before_error_message' ); ?>

							<span class="ult-notice-text"><?php echo $error; ?></span>

							<?php do_action( 'uo_reset_after_error_message' ); ?>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_submit' ); ?>

					<div class="ult-form__row ult-form__row--submit">
						<button type="submit" id="ult-reset-password-submit-btn" class="ult-form__submit-btn">
							<?php echo $innerText['Reset-Password-Button']; ?>
						</button>
					</div>

					<?php do_action( 'uo_reset_after_submit' ); ?>
				</form>

			</div>
			
		</div>

		<div class="ult-form__footer">
			<?php do_action( 'uo_forgot_before_login' ); ?>

			<div class="ult-form-footer__login">
				<a id="ult-form-footer-login" href="<?php echo $login_page_url; ?>">
					<?php echo $innerText[ 'Log-in-instead' ]; ?>
				</a>
			</div>
		</div>

		<?php do_action( 'uo_reset_after_form' ); ?>
	</div>
</div>

<?php do_action( 'uo_reset_after_container' ); ?>