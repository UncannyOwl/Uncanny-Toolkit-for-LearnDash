import {
	isDefined
} from './Utilities';

class ResetPassword {
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
		this.$elements.resetForm    = document.getElementById( 'ult-reset-password' );
		this.$elements.submitButton = document.getElementById( 'ult-reset-password-submit-btn' );
	}

	getConfiguration(){
		// Define default configuration
		this.configuration = {
			showAnimationOnSubmit: true,
			buttonDisabledOnSubmit: true
		}

		// Check if the user wants to overwrite some settings
		if ( isDefined( window.ULT_ResetConfig ) ){

			// Check if the user wants to disable the loading animation
			if ( isDefined( window.ULT_ResetConfig.loadingAnimationOnSubmit ) ){
				// Set new value
				this.configuration.showAnimationOnSubmit = window.ULT_ResetConfig.loadingAnimationOnSubmit;
			}

			// Check if the user wants to disable the button on submit
			if ( isDefined( window.ULT_ResetConfig.buttonDisabledOnSubmit ) ){
				// Set new value
				this.configuration.buttonDisabledOnSubmit = window.ULT_ResetConfig.buttonDisabledOnSubmit;
			}

		}
	}

	bindFormSubmission(){
		// Check if the form exists
		if ( isDefined( this.$elements.resetForm ) ){
			// Bind submission
			this.$elements.resetForm.onsubmit = ( event ) => {
				/**
				 * To disable the loading animation create a ULT_ResetConfig object
				 * with a loadingAnimationOnSubmit property and set it to false.
				 * ULT_ResetConfig.loadingAnimationOnSubmit = false;
				 */
				
				if ( this.configuration.showAnimationOnSubmit ){
					this.$elements.submitButton.classList.add( 'ult-form__submit-btn--loading' );
				}

				/**
				 * To prevent the button to become disabled on submitcreate a ULT_ResetConfig object
				 * with a buttonDisabledOnSubmit property and set it to false.
				 * ULT_ResetConfig.buttonDisabledOnSubmit = false;
				 */
				
				if ( this.configuration.buttonDisabledOnSubmit ){
					this.$elements.submitButton.classList.add( 'ult-form__submit-btn--disabled' );
				}
			};
		}
	}
}

export default ResetPassword;