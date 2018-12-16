import Login from './js/Login';
import ResetPassword from './js/ResetPassword';

import './scss/main.scss';

// Do on DOM ready
document.addEventListener( 'DOMContentLoaded', () => {
	new Login();
	new ResetPassword();
});