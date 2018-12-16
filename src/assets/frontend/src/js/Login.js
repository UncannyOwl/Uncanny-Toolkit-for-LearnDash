import {
	isDefined
} from './Utilities';

class Login {
	constructor(){
		// Get elements
		this.getElements();

		// Get configuration
		this.getConfiguration();

		// Bind form submission
		this.bindFormSubmission();
	}

	getElements(){
		// Create elements property
		this.$elements = {}

		// Get elements
		this.$elements.loginForm    = document.getElementById( 'ult-login-form' );
		this.$elements.submitButton = document.getElementById( 'ult-login-submit-btn' );
	}

	getConfiguration(){
		// Define default configuration
		this.configuration = {
			showAnimationOnSubmit: true,
			buttonDisabledOnSubmit: true
		}

		// Check if the user wants to overwrite some settings
		if ( isDefined( window.ULT_LoginConfig ) ){

			// Check if the user wants to disable the loading animation
			if ( isDefined( window.ULT_LoginConfig.loadingAnimationOnSubmit ) ){
				// Set new value
				this.configuration.showAnimationOnSubmit = window.ULT_LoginConfig.loadingAnimationOnSubmit;
			}

			// Check if the user wants to disable the button on submit
			if ( isDefined( window.ULT_LoginConfig.buttonDisabledOnSubmit ) ){
				// Set new value
				this.configuration.buttonDisabledOnSubmit = window.ULT_LoginConfig.buttonDisabledOnSubmit;
			}

		}
	}

	bindFormSubmission(){
		// Bind submission
		this.$elements.loginForm.onsubmit = ( event ) => {
			event.preventDefault();

			alert( 'Hello there' );
			/**
			 * To disable the loading animation create a ULT_LoginConfig object
			 * with a loadingAnimationOnSubmit property and set it to false.
			 * ULT_LoginConfig.loadingAnimationOnSubmit = false;
			 */
			
			if ( this.configuration.showAnimationOnSubmit ){
				this.$elements.submitButton.classList.add( 'ult-login-form-row__btn--loading' );
			}

			/**
			 * To prevent the button to become disabled on submitcreate a ULT_LoginConfig object
			 * with a buttonDisabledOnSubmit property and set it to false.
			 * ULT_LoginConfig.buttonDisabledOnSubmit = false;
			 */
			
			if ( this.configuration.buttonDisabledOnSubmit ){
				this.$elements.submitButton.classList.add( 'ult-login-form-row__btn--disabled' );
			}
		};
	}
}

export default Login;