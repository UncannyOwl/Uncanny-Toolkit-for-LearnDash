<?php
/**
 * Dynamic render for uncanny-toolkit/resume-button.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\uncanny_learndash_toolkit\LearnDashResume' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\LearnDashResume::dependants_exist() ) {
	return;
}

$course_id = isset( $attributes['courseId'] ) ? $attributes['courseId'] : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WP core includes render.php inside a function (block render callback), so this variable is local.

if ( empty( $course_id ) ) {
	echo \uncanny_learndash_toolkit\LearnDashResume::learndash_resume(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same markup as the [uo_learndash_resume] / [uo_course_resume] shortcodes, built by LearnDashResume (escaped there); wp_kses_post() would strip the submit input.
} else {
	echo \uncanny_learndash_toolkit\LearnDashResume::uo_course_resume( array( 'course_id' => $course_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same markup as the [uo_learndash_resume] / [uo_course_resume] shortcodes, built by LearnDashResume (escaped there); wp_kses_post() would strip the submit input.
}
