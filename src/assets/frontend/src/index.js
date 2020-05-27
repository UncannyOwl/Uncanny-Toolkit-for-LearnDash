import {
	Login,
	ForgotPassword,
	ResetPassword
} from './js/FrontendLogin';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new Login();
	new ForgotPassword();
	new ResetPassword();
});