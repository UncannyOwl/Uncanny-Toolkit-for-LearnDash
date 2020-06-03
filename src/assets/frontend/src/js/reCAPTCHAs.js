import {
	isDefined
} from './Utilities';

/**
 * Render reCAPTCHAs
 */

class reCAPTCHAs {
	constructor(){
		// Get elements
		this.whenReady(() => {
			// Render the reCAPTCHAs
			this.renderReCaptchas();
		});
	}

	whenReady( callback ){
		// Check if the reCAPTCHA script already loaded
		if ( isDefined( window.grecaptcha ) ){
			// Invoke the callback
			callback();
		}
		else {
			// Define a function that will be invoked once Google reCAPTCHA
			// is ready to use
			window.UncannyToolkit.reCaptchaInit = () => {
				// Invoke the callback
				callback();
			}
		}
	}

	renderReCaptchas(){
		// Get containers
		const $reCAPTCHAs = document.querySelectorAll( '.ult-form-recaptcha' );

		// Iterate the elements
		[ ...$reCAPTCHAs ].forEach(( $reCAPTCHA ) => {
			// Render the reCAPTCHA
			const id = window.grecaptcha.render( $reCAPTCHA, {
				sitekey:  $reCAPTCHA.getAttribute( 'data-sitekey' ),
				callback: $reCAPTCHA.getAttribute( 'data-callback' )
			});

			// Add the ID to the element so we can get the correct
			// validation token later
			$reCAPTCHA.setAttribute( 'data-id', id );
		});
	}
}

export default reCAPTCHAs;