<?php

/**
 * Register Front End Login
 * render it with a callback function
 */


register_block_type( 'uncanny-toolkit/login-uncanny', [
	'render_callback' => 'render_frontend_login'
] );

function render_frontend_login() {

	// Start output
	ob_start();

	// Check if the class exists
	if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
		echo \uncanny_learndash_toolkit\FrontendLoginPlus::uo_login_ui();
	}

	// Get output
	$output = ob_get_clean();

	// Return output
	return $output;
}