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
				const turnstileResponse = turnstile.getResponse( widgetId );
				// Updated the form datam.
				UOToolkitFrontEndLoginFormData.turnstileRecaptcha = token;
			},
			// Error handling.
			'error-callback': function( message ) {
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
	};
</script>
