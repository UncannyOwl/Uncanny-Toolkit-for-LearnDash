import Login from './js/Login';
import ForgotPassword from './js/ForgotPassword';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new Login();
	new ForgotPassword();
});