import 'whatwg-fetch';
import 'url-search-params-polyfill';

import {
	Login,
	ForgotPassword,
	ResetPassword
} from './js/FrontendLogin';

import reCAPTCHAs from './js/reCAPTCHAs';

import Modals from './js/Modals';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new reCAPTCHAs();

	new Modals();

	new Login();
	new ForgotPassword();
	new ResetPassword();
});