<?php
namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside FrontendLoginPlus::uo_login_ui(); variables are local to that method.

if ( '' !== trim( $recaptcha_key ) && '' !== trim( $recaptcha_secrete_key ) ) {
	$has_recaptcha = true;
} else {
	$has_recaptcha = false;
}

$login_page     = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();
$login_page_url = get_permalink( $login_page );

?>
	
	<?php do_action( 'uo_forgot_before_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

	<?php 
	// Enqueue color overrides for Modern UI
	if ( FrontendLoginPlus::is_modern_ui() ) {
		FrontendLoginPlus::enqueue_modern_ui_colors();
	}
	?>

	<div id="ult-forgot-password"<?php echo FrontendLoginPlus::is_modern_ui() ? ' class="' . esc_attr( FrontendLoginPlus::get_modern_ui_theme() ) . '"' : ''; ?>>
		<div class="ult-box">

			<div class="ult-form__content">
				<?php

				/**
				 * If the user submitted the form and the response was
				 * successful then we shouldn't show the fields again, only
				 * the success message
				 */

				if ( isset( $forgot_password_response ) && ! $forgot_password_response->error ) {
					?>
					
					<?php do_action( 'uo_forgot_before_title' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__title">
						<?php echo wp_kses_post( $innerText['Password-Recovery-Title'] ); ?>
					</div>
					
					<?php do_action( 'uo_forgot_before_success' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__row ult-form__row--validation">
						<div class="ult-notice ult-notice--success">
							<?php do_action( 'uo_forgot_before_success_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
							<?php echo wp_kses_post( $forgot_password_response->message ); ?>
							<?php do_action( 'uo_forgot_after_success_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
						</div>
					</div>
					
					<?php do_action( 'uo_forgot_after_success' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
				
				<?php } else { ?>
					
					<?php do_action( 'uo_forgot_before_title' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__title">
						<?php echo wp_kses_post( $innerText['Password-Recovery-Title'] ); ?>
					</div>
					
					<?php do_action( 'uo_forgot_before_description' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__description">
						<?php
						if ( ! isset( $forgot_password_response ) ) {
							echo wp_kses_post( $innerText['Password-Recovery-Desc'] );
						}
						?>
					</div>
					
					<?php do_action( 'uo_forgot_before_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

					<div class="ult-form__container">

						<form id="ult-forgot-password-form" name="lostpasswordform" action="<?php echo esc_url( site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="POST">

							<input type="hidden" name="redirect_to" value="<?php echo esc_url( $login_page_url . ( strpos( $login_page_url, '?' ) ? '&' : '?' ) ); ?>action=forgot&success=1">
							
							<?php do_action( 'uo_forgot_before_email' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

							<div class="ult-form__row ult-form__row--email">
								<div class="ult-form-field__header">
									<div class="ult-form-field__label-container">
										<label for="ult-forgot-email" class="ult-form-field__label">
											<?php echo wp_kses_post( $innerText['Password-Recovery-Label'] ); ?>
										</label>
									</div>
									<div class="ult-form-field__actions">
										<?php do_action( 'uo_forgot_email_actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
									</div>
								</div>

								<div class="ult-form__field">
									<input required type="text" name="user_login" id="ult-forgot-email" class="ult-form__input" placeholder="">
								</div>
							</div>
							
							<?php do_action( 'uo_forgot_before_captcha' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
							
							<?php if ( $has_recaptcha ) { ?>

								<div class="ult-form__row ult-form__row--recaptcha">
									<div class="ult-form-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_key ); ?>" data-callback="UncannyToolkitFrontendLoginReCaptchaCorrect"></div>
								</div>
							
							<?php } ?>
							
							<?php

							// Validation classes
							$notice_css_classes = array();

							// Add a CSS class if it has an error
							if ( isset( $forgot_password_response ) ) {
								$notice_css_classes[] = 'ult-form__validation--has-error';
							} else {
								$forgot_password_response = '';
							}

							?>
								
							<?php do_action( 'uo_forgot_before_error' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

							<div class="ult-form__validation <?php echo esc_attr( implode( ' ', $notice_css_classes ) ); ?>">
								<div class="ult-notice ult-notice--error">
									<?php do_action( 'uo_forgot_before_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

									<span class="ult-notice-text"><?php echo isset( $forgot_password_response->message ) ? wp_kses_post( $forgot_password_response->message ) : ''; ?></span>

									<?php do_action( 'uo_forgot_after_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
								</div>
							</div>
							
							<?php do_action( 'uo_forgot_before_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

							<div class="ult-form__row ult-form__row--submit">
								<button type="submit" id="ult-forgot-password-submit-btn" class="ult-form__submit-btn">
									<?php echo wp_kses_post( $innerText['Get-New-Password'] ); ?>
								</button>
							</div>
							
							<?php do_action( 'uo_forgot_after_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
						</form>

					</div>
					
					<?php do_action( 'uo_forgot_after_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
				
				<?php } ?>

				<?php do_action( 'uo_forgot_before_footer' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
			</div>

			<div class="ult-form__footer">
				<?php do_action( 'uo_forgot_before_login' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

				<div class="ult-form-footer__login">
					<a id="ult-form-footer-login" href="<?php echo esc_url( $login_page_url ); ?>">
						<?php echo wp_kses_post( $innerText['Log-in-instead'] ); ?>
					</a>
				</div>
			</div>

			<?php do_action( 'uo_forgot_after_footer' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>

		</div>
	</div>
	
	<?php do_action( 'uo_forgot_after_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
