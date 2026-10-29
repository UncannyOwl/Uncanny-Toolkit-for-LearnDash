<?php
namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside FrontendLoginPlus::uo_login_ui(); variables are local to that method.

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

<?php do_action( 'uo_reset_before_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
		
<div id="ult-reset-password">
	<div class="ult-box">

		<div class="ult-form__content">

			<?php do_action( 'uo_reset_before_title' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

			<div class="ult-form__title">
				<?php echo wp_kses_post( $innerText['Reset-Password-Title'] ); ?>
			</div>

			<?php do_action( 'uo_reset_before_description' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

			<div class="ult-form__description">
				<?php echo wp_kses_post( $innerText['Reset-Password-Desc'] ); ?>
			</div>

			<?php do_action( 'uo_reset_before_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

			<div class="ult-form__container">

				<form id="ult-reset-password-form" name="resetpassform" action="?action=validatepasswordreset" method="POST">

					<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"/>

					<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>

					<?php do_action( 'uo_reset_before_password' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__row ult-form__row--password">
						<div class="ult-form-field__header">
							<div class="ult-form-field__label-container">
								<label for="ult-reset-new-password" class="ult-form-field__label">
									<?php echo wp_kses_post( $innerText['New-Password'] ); ?>
								</label>
							</div>
							<div class="ult-form-field__actions">
								<?php do_action( 'uo_reset_password_actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
							</div>
						</div>

						<div class="ult-form__field">
							<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="ult-reset-new-password" class="ult-form__input" placeholder="" value="" autocomplete="off" aria-describedby="pass-strength-result" required>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_confirm_password' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__row ult-form__row--confirm-password">
						<div class="ult-form-field__header">
							<div class="ult-form-field__label-container">
								<label for="ult-reset-confirm-password" class="ult-form-field__label">
									<?php echo wp_kses_post( $innerText['Confirm-Password'] ); ?>
								</label>
							</div>
							<div class="ult-form-field__actions">
								<?php do_action( 'uo_reset_confirm_password_actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
							</div>
						</div>

						<div class="ult-form__field">
							<input type="password" name="pass2" id="ult-reset-confirm-password" class="ult-form__input" placeholder="" required>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_captcha' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<?php if ( $has_recaptcha ){ ?>

                        <div class="ult-form__row ult-form__row--recaptcha">
                            <div class="ult-form-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_key ); ?>" data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
                        </div>
					
					<?php } ?>

					<?php do_action( 'uo_reset_before_error' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__validation <?php echo esc_attr( implode( ' ', $notice_css_classes ) ); ?>">
						<div class="ult-notice ult-notice--error">
							<?php do_action( 'uo_reset_before_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

							<span class="ult-notice-text"><?php echo wp_kses_post( isset( $error ) ? $error : '' ); ?></span>

							<?php do_action( 'uo_reset_after_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
						</div>
					</div>

					<?php do_action( 'uo_reset_before_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__row ult-form__row--submit">
						<button type="submit" id="ult-reset-password-submit-btn" class="ult-form__submit-btn">
							<?php echo wp_kses_post( $innerText['Reset-Password-Button'] ); ?>
						</button>
					</div>

					<?php do_action( 'uo_reset_after_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
				</form>

			</div>
			
		</div>

		<div class="ult-form__footer">
			<?php do_action( 'uo_forgot_before_login' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

			<div class="ult-form-footer__login">
				<a id="ult-form-footer-login" href="<?php echo esc_url( $login_page_url ); ?>">
					<?php echo wp_kses_post( $innerText['Log-in-instead'] ); ?>
				</a>
			</div>
		</div>

		<?php do_action( 'uo_reset_after_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
	</div>
</div>

<?php do_action( 'uo_reset_after_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>