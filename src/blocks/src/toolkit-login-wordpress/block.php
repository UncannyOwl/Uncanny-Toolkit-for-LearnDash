<?php 

/**
 * Register WordPress Login Form
 * render it with a callback function
 */

register_block_type( 'uncanny-toolkit/login-wordpress', [
	'render_callback' => 'render_login_wordpress'
]);

function render_login_wordpress( $attributes ){
	// Start output
	ob_start();

	// Check if the class exists
	if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ){
		echo \uncanny_learndash_toolkit\FrontendLoginPlus::uo_login_form();
	}	

	// Get output
	$output = ob_get_clean();

	// Return output
	return $output;
}