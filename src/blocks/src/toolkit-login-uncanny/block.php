<?php 

/**
 * Register Uncanny Login Form
 * render it with a callback function
 */

register_block_type( 'uncanny-toolkit/login-uncanny', [
	'render_callback' => 'render_login_uncanny'
]);

function render_login_uncanny( $attributes ){


	// Start output
	ob_start();
	echo 'here';
	// Check if the class exists
	if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ){
		//echo \uncanny_learndash_toolkit\FrontendLoginPlus::uo_login_ui();
	}	

	// Get output
	$output = ob_get_clean();

	// Return output
	return $output;
}