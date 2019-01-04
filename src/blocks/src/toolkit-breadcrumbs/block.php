<?php

/**
 * Register Toolkit Breacrumbs
 * render it with a callback function
 */

register_block_type( 'uncanny-toolkit/breadcrumbs', [
	'render_callback' => 'render_toolkit_breadcrumbs'
] );

function render_toolkit_breadcrumbs( $attributes ) {
	ob_start();

	if ( class_exists( '\uncanny_learndash_toolkit\Breadcrumbs' ) ) {
		if ( true === \uncanny_learndash_toolkit\Breadcrumbs::dependants_exist() ) {
			echo \uncanny_learndash_toolkit\Breadcrumbs::uo_breadcrumbs();
		}
	}

	$output = ob_get_clean();

	return $output;
}