import Login from './js/Login';
import NewLogin from './js/NewLogin';
import ForgotPassword from './js/ForgotPassword';
import ResetPassword from './js/ResetPassword';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new Login();
	new NewLogin();
	new ForgotPassword();
	new ResetPassword();
});