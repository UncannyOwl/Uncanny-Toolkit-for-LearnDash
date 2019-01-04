<?php

/**
 * Register Toolkit Resume Button
 * render it with a callback function
 */

register_block_type( 'uncanny-toolkit/resume-button', [
	'attributes'      => [
		'courseId' => [
			'type'    => 'string',
			'default' => ''
		]
	],
	'render_callback' => 'render_toolkit_resume_button'
] );

function render_toolkit_resume_button( $attributes ) {
	// Get course ID
	$course_id = $attributes['courseId'];

	// Start output
	ob_start();

	// Check if the class exists
	if ( class_exists( '\uncanny_learndash_toolkit\LearnDashResume' ) ) {

		if ( true === \uncanny_learndash_toolkit\LearnDashResume::dependants_exist() ) {

			// Check if the course ID is empty
			if ( empty( $course_id ) ) {
				echo \uncanny_learndash_toolkit\LearnDashResume::learndash_resume();
			} else {
				echo \uncanny_learndash_toolkit\LearnDashResume::uo_course_resume( [
					'course_id' => $course_id,
				] );
			}
		}
	}

	// Get output
	$output = ob_get_clean();

	// Return output
	return $output;
}