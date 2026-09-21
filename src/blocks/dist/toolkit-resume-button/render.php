<?php
/**
 * Dynamic render for uncanny-toolkit/resume-button.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! class_exists( '\uncanny_learndash_toolkit\LearnDashResume' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\LearnDashResume::dependants_exist() ) {
	return;
}

$course_id = isset( $attributes['courseId'] ) ? $attributes['courseId'] : '';

if ( empty( $course_id ) ) {
	echo \uncanny_learndash_toolkit\LearnDashResume::learndash_resume();
} else {
	echo \uncanny_learndash_toolkit\LearnDashResume::uo_course_resume( array(
		'course_id' => $course_id,
	) );
}
