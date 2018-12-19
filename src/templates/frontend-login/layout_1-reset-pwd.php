<?php
namespace uncanny_learndash_toolkit;
$has_recaptcha = ! empty( trim( $recaptcha_key ) );
?>

<?php do_action( 'uo_reset_before_container' ); ?>
		
<div id="ult-reset-password">
	<div class="ult-box">

		<?php do_action( 'uo_reset_before_title' ); ?>

		<div class="ult-form__title">
			<?php echo $innerText[ 'Reset-Password-Title' ]; ?>
		</div>

		<?php do_action( 'uo_reset_before_description' ); ?>

		<div class="ult-form__description">
			Please choose a new password to finish recovering your account.
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
						<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="ult-reset-new-password" class="ult-form__input" placeholder="" value="" autocomplete="off" aria-describedby="pass-strength-result">
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
						<input type="password" name="pass2" id="ult-reset-confirm-password" class="ult-form__input" placeholder="">
					</div>
				</div>

				<?php do_action( 'uo_reset_before_captcha' ); ?>

				<?php if ( $has_recaptcha ){ ?>

				<div class="ult-form__row ult-form__row--recaptcha">
					<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_key; ?>" data-callback="correctCaptcha" data-expired-callback="expiredCaptcha"></div>
				</div>

				<?php } ?>
				
				<?php if ( ! empty( $error ) ){ ?>

					<?php do_action( 'uo_reset_before_error' ); ?>

					<div class="ult-form__row ult-form__row--validation">
						<div class="ult-notice ult-notice--error">
							<?php do_action( 'uo_reset_before_error_message' ); ?>

							<?php echo $error; ?>

							<?php do_action( 'uo_reset_after_error_message' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php do_action( 'uo_reset_before_submit' ); ?>

				<div class="ult-form__row ult-form__row--submit">
					<button type="submit" id="ult-reset-password-submit-btn" class="ult-form__submit-btn">
						<?php esc_attr_e( 'Reset Password', 'uncanny-learndash-toolkit' ); ?>
					</button>
				</div>

				<?php do_action( 'uo_reset_after_submit' ); ?>
			</form>

		</div>

		<?php do_action( 'uo_reset_after_form' ); ?>
	</div>
</div>

<?php do_action( 'uo_reset_after_container' ); ?>