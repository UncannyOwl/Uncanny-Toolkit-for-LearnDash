<?php
/**
 * Dynamic render for uncanny-toolkit/breadcrumbs.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\uncanny_learndash_toolkit\Breadcrumbs' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\Breadcrumbs::dependants_exist() ) {
	return;
}

echo \uncanny_learndash_toolkit\Breadcrumbs::uo_breadcrumbs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same markup as the [uo_breadcrumbs] shortcode, built by Breadcrumbs::uo_breadcrumbs(): anchors are escaped in uo_build_anchor_links(); current-page text (get_the_title(), single_cat_title(), get_the_author()) and the admin-set separator are output as the shortcode does; wp_kses_post() would strip its schema.org attributes.
