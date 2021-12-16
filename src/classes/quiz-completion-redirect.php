<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Contains Quiz Completion Redirection Module
 *
 * @since unknown
 * @version 3.4.2
 *
 * @package Uncanny_Learndash_Toolkit
 */

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MarkLessonsComplete
 */
class QuizCompletionRedirect extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'hook' ) );
	}

	/**
	 * Initialize frontend actions and filters
	 */
	public static function hook() {

		if ( ! self::dependants_exist() ) {
			return;
		}

		add_filter( 'learndash_completion_redirect', array( __CLASS__, 'redirect_url' ), 99, 2 );

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'quiz-completion-advances-to-next-step';
		$class_title       = esc_html__( 'Quiz completion advances to next step', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/quiz-completion-advances-to-next-step/';
		$class_description = esc_html__( 'Automatically advance to the next step when user clicks "Click here to continue" on final quiz page of a lesson/topic.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-check-circle-o"></i>';
		$category          = 'learndash';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display.
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => false,
			'icon'             => $class_icon,
		);

	}


	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean|string Return either true or name of function or plugin
	 */
	public static function dependants_exist() {

		/* Checks for LearnDash */
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		return true;
	}

	/**
	 * Returns the post quiz redirection url.
	 *
	 * @param string $default_url Default redirection URL.
	 * @param int    $quiz_id ID of the quiz.
	 *
	 * @return string
	 */
	public static function redirect_url( $default_url, $quiz_id ) {
		$quiz_redirect = filter_input( INPUT_GET, 'quiz_redirect', FILTER_VALIDATE_BOOLEAN );

		// don't meddle if this is not a quiz redirection.
		if ( empty( $quiz_redirect ) ) {
			return $default_url;
		}
		$redirected_quiz_id = filter_input( INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT );

		// the quiz id in the url query args and in the filter should match and exist.
		if ( empty( $redirected_quiz_id ) || $redirected_quiz_id !== $quiz_id ) {
			return $default_url;
		}

		$quiz_type = filter_input( INPUT_GET, 'quiz_type', FILTER_SANITIZE_STRING );

		// LearnDash already handles redirecting to the course or the next global quiz, bail.
		if ( 'lesson' !== $quiz_type ) {
			return $default_url;
		}

		return self::after_quiz_url( $default_url, $quiz_id );

	}

	/**
	 * Returns a custom next step URL after quiz completion.
	 *
	 * @param string $default_url Default redirection URL.
	 * @param int    $quiz_id ID of the quiz.
	 *
	 * @return string
	 */
	public static function after_quiz_url( $default_url, $quiz_id ) {

		$quiz = get_post( $quiz_id );

		// confirming over and above the quiZ_redirect query arg that we're dealing with a quiz redirection.
		if ( 'sfwd-quiz' !== $quiz->post_type ) {
			return $default_url;
		}

		$step_id = filter_input( INPUT_GET, 'lesson_id', FILTER_VALIDATE_INT );

		// no lesson id was set, something's not right, bail.
		if ( empty( $step_id ) ) {
			return $default_url;
		}

		/* everything lines up, a quiz on a topic/lesson was just completed. */

		$next_quiz_url = learndash_next_lesson_quiz( true, null, $step_id, null );

		// there's another quiz to do in the step.
		if ( ! empty( $next_quiz_url ) && wp_http_validate_url( $next_quiz_url ) ) {
			return $next_quiz_url;
		}

		$step = get_post( $step_id );

		$next_step_url = learndash_next_post_link( '', true, $step );

		// this is not the last step in its scope, redirect to the next step.
		if ( ! empty( $next_step_url ) ) {
			return $next_step_url;
		}

		// otherwise, calcualte the next lesson url.
		return self::next_lesson_url( $default_url, $step );

	}

	/**
	 * Returns the next lesson URL for a step.
	 *
	 * @param string  $default_url Default redirection URL.
	 * @param WP_Post $step WP_Post object for the step.
	 *
	 * @return string
	 */
	public static function next_lesson_url( $default_url, $step ) {

		// fetch the course ID from query args.
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );

		// if not available, populate using APi function.
		if ( empty( $course_id ) ) {
			$course_id = learndash_get_course_id();
		}

		// still no course ID, something's wrong; bail.
		if ( empty( $course_id ) ) {
			return $default_url;
		}

		$post_type_name = ( 'sfwd-lessons' === $step->post_type ) ? 'lesson' : 'topic';

		// if there's no next step, we could be at the last topic in a lesson.
		if ( 'topic' === $post_type_name ) {

			$lesson_of_topic = learndash_get_lesson_id( $step->ID, $course_id );

			// if this won't complete the lesson, return users to the lesson.
			if ( ! learndash_can_complete_step( null, $lesson_of_topic, $course_id ) ) {
				return learndash_get_step_permalink( $lesson_of_topic, $course_id );
			}

			// otherwise, complete the lesson.
			learndash_process_mark_complete( null, $lesson_of_topic );

			// set this lesson as the current lesson for calculating the next url.
			$current_lesson = get_post( $lesson_of_topic );
		}

		// if not a topic, this is probably the last lesson in the course.
		if ( 'lesson' === $post_type_name ) {
			$current_lesson = $step;
		}

		$next_lesson_url = learndash_next_post_link( '', true, $current_lesson );

		// if there's a next lesson, return its url.
		if ( ! empty( $next_lesson_url ) ) {
			return $next_lesson_url;
		}

		// otherwise, this was the last lesson in the course. return the course's url instead.
		return get_permalink( $course_id );

	}
}
