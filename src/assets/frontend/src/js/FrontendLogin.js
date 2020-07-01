const events = require( 'eventslibjs' );

import {
	AJAXRequest,
	isEmpty,
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

			// Handle reCAPTCHA changes
			this.handleReCaptchaChanges();
		}
	}

	getElements(){
		// Get the main elements
		this.$elements = {}

		// Define the main CSS classes
		this.cssClasses = {}
	}

	get formData(){
		return {}
	}

	get ajaxAction(){
		return '';
	}

	validateFormSubmission(){
		// Check if the form exists
		if ( isDefined( this.$elements.mainForm ) ){
			// Bind submission
			this.$elements.mainForm.onsubmit = ( event ) => {
				// Create a variable to evaluate if the form has errors later
				let canSubmit = true;

				// Check if the recaptcha is enabled, but the checkbox is not checked
				if ( this.hasReCAPTCHA() && ! this.isCaptchaChecked() ){
					// Add a CSS class for the reCAPTCHA error
					this.$elements.container.classList.add( this.cssClasses.reCAPTCHAerror );

					// Update the error status
					canSubmit = false;

					// Set frontend error
					this.setError( UncannyToolkit.frontendLogin.i18n.checkReCaptcha );

					console.error( '✋Frontend Login: The form is using reCAPTCHA, but you didn\'t pass the verification' );
				}

				// Check if we can submit the form
				if ( canSubmit ){
					// Submit the form
					this.submitForm( event );
				}
				else {
					// Prevent the form submission
					event.preventDefault();

					console.error( '✋Frontend Login: Submission prevented' );
				}
			};
		}
	}

	submitForm( event ){
		// Hide errors
		this.setError( false );

		// Add the loading animation
		this.setLoadingStatus( true );

		// Disable the submit button
		this.setButtonDisableStatus( true );

		// Check if we have to use AJAX
		if ( UncannyToolkit.frontendLogin.hasAjaxEnabled ){
			// Prevent the form submission
			event.preventDefault();

			// Do AJAX call
			AJAXRequest( this.ajaxAction, this.formData, ( response ) => {
				// Remove the loading animation
				this.setLoadingStatus( false );

				// Enable the submit button
				this.setButtonDisableStatus( false );

				// Check if the call was really successful
				if ( response.success ){
					this.afterValidSubmission( response );
				}
				else {
					// Check if there is a message
					if ( isDefined( response.message ) ){
						// Show the error
						this.setError( response.message );
					}
					else {
						// Set a generic error message
						this.setError( UncannyToolkit.i18n.error.generic );
					}

					// Reset reCAPTCHA
					this.resetReCAPTCHA();
				}
			}, ( response ) => {
				// Check if there is a message
				if ( isDefined( response.message ) ){
					// Show the error
					this.setError( response.message );
				}
				else {
					// Set a generic error message
					this.setError( UncannyToolkit.i18n.error.generic );
				}

				// Reset reCAPTCHA
				this.resetReCAPTCHA();

				// Remove the loading animation
				this.setLoadingStatus( false );

				// Enable the submit button
				this.setButtonDisableStatus( false );
			});
		}
	}

	afterValidSubmission( response ){}

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
				this.$elements.submitButton.button.removeAttribute( 'disabled' );
			}
		}
	}

	setError( error = false ){
		// Check if the error is defined
		if ( ! isEmpty( error ) ){
			// Set the class to show the notice
			this.$elements.errorNotice.container.classList.add( this.cssClasses.noticeHasError );

			// Set the error message
			this.$elements.errorNotice.textNode.textContent = error;
		}
		else {
			// Set the class to hide the notice
			this.$elements.errorNotice.container.classList.remove( this.cssClasses.noticeHasError );

			// Set the error message
			this.$elements.errorNotice.textNode.textContent = '';

			// Remove a CSS class for the reCAPTCHA error
			this.$elements.container.classList.remove( this.cssClasses.reCAPTCHAerror );
		}
	}

	handleReCaptchaChanges(){
		// Add global method to handle what happened when
		// the reCAPTCHA is checked
		UncannyToolkit.frontendLogin.reCaptcha = {
			correct: ( response ) => {
				// Reset errors
				this.setError( false );
			}
		}
	}

	hasReCAPTCHA(){
		return isDefined( this.$elements.formFields.reCAPTCHA );
	}

	getReCAPTCHAResponse(){
		// Get the reCAPTCHA ID
		const id = this.$elements.formFields.reCAPTCHA.getAttribute( 'data-id' );

		// Get the response
		return grecaptcha.getResponse( id );
	}

	resetReCAPTCHA(){
		try {
			// Get the reCAPTCHA ID
			const id = this.$elements.formFields.reCAPTCHA.getAttribute( 'data-id' );

			// Reset the reCAPTCHA
			grecaptcha.reset( id );
		} catch ( e ){}
	}

	isOnModal(){
		return isDefined( UncannyToolkit.modals[ 'ult-login' ] );
	}

    isCaptchaChecked(){
        return ! isEmpty( this.getReCAPTCHAResponse() );
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
			},
			errorNotice: {
				container: document.querySelector( '#ult-login .ult-form__validation' ),
				textNode: document.querySelector( '#ult-login .ult-notice-text' ),
			},
			formFields: {
				email: document.getElementById( 'ult-login-email' ),
				password: document.getElementById( 'ult-login-password' ),
				redirectTo: document.querySelector( '#ult-login .login-submit input[name="redirect_to"]' ),
				reCAPTCHA: document.querySelector( '#ult-login .ult-form-recaptcha' ),
				rememberMe: document.getElementById( 'ult-login-remember' )
			}
		}
	}

	// Define the AJAX action ID
	get ajaxAction(){
		return 'ult-login';
	}

	get cssClasses(){
		return {
			reCAPTCHAerror: 'ult-login--recaptcha-error',
			loading:        'ult-login--loading',
			disabled:       'ult-login--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
			noticeHasError: 'ult-form__validation--has-error' 
		};
	}

	get formData(){
		// Get the form data
		const formData = {
			email: this.$elements.formFields.email.value,
			password: this.$elements.formFields.password.value,
			redirectTo: this.$elements.formFields.redirectTo.value,
		}

		// Check if it has the "Remember me" checkbox
		if ( isDefined( this.$elements.formFields.rememberMe ) ){
			formData.rememberMe = this.$elements.formFields.rememberMe.checked;
		}

		// Check if it has a reCAPTCHA
		if ( this.hasReCAPTCHA() ){
			formData.reCAPTCHA = this.getReCAPTCHAResponse();
		}

		return formData;
	}

	afterValidSubmission( response ){
		// Show the loading animation again, we're going 
		// to redirect them to another page and it might
		// take some seconds

		// Add the loading animation
		this.setLoadingStatus( true );

		// Disable the submit button
		this.setButtonDisableStatus( true );

		// Reload the page
		if ( ! this.isOnModal() && ! isEmpty( response.redirectTo )){
			window.location.href = response.redirectTo;
		}
		else {
			window.location.reload();
		}
	}
}

export class ForgotPassword extends FrontendLogin {
	getElements(){
		// Get the main elements
		this.$elements = {
			container: document.getElementById( 'ult-forgot-password' ),
			boxContent: document.querySelector( '#ult-forgot-password .ult-form__content' ),
			mainForm:  document.getElementById( 'ult-forgot-password-form' ),
			submitButton: {
				container: document.querySelector( '.ult-form__row--submit' ),
				button: document.getElementById( 'ult-forgot-password-submit-btn' )
			},
			errorNotice: {
				container: document.querySelector( '#ult-forgot-password .ult-form__validation' ),
				textNode: document.querySelector( '#ult-forgot-password .ult-notice-text' ),
			},
			formFields: {
				email: document.getElementById( 'ult-forgot-email' ),
				reCAPTCHA: document.querySelector( '#ult-forgot-password .ult-form-recaptcha' ),
			}
		}
	}

	// Define the AJAX action ID
	get ajaxAction(){
		return 'ult-forgot-password';
	}

	get cssClasses(){
		return {
			reCAPTCHAerror: 'ult-forgot-password--recaptcha-error',
			loading:        'ult-forgot-password--loading',
			disabled:       'ult-forgot-password--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
			noticeHasError: 'ult-form__validation--has-error' 
		};
	}

	get formData(){
		// Get the form data
		const formData = {
			email: this.$elements.formFields.email.value
		}

		// Check if it has a reCAPTCHA
		if ( this.hasReCAPTCHA() ){
			formData.reCAPTCHA = this.getReCAPTCHAResponse();
		}

		return formData;
	}

	afterValidSubmission( response ){
		// Show a confirmation message
		this.$elements.boxContent.innerHTML = response.message;
	}
}

export class ResetPassword extends FrontendLogin {
	getElements(){
		// Get the main elements
		this.$elements = {
			container: document.getElementById( 'ult-reset-password' ),
			boxContent: document.querySelector( '#ult-reset-password .ult-form__content' ),
			mainForm:  document.getElementById( 'ult-reset-password-form' ),
			submitButton: {
				container: document.querySelector( '.ult-form__row--submit' ),
				button: document.getElementById( 'ult-reset-password-submit-btn' )
			},
			errorNotice: {
				container: document.querySelector( '#ult-reset-password .ult-form__validation' ),
				textNode: document.querySelector( '#ult-reset-password .ult-notice-text' ),
			},
			formFields: {
				password: document.getElementById( 'ult-reset-new-password' ),
				passwordRepeat: document.getElementById( 'ult-reset-confirm-password' ),
				reCAPTCHA: document.querySelector( '#ult-reset-password .ult-form-recaptcha' ),
			}
		}
	}

	// Define the AJAX action ID
	get ajaxAction(){
		return 'ult-reset-password';
	}

	get cssClasses(){
		return {
			reCAPTCHAerror: 'ult-reset-password--recaptcha-error',
			loading:        'ult-reset-password--loading',
			disabled:       'ult-reset-password--disabled',
			btnLoading:     'ult-form__submit-btn--loading',
			btnDisabled:    'ult-form__submit-btn--disabled',
			noticeHasError: 'ult-form__validation--has-error' 
		};
	}

	get formData(){
		// Get the form data
		const formData = {
			password: this.$elements.formFields.password.value,
			passwordRepeat: this.$elements.formFields.passwordRepeat.value,
		}

		// Check if it has a reCAPTCHA
		if ( this.hasReCAPTCHA() ){
			formData.reCAPTCHA = this.getReCAPTCHAResponse();
		}

		return formData;
	}

	afterValidSubmission( response ){
		// Show a confirmation message
		this.$elements.boxContent.innerHTML = response.message;
	}
}

/**
 * This class handles the login functionality when 
 */
export class FrontendLogin_Modal {
	constructor(){
		// Check if we should invoke the callbacks
		if ( this.shouldInvokeMethods() ){
			// Get elements
			this.getElements();

			// Handle clicks to the buttons to show the sections
			this.handleContainerVisiblity();
		}
		else {
			// Check if the current page is the Login page
			// That would mean that the modal is not defined here
			// We want to reload the page when they click on the "Login" link
			if ( isDefined( UncannyToolkit.frontendLogin.currentPageIsLoginPage ) &&  UncannyToolkit.frontendLogin.currentPageIsLoginPage ){
				this.handleModalInLoginPage();
			}
		}
	}

	getElements(){
		this.$elements = {
			container: document.getElementById( 'ult-login-modal' ),
			loginShowButton: document.getElementById( 'ult-form-footer-login' ),
			forgotPasswordShowButton: document.getElementById( 'ult-form-footer-forgot-password' ),
		}
	}

	handleContainerVisiblity(){
		if ( isDefined( this.$elements.forgotPasswordShowButton ) ){
			// Listen clicks to the button to show the Forgot Password section
			this.$elements.forgotPasswordShowButton.addEventListener( 'click', ( event ) => {
				// Prevent the event, we don't want to send them to another page
				event.preventDefault();

				// Show the Forgot Password form
				this.showSection( 'forgot-password' );
			});
		}
		
		if ( isDefined( this.$elements.loginShowButton ) ){
			// Listen clicks to the button to show the Log in section
			this.$elements.loginShowButton.addEventListener( 'click', ( event ) => {
				// Prevent the event, we don't want to send them to another page
				event.preventDefault();

				// Show the Forgot Password form
				this.showSection( 'login' );
			});
		}
	}

	showSection( section = 'login' ){
		// Remove both classes. We'll add the correct one later
		this.$elements.container.classList.remove( 'ult-login-modal--login', 'ult-login-modal--forgot-password' );

		// Show and hide sections
		if ( section == 'login' ){
			this.$elements.container.classList.add( 'ult-login-modal--login' );
		}
		else if ( section == 'forgot-password' ){
			this.$elements.container.classList.add( 'ult-login-modal--forgot-password' );
		}
	}

	shouldInvokeMethods(){
		return isDefined( UncannyToolkit.frontendLogin ) && isDefined( UncannyToolkit.frontendLogin.currentPageIsLoginPage ) && ! UncannyToolkit.frontendLogin.currentPageIsLoginPage && isDefined( UncannyToolkit.modals[ 'ult-login' ] );
	}

	handleModalInLoginPage(){
		events.on( 'click', `[href*="ult-modal-open----ult-login"]`, ( event ) => {
			// Prevent default
			event.preventDefault();

			// Reload the page
			location.reload();
		});
	}
}