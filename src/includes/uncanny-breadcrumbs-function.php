<?php
/* This function is only going to be used in templates instead of shortcode.  */

function uo_breadcrumbs( $return = true ) {
	$crumb = new \uncanny_learndash_toolkit\uncannyBreadcrumbs();
	if ( ! $return ) {
		return $crumb->uo_breadcrumbs( false );
	} else {
		$crumb->uo_breadcrumbs();
	}
}