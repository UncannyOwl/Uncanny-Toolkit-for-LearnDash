import {
	isDefined
} from './Utilities';

class FrontendLogin {
	constructor(){
		// Get elements
		this.getElements();

		// Check if we have to invoke the methods
		if ( this.shouldInvokeMethods() ){
			// Bind form submission
			this.validateFormSubmission();
		}
	}

	getElements(){
		// Get the main elements
		this.$elements = {}

		// Define the main CSS classes
		this.cssClasses = {}
	}

	validateFormSubmission(){
		// Check if the form exists
		if ( isDefined( this.$elements.mainForm ) ){
			// Bind submission
			this.$elements.mainForm.onsubmit = ( event ) => {
				// Create a variable to evaluate if the form has errors later
				let canSubmit = true;

				// Check if the recaptcha is enabled, but the checkbox is not checked
				if ( UncannyToolkit.frontendLogin.hasReCAPTCHA && ! this.isCaptchaChecked() ){
					// Add a CSS class for the reCAPTCHA error
					this.$elements.container.classList.add( this.cssClasses.reCAPTCHAerror );

					// Update the error status
					canSubmit = false;

					console.error( '✋Frontend Login: The form is using reCAPTCHA, but you didn\'t pass the verification' );
				}

				// Check if we can submit the form
				if ( canSubmit ){
					// Submit the form
					this.submitForm();
				}
				else {
					// Prevent the form submission
					event.preventDefault();

					console.error( '✋Frontend Login: Submission prevented' );
				}
			};
		}
	}

	submitForm(){
		// Add the loading animation
		this.setLoadingStatus( true );

		// Disable the submit button
		this.setButtonDisableStatus( true );
	}

	setLoadingStatus( setAsLoading = true ){
		// Check if we have to add a loading animation to the submit button
		if ( UncannyToolkit.frontendLogin.ui.showAnimationOnSubmit ){
			// Check if we have to enable the loading status
			if ( setAsLoading ){
				// Add the loading animation
				this.$elements.container.classList.add( this.cssClasses.loading );
				this.$elements.submitButton.container.classList.add( this.cssClasses.btnLoading );
			}
			else {
				// Remove the loading status
				this.$elements.container.classList.remove( this.cssClasses.loading );
				this.$elements.submitButton.container.classList.remove( this.cssClasses.btnLoading );
			}
		}
	}

	setButtonDisableStatus( setAsDisabled = true ){
		// Check if we have to disable the submit button
		if ( UncannyToolkit.frontendLogin.ui.buttonDisabledOnSubmit ){
			// Check if we have to set it as disabled
			if ( setAsDisabled ){
				// Disable the submit button
				this.$elements.container.classList.add( this.cssClasses.disabled );
				this.$elements.submitButton.container.classList.add( this.cssClasses.btnDisabled );
				this.$elements.submitButton.button.setAttribute( 'disabled', true );
			}
			else {
				// Disable the submit button
				this.$elements.container.classList.remove( this.cssClasses.disabled );
				this.$elements.submitButton.container.classList.remove( this.cssClasses.btnDisabled );
				this.$elements.submitButton.button.setAttribute( 'disabled', false );
			}
		}
	}

    isCaptchaChecked(){
        return grecaptcha && grecaptcha.getResponse().length !== 0;
    }

    shouldInvokeMethods(){
    	return isDefined( this.$elements.container );
    }
}

export class Login extends FrontendLogin {
	getElements(){
		// Get the main elements
		this.$elements = {
			container: document.getElementById( 'ult-login' ),
			mainForm:  document.getElementById( 'ult-login-form' ),
			submitButton: {
				container: document.getElementsByClassName( 'login-submit' )[0],
				button: document.getElementById( 'ult-login-submit' )
			}
		}

		// Define the main CSS classes
		this.cssClasses = {
			reCAPTCHAerror: 'ult-login--recaptcha-error',
			loading:        'ult-login--loading',
			disabled:       'ult-login--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
		}
	}
}

export class ForgotPassword extends FrontendLogin {
	getElements(){
		// Get the main elements
		this.$elements = {
			container: document.getElementById( 'ult-forgot-password' ),
			mainForm:  document.getElementById( 'ult-forgot-password-form' ),
			submitButton: {
				container: document.querySelector( '.ult-form__row--submit' ),
				button: document.getElementById( 'ult-forgot-password-submit-btn' )
			}
		}

		// Define the main CSS classes
		this.cssClasses = {
			reCAPTCHAerror: 'ult-forgot-password--recaptcha-error',
			loading:        'ult-forgot-password--loading',
			disabled:       'ult-forgot-password--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
		}
	}
}

export class ResetPassword extends FrontendLogin {
	getElements(){
		// Get the main elements
		this.$elements = {
			container: document.getElementById( 'ult-reset-password' ),
			mainForm:  document.getElementById( 'ult-reset-password-form' ),
			submitButton: {
				container: document.querySelector( '.ult-form__row--submit' ),
				button: document.getElementById( 'ult-reset-password-submit-btn' )
			}
		}

		// Define the main CSS classes
		this.cssClasses = {
			reCAPTCHAerror: 'ult-reset-password--recaptcha-error',
			loading:        'ult-reset-password--loading',
			disabled:       'ult-reset-password--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
		}
	}
}