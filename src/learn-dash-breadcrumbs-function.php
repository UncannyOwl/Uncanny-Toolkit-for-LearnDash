<?php
	/**
	 * Created by PhpStorm.
	 * User: Saad
	 * Date: 2016-04-07
	 * Time: 6:39 PM
	 */

	use uncanny_learndash_toolkit\learnDashBreadcrumbs;

	function learndash_breadcrumbs( $return = true ) {
		$crumb = new learnDashBreadcrumbs();
		if ( ! $return ) {
			return $crumb->learndash_breadcrumbs( false );
		} else {
			$crumb->learndash_breadcrumbs();
		}
	}