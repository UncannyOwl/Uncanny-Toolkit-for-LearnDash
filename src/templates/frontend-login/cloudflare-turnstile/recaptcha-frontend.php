<?php
/**
 * @var array{`site_key`:string,`turnstile_response_null`:string} $params
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="uo-toolkit-turnstile-recaptcha" class="ult-form__row ult-form__row--recaptcha"></div>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback" defer></script>

<script>
	window.onloadTurnstileCallback = function () {
		let widgetId = turnstile.render('#uo-toolkit-turnstile-recaptcha', {
			sitekey: '<?php echo esc_js( $params['site_key'] ); ?>',
			theme: 'light',
			callback: function(token) {
				// Store the fresh, single-use token for the AJAX login request.
				UOToolkitFrontEndLoginFormData.turnstileRecaptcha = token;
			},
			// Token expired before it was used. Drop it and request a new one.
			'expired-callback': function() {
				UOToolkitFrontEndLoginFormData.turnstileRecaptcha = false;
				turnstile.reset( widgetId );
			},
			// Error handling.
			'error-callback': function( message ) {
				UOToolkitFrontEndLoginFormData.turnstileRecaptcha = false;
				const errorMessage = '<?php echo esc_html( $params['turnstile_response_null'] ); ?>';
				document.getElementById('uo-toolkit-turnstile-recaptcha').innerHTML = `<div class="ult-form__validation__DISABLED__ ult-notice ult-notice--error">
						<span class="ult-notice-text">
							<p class="login-msg">
								<strong>${errorMessage}</strong>
							</p>
						</span>
					</div>`;
			},
		});

		// Turnstile tokens are single-use. After a failed login/forgot-password
		// attempt the previous token is already spent, so reset the widget to
		// issue a fresh one before the user retries. Without this the spent token
		// is resent and Cloudflare rejects it ("error validating the form").
		['login', 'forgot-password'].forEach(function (formId) {
			document.addEventListener('uncanny-toolkit/frontend-login/' + formId + '/submitted', function (event) {
				if ( event.detail && false === event.detail.success ) {
					UOToolkitFrontEndLoginFormData.turnstileRecaptcha = false;
					turnstile.reset( widgetId );
				}
			});
		});
	};
</script>
