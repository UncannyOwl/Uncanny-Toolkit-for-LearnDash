<?php 

/**
 * Register Toolkit Breacrumbs
 * render it with a callback function
 */

register_block_type( 'uncanny-toolkit/breadcrumbs', [
	'render_callback' => 'render_toolkit_breadcrumbs'
]);

function render_toolkit_breadcrumbst( $attributes ){
	ob_start();

	echo 'Hello';

	$output = ob_get_clean();

	return $output;
}