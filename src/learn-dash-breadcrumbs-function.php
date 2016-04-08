<?php
	/* This function is only going to be used in templates instead of shortcode everytime.  */
	use uncanny_learndash_toolkit\learnDashBreadcrumbs;

	function learndash_breadcrumbs( $return = true ) {
		$crumb = new learnDashBreadcrumbs();
		if ( ! $return ) {
			return $crumb->learndash_breadcrumbs( false );
		} else {
			$crumb->learndash_breadcrumbs();
		}
	}