import 'whatwg-fetch';
import 'url-search-params-polyfill';

import {
	Login,
	ForgotPassword,
	ResetPassword,
	FrontendLogin_Modal
} from './js/FrontendLogin';

import reCAPTCHAs from './js/reCAPTCHAs';
import Modals from './js/Modals';
import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	// Modals (global module)
	new reCAPTCHAs();

	// Modals (global module)
	new Modals();

	// Frontend Login
	new Login();
	new ForgotPassword();
	new ResetPassword();

	// Exceptions for the login form when
	// it's used on a modal
	new FrontendLogin_Modal();
});