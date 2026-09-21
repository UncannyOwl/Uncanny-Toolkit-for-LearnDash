<?php
/**
 * Dynamic render for uncanny-toolkit/breadcrumbs.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! class_exists( '\uncanny_learndash_toolkit\Breadcrumbs' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\Breadcrumbs::dependants_exist() ) {
	return;
}

echo \uncanny_learndash_toolkit\Breadcrumbs::uo_breadcrumbs();
