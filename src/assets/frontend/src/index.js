import {
	Login,
	ForgotPassword,
	ResetPassword
} from './js/FrontendLogin';

import Modals from './js/Modals';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new Modals();

	new Login();
	new ForgotPassword();
	new ResetPassword();
});