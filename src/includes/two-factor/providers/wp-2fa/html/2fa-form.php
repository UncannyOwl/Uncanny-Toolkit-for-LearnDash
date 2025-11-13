<?php
/**
 * 2FA Authentication Form Template
 *
 * @package uncanny-learndash-toolkit
 * @var array $form_data The 2FA form data
 */

defined( 'ABSPATH' ) || exit;

// Display error message if present.
if ( isset( $_GET['2fa_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	echo '<div class="ult-notice ult-notice--error">';
	echo '<span class="ult-notice-text">' . esc_html( urldecode( sanitize_text_field( wp_unslash( $_GET['2fa_error'] ) ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	echo '</div>';
}
?>

<div class="ult-two-factor-container">
	<div class="ult-two-factor-form">
		<div class="ult-two-factor-content">
			<div class="ult-two-factor-header">
				<h3 class="ult-two-factor-title">
					<?php echo esc_html( $form_data['strings']['twoFactorTitle'] ); ?>
				</h3>
				
				<div class="ult-two-factor-instructions">
					<p><?php echo esc_html( $form_data['instructions'] ); ?></p>
				</div>
			</div>
			
			<div class="ult-two-factor-body">
				<!-- Primary Method Form -->
				<div id="ult-two-factor-primary-method">
					<form method="post" class="ult-two-factor-form">
						<div class="ult-two-factor-row">
							<div class="ult-two-factor-field">
								<label for="ult-two-factor-code" class="ult-two-factor-label">
									<?php echo esc_html( $form_data['strings']['enterCode'] ); ?>
								</label>
								<input 
									type="text" 
									name="authcode" 
									id="ult-2fa-code" 
									class="ult-form__input" 
									maxlength="6" 
									pattern="[0-9]{6}" 
									placeholder="<?php echo esc_attr( $form_data['strings']['codePlaceholder'] ); ?>"
									autocomplete="one-time-code"
									required
								>
							</div>
						</div>
						
						<div class="ult-form__row ult-form__row--submit">
							<button type="submit" class="ult-form__submit-btn button" id="ult-2fa-submit">
								<?php echo esc_html( $form_data['strings']['verifyCode'] ); ?>
							</button>
						</div>
						
						<!-- Hidden fields for form submission -->
						<input type="hidden" name="2fa_action" value="verify_primary">
						
						<div class="ult-form__validation">
							<div class="ult-notice-text" role="alert" aria-live="polite"></div>
						</div>
					</form>
				</div>
				
				<!-- Backup Method Form -->
				<?php if ( ! empty( $form_data['backup_methods'] ) ) : ?>
				<div id="ult-2fa-backup-method" style="display: none;">
					<form method="post" class="ult-form__form">
						<div class="ult-form__row">
							<div class="ult-form__field">
								<label for="ult-2fa-backup-code" class="ult-form__label">
									<?php echo esc_html( $form_data['strings']['backupCodeLabel'] ); ?>
								</label>
								<input 
									type="text" 
									name="backupcode" 
									id="ult-2fa-backup-code" 
									class="ult-form__input" 
									placeholder="<?php echo esc_attr( $form_data['strings']['backupCodePlaceholder'] ); ?>"
									autocomplete="off"
								>
							</div>
						</div>
						
						<div class="ult-form__row ult-form__row--submit">
							<button type="submit" class="ult-form__submit-btn button" id="ult-2fa-backup-submit">
								<?php echo esc_html( $form_data['strings']['verifyCode'] ); ?>
							</button>
						</div>
						
						<!-- Hidden fields for form submission -->
						<input type="hidden" name="2fa_action" value="verify_backup">
						
						<div class="ult-form__validation">
							<div class="ult-notice-text" role="alert" aria-live="polite"></div>
						</div>
					</form>
				</div>
				<?php endif; ?>
			</div>
			
			<!-- Navigation Footer -->
			<div class="ult-form__footer">
				<?php if ( ! empty( $form_data['backup_methods'] ) ) : ?>
					<button type="button" class="ult-form__link button" id="ult-2fa-use-backup">
						<?php echo esc_html( $form_data['strings']['useBackupCode'] ); ?>
					</button>
					<button type="button" class="ult-form__link button" id="ult-2fa-use-primary" style="display: none;">
						<?php echo esc_html( $form_data['strings']['usePrimaryMethod'] ); ?>
					</button>
				<?php endif; ?>
				<form method="post" style="display: inline;">
					<input type="hidden" name="2fa_action" value="go_back">
					<button type="submit" class="ult-form__link button" id="ult-2fa-back">
						<?php echo esc_html( $form_data['strings']['goBack'] ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript for backup code toggle -->
<script type="text/javascript">
(function() {
	'use strict';
	
	// Wait for DOM to be ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init2FAToggle);
	} else {
		init2FAToggle();
	}
	
	function init2FAToggle() {
		const primaryMethod = document.getElementById('ult-2fa-primary-method');
		const backupMethod = document.getElementById('ult-2fa-backup-method');
		const useBackupBtn = document.getElementById('ult-2fa-use-backup');
		const usePrimaryBtn = document.getElementById('ult-2fa-use-primary');
		
		// Early exit if elements don't exist
		if (!primaryMethod || !backupMethod || !useBackupBtn || !usePrimaryBtn) {
			return;
		}
		
		// Show backup method
		useBackupBtn.addEventListener('click', function() {
			primaryMethod.style.display = 'none';
			backupMethod.style.display = 'block';
			useBackupBtn.style.display = 'none';
			usePrimaryBtn.style.display = 'inline-block';
			
			// Focus on backup code input
			const backupInput = document.getElementById('ult-2fa-backup-code');
			if (backupInput) {
				backupInput.focus();
			}
		});
		
		// Show primary method
		usePrimaryBtn.addEventListener('click', function() {
			primaryMethod.style.display = 'block';
			backupMethod.style.display = 'none';
			useBackupBtn.style.display = 'inline-block';
			usePrimaryBtn.style.display = 'none';
			
			// Focus on primary code input
			const primaryInput = document.getElementById('ult-2fa-code');
			if (primaryInput) {
				primaryInput.focus();
			}
		});
	}
})();
</script>
