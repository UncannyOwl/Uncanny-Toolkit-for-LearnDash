<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MarkLessonsComplete
 * @package uncanny_custom_toolkit
 */
class QuizCompletionRedirect extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	/**
	 *
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {
			add_filter( 'learndash_quiz_continue_link', array( __CLASS__, 'quiz_continue_link_func' ), 99, 2 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title       = esc_html__( 'Quiz completion advances to next step', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/quiz-completion-advances-to-next-step/';
		$class_description = esc_html__( 'Automatically advance to the next step when user clicks "Click here to continue" on final quiz page of a lesson/topic.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-check-circle-o"></i>';
		$category          = 'learndash';
		$type              = 'free';

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => false,
			'icon'             => $class_icon,
		);

	}


	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
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
	 * @param $return_link
	 * @param $url
	 *
	 * @return string
	 */
	public static function quiz_continue_link_func( $return_link, $url ) {
		$settings_value = self::get_settings_value( 'uo-quiz-completion-redirect-continue-btn', __CLASS__ );
		if ( ! empty( $settings_value ) ) {
			// We are bailing out early
			return $return_link;
		}
		global $post;
		parse_str( $url, $query_string );
		$next_link = '';
		$prev_link = $url;
		$course_id = learndash_get_course_id( $post );
		if ( isset( $query_string['lesson_id'] ) ) {
			$lesson_topic_id = absint( $query_string['lesson_id'] );
			$next_link       = learndash_next_post_link( '', true, get_post( $lesson_topic_id ) );

			if ( empty( $next_link ) ) {
				// There is no next step for topic/lesson.. Check if this step is topic
				if ( 'sfwd-lessons' !== get_post( $lesson_topic_id )->post_type ) {
					// get lesson Id and try to get next lesson Id
					$lesson_id = learndash_get_lesson_id( $lesson_topic_id, $course_id );
					$next_link = learndash_next_post_link( '', true, get_post( $lesson_id ) );
				}
			}

			if ( ! empty( $next_link ) ) {
				$return_link = '<a id="quiz_continue_link" href="' . $next_link . '">' . esc_html( \LearnDash_Custom_Label::get_label( 'button_click_here_to_continue' ) ) . '</a>';
			}
		}

		return apply_filters( 'uo_quiz_continue_link', $return_link, $next_link, $prev_link, $course_id, $post );
	}
}