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
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/**
	 * Initialize frontend actions and filters
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
	 * Returns a custom quiz continue link
	 *
	 * @param string $return_link Link HTML.
	 * @param string $url Link URL.
	 *
	 * @return string
	 */
	public static function quiz_continue_link_func( $return_link, $url ) {

		global $post;
		parse_str( $url, $query_string );
		$next_link = '';
		$prev_link = $url;
		$course_id = learndash_get_course_id( $post );
		if ( isset( $query_string['lesson_id'] ) ) {
			$lesson_topic_id  = absint( $query_string['lesson_id'] );
			$quiz_id          = absint( $query_string['quiz_id'] );
			$next_link        = learndash_next_post_link( '', true, get_post( $lesson_topic_id ) );
			$lesson_completed = MarkLessonsComplete::check_lesson_complete( $lesson_topic_id, wp_get_current_user()->ID );

			if ( empty( $next_link ) ) {
				// There is no next step for topic/lesson.. Check if this step is not lesson.
				if ( 'sfwd-lessons' !== get_post( $lesson_topic_id )->post_type ) {
					// get lesson Id and try to get next lesson Id.
					$lesson_id = learndash_get_lesson_id( $lesson_topic_id, $course_id );
					$next_link = learndash_next_post_link( '', true, get_post( $lesson_id ) );
					$quiz_list = self::check_quiz_list( $lesson_id, $quiz_id, wp_get_current_user() );

					// if there's no next link, we're on the last lesson.
					if ( empty( $next_link ) ) {
						$next_link = self::get_next_link( $lesson_completed, $query_string, $lesson_topic_id, $course_id );
						// if next link is still empty, redirect to lesson
						if ( empty( $next_link ) ) {
							// get the lesson's permalink instead.
							$next_link = get_permalink( $lesson_id );
						}
					} else {
						if ( ! $quiz_list ) {
							// get the lesson's permalink instead.
							$next_link = get_permalink( $lesson_id );
						}
					}
				} else {
					// It's a lesson
					$next_link = self::get_next_link( $lesson_completed, $query_string, $lesson_topic_id, $course_id );
				}
			}

			if ( ! empty( $next_link ) ) {
				$return_link = '<a id="quiz_continue_link" href="' . $next_link . '">' . esc_html( \LearnDash_Custom_Label::get_label( 'button_click_here_to_continue' ) ) . '</a>';
			}
		}

		return apply_filters( 'uo_quiz_continue_link', $return_link, $next_link, $prev_link, $course_id, $post );
	}

	/**
	 * @param $lesson_topic_id
	 * @param $quiz_id_match
	 * @param null $user
	 *
	 * @return bool
	 */
	public static function check_quiz_list( $lesson_topic_id, $quiz_id_match, $user = null ) {

		if ( empty( $user ) ) {
			$user = wp_get_current_user();
		}

		$quiz_list = learndash_get_lesson_quiz_list( $lesson_topic_id );
		if ( empty( $quiz_list ) ) {
			$quiz_list = array();
		}

		if ( is_array( $quiz_list ) && ! empty( $quiz_list ) ) {

			// Loop all quizzes in lessons
			foreach ( $quiz_list as $quiz ) {

				$quiz_id = $quiz['post']->ID;
				if ( ! learndash_is_quiz_complete( $user->ID, $quiz_id ) ) {
					// If the quiz id matches the current quiz id, assume it's completed but not recorded yet
					if ( absint( $quiz_id ) === absint( $quiz_id_match ) ) {
						continue;
					}

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param $lesson_completed
	 * @param $query_string
	 * @param $lesson_topic_id
	 * @param $course_id
	 *
	 * @return false|string|\WP_Error
	 */
	public static function get_next_link( $lesson_completed, $query_string, $lesson_topic_id, $course_id ) {
		$next_link         = '';
		$quizzes_completed = 0;
		$topics_completed  = $lesson_completed['topics_completed'];
		if ( isset( $lesson_completed['quizzes_completed'] ) && isset( $lesson_completed['quiz_list_left'] ) && empty( $lesson_completed['quizzes_completed'] ) ) {
			$quiz_list = $lesson_completed['quiz_list_left'];
			if ( in_array( absint( $query_string['quiz_id'] ), $quiz_list, true ) && 1 === (int) count( $quiz_list ) ) {
				// assume all quizzes completed
				$quizzes_completed = 1;
			}
		}
		if ( 1 === absint( $quizzes_completed ) && 1 === absint( $topics_completed ) ) {
			// Check if assignment is turned on.
			$maybe_complete = MarkLessonsComplete::is_linked_with_assignment( get_post( $lesson_topic_id ), true );
			if ( $maybe_complete ) {
				$next_link = get_permalink( $course_id );
			}
		}

		return $next_link;
	}
}
