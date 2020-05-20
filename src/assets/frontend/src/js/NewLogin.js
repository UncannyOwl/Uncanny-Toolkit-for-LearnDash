import {isDefined} from './Utilities';

class NewLogin {
    constructor(){

        if ( !isDefined( uo_login.is_ajax ) ) {
            return;
        }

        // Get elements
        this.getElements();

        // Get configuration
        this.getConfiguration();

        // Bind using Jquery
        this.bindButtons();

        // Bind form submission
        this.bindFormSubmission();

        // Bind form submission
        this.bindLostFormSubmission();

        // Bind submit container
        this.bindSubmitContainer();

        // Bind close button
        this.bindCloseButton();
    }

    getElements(){
        // Create elements property
        this.$elements = {}

        // Get elements
        this.$elements.loginForm        = document.getElementById( 'ult-login-form' );

        if( isDefined( document.getElementById('ult-forgot-password-form') ) ){
            this.$elements.lostPswForm      = document.getElementById( 'ult-forgot-password-form' );
        } else if( isDefined( document.getElementById('lostpasswordform') ) ){
            this.$elements.lostPswForm      = document.getElementById( 'lostpasswordform' );
        }
        this.$elements.recaptchaElement = document.getElementsByClassName( 'ult-form__row--recaptcha' )[0];
        this.$elements.submitButton     = document.getElementsByClassName( 'login-submit' )[0];
        if (isDefined(document.getElementById('wp-submit'))) {
            this.$elements.lostPassButton = document.getElementById('wp-submit');
        } else if (isDefined(document.getElementById('ult-forgot-password-submit-btn'))) {
            this.$elements.lostPassButton = document.getElementById('ult-forgot-password-submit-btn');
        }
        this.$elements.loginFormError   = document.getElementById( 'ult_error_container' );
        this.$elements.lostPassFormError   = document.getElementById( 'ult_lp_error_container' );
        this.$elements.loginModalCloser = document.getElementsByClassName( 'ult-modal-closer' )[0];
        this.$elements.loginModal       = document.getElementsByClassName( 'ult-wrapper-login-modal' )[0];
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

    bindButtons(){
        var _self = this;
        jQuery('body').on('click', 'a[href="#uo_login"]', function(e) {
            e.preventDefault();
            if( uo_login.on_login_page == false ) {
                _self.showLostPassBox('login');
                _self.openLoginModal();
            }
        });

        if( uo_login.on_login_page == false ) {
            jQuery('body').on('click', 'a.forgot-link', function (e) {
                e.preventDefault();
                _self.showLostPassBox();
            });

            jQuery('body').on('click', '.ult-form-footer__forgot-password a', function (e) {
                e.preventDefault();
                _self.showLostPassBox();
            });
        }
    }

    bindFormSubmission(){
        // Check if the form exists
        if ( isDefined( this.$elements.loginForm ) ){
            // Bind submission
            this.$elements.loginForm.onsubmit = ( event ) => {
                event.preventDefault();
                this.$elements.loginFormError.classList.add( 'ult-hide' );

                if( typeof ( grecaptcha ) !== 'undefined' && isCaptchaChecked ){

                }

                /**
                 * To disable the loading animation create a ULT_LoginConfig object
                 * with a loadingAnimationOnSubmit property and set it to false.
                 * ULT_LoginConfig.loadingAnimationOnSubmit = false;
                 */

                if ( this.configuration.showAnimationOnSubmit ){
                    this.$elements.submitButton.classList.add( 'ult-form__submit-btn--loading' );
                }

                /**
                 * To prevent the button to become disabled on submitcreate a ULT_LoginConfig object
                 * with a buttonDisabledOnSubmit property and set it to false.
                 * ULT_LoginConfig.buttonDisabledOnSubmit = false;
                 */

                if ( this.configuration.buttonDisabledOnSubmit ){
                    this.$elements.submitButton.classList.add( 'ult-form__submit-btn--disabled' );
                }

                var $_self = this;
                var formData = jQuery( this.$elements.loginForm ).serializeArray();
                formData.push({'name': 'action', 'value': 'uo_login', 'on_login_page' : uo_login.on_login_page});
                jQuery.ajax({
                    type: 'POST',
                    url: uo_login.ajax_url,
                    data: formData,
                    dataType: 'json',
                }).done(function (data, status, xhr) {
                    $_self.$elements.submitButton.classList.remove('ult-form__submit-btn--loading');
                    $_self.$elements.submitButton.classList.remove('ult-form__submit-btn--disabled');
                    if (data.error) {
                        $_self.$elements.loginFormError.classList.remove('ult-hide');
                        $_self.$elements.loginFormError.firstElementChild.innerHTML = data.error_message;
                    } else {
                        $_self.$elements.loginFormError.classList.add('ult-hide');
                        $_self.$elements.loginFormError.firstElementChild.innerHTML = '';
                        if( uo_login.on_login_page == true && data.redirect_to){
                            window.location = data.redirect_to;
                        } else {
                            window.location.reload();
                        }

                    }
                }).fail(function (xhr, status, error) {

                });
            };
        }
    }

    bindLostFormSubmission(){
        // Check if the form exists
        if ( isDefined( this.$elements.lostPswForm ) ){
            // Bind submission
            this.$elements.lostPswForm.onsubmit = ( event ) => {
                event.preventDefault();
                this.$elements.lostPassFormError.classList.add( 'ult-hide' );

                if( typeof ( grecaptcha ) !== 'undefined' && isCaptchaChecked ){

                }

                /**
                 * To disable the loading animation create a ULT_LoginConfig object
                 * with a loadingAnimationOnSubmit property and set it to false.
                 * ULT_LoginConfig.loadingAnimationOnSubmit = false;
                 */

                if ( this.configuration.showAnimationOnSubmit ){
                    this.$elements.lostPassButton.classList.add( 'ult-form__submit-btn--loading' );
                }

                /**
                 * To prevent the button to become disabled on submitcreate a ULT_LoginConfig object
                 * with a buttonDisabledOnSubmit property and set it to false.
                 * ULT_LoginConfig.buttonDisabledOnSubmit = false;
                 */

                if ( this.configuration.buttonDisabledOnSubmit ){
                    this.$elements.lostPassButton.classList.add( 'ult-form__submit-btn--disabled' );
                }

                var $_self = this;
                var formData = jQuery( this.$elements.lostPswForm ).serializeArray();
                formData.push({'name': 'action', 'value': 'uo_lostPass'});
                jQuery.ajax({
                    type: 'POST',
                    url: uo_login.ajax_url,
                    data: formData,
                    dataType: 'json',
                }).done(function (data, status, xhr) {
                    $_self.$elements.lostPassButton.classList.remove('ult-form__submit-btn--loading');
                    $_self.$elements.lostPassButton.classList.remove('ult-form__submit-btn--disabled');
                    if (data.error) {
                        $_self.$elements.lostPassFormError.classList.remove('ult-hide');
                        $_self.$elements.lostPassFormError.firstElementChild.innerHTML = data.message;
                    } else {
                        $_self.$elements.lostPswForm.innerHTML = data.message;

                    }
                }).fail(function (xhr, status, error) {

                });
            };
        }
    }

    bindCloseButton(){
        // Check if the form exists
        if ( isDefined( this.$elements.loginModalCloser ) ) {
            this.$elements.loginModalCloser.onmousedown = (event) => {
                this.closeLoginModal();
            }
        }
    }

    bindSubmitContainer(){
        // Check if the form exists
        if ( isDefined( this.$elements.submitButton ) ) {
            // Bind submission
            this.$elements.submitButton.onmousedown = (event) => {
                if ( typeof ( grecaptcha ) !== 'undefined' && 0 === this.isCaptchaChecked() ) {
                    this.$elements.recaptchaElement.classList.add( 'ult-form__row--recaptcha-error' );
                }
            }
        }
    }

    openLoginModal() {
        if ( isDefined( this.$elements.loginModal ) ) {
            this.$elements.loginModal.classList.add('ult-modal-open');
            this.$elements.loginModal.classList.remove('ult-modal-closed');

            jQuery('html, body').animate({
                scrollTop: jQuery('.ult-modal', jQuery(this.$elements.loginModal)).offset().top
            }, 50);
        }
    }

    closeLoginModal() {
        if ( isDefined( this.$elements.loginModal ) ) {
            this.$elements.loginModal.classList.remove('ult-modal-open');
            this.$elements.loginModal.classList.add('ult-modal-closed');
        }
    }

    isCaptchaChecked(){
        return grecaptcha && grecaptcha.getResponse().length !== 0;
    }

    showLostPassBox(show){
        if('login' == show ){
            document.getElementById('ult-last-pwd').classList.add('ult-hide');
            document.getElementById('ult-login').classList.remove('ult-hide');
        } else {
            document.getElementById('ult-login').classList.add('ult-hide');
            document.getElementById('ult-last-pwd').classList.remove('ult-hide');
        }
    }
}

export default NewLogin;