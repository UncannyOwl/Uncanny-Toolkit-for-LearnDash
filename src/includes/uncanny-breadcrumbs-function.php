<?php
/* This function is only going to be used in templates instead of shortcode.  */

/**
 * @param bool $echo
 *
 * @return string
 */
function uo_breadcrumbs( $echo = true ) {
	$uo_active_classes = get_option( 'uncanny_toolkit_active_classes', 0 );
	if ( 0 !== $uo_active_classes ) {
		if ( ! key_exists( 'uncanny_learndash_toolkit\Breadcrumbs', $uo_active_classes ) ) {
			return '';
		}
	}
	$crumb = new \uncanny_learndash_toolkit\Breadcrumbs();
	if ( ! $echo ) {
		return $crumb->uo_breadcrumbs();
	} else {
		echo wp_kses( $crumb->uo_breadcrumbs(), array(
			'a'    => array(
				'href'   => array(),
				'title'  => array(),
				'class'  => array(),
				'target' => array(),
			),
			'br'   => array(),
			'i'    => array(
				'title' => array(),
				'class' => array(),
			),
			'span' => array(
				'title' => array(),
				'class' => array(),
			),
			'nav'  => array(
				'title' => array(),
				'class' => array(),
			),
		) );
	}
}