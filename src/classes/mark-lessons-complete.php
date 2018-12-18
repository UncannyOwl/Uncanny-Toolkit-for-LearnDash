<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MarkLessonsComplete
 * @package uncanny_custom_toolkit
 */
class MarkLessonsComplete extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {
			add_action( 'learndash_topic_completed', array( __CLASS__, 'check_learndash_topic_completed' ), 20, 1 );
			//add_action( 'learndash_lesson_completed', array( __CLASS__, 'check_learndash_lesson_completed' ), 10, 1 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title       = esc_html__( 'Topics Autocomplete Lessons', 'uncanny-learndash-toolkit' );
		$kb_link           = 'http://www.uncannyowl.com/knowledge-base/topics-autocomplete-lessons/';
		$class_description = esc_html__( 'Automatically marks lessons as completed when all topics and quizzes for that lesson are marked complete.', 'uncanny-learndash-toolkit' );
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
	 * @param $data
	 */
	public static function check_learndash_topic_completed( $data ) {

		$lesson_id = $data['lesson']->ID;

		$lesson_completed = self::check_lesson_complete( $lesson_id );

		if ( $lesson_completed ) {

			// Check if all topics and quizzes are complete in lesson
			if ( true === $lesson_completed['topics_completed'] && true === $lesson_completed['quizzes_completed'] ) {

				$mark_complete = learndash_process_mark_complete( null, $lesson_id );

				if ( $mark_complete ) {

					//Adding Lesson completed dummy filter so that BadgeOS ( or any other plugin ) hooking in to
					//learndash_lesson_completed can run here.
					add_filter( 'learndash_lesson_completed', array( __CLASS__, 'learndash_lesson_completed_filter' ) );
					if ( ( isset( $_POST['post'] ) && absint( $_POST['post'] ) ) && isset( $_POST['uploadfile'] ) ) {
						$post = get_post( absint( $_POST['post'] ) );
						if ( lesson_hasassignments( $post ) ) {
							learndash_approve_assignment( get_current_user_id(), absint( $_POST['post'] ) );
						}
					}

					// only redirect if lesson does not have auto-complete on
					if ( self::maybe_redirect( $data['lesson'] ) ) {
						if( !is_admin()){
							learndash_get_next_lesson_redirect( $data['lesson'] );
						}
					}

				}
			}

			// If quizzes are not complete
			if ( is_array( $lesson_completed['quizzes_completed'] ) ) {

				$Lesson_quiz_ids = $lesson_completed['quiz_list_left'];

				foreach ( $Lesson_quiz_ids as $quiz_id ) {

					$is_quiz_notcomplete = learndash_is_quiz_notcomplete( null, array( $quiz_id ) );

					// Redirect to first incomplete quiz in list
					if ( $is_quiz_notcomplete ) {

						// only redirect if lesson does not have auto-complete on
						if ( self::maybe_redirect( $data['lesson'] ) ) {
							if( ! is_admin() ){
								wp_safe_redirect( get_permalink( $quiz_id ) );
								exit;
							}

						}

					}

				}

			}
		}

	}

	/*
	 * Only redirect if the Pro module Auto complete lessons/Topics is off for lesson
	 * @param object $lesson_post_object custom post type lesson object
	 *
	 * @return bool $maybe_redirect
	 */
	private static function maybe_redirect( $lesson_post_object ) {

		$active_classes = Config::get_active_classes();

		$maybe_redirect = true;

		// is auto-complete active
		if ( in_array( 'uncanny_pro_toolkit\LessonTopicAutoComplete', $active_classes ) ) {
			$maybe_redirect = false;

			$feature_auto_complete_default = self::get_settings_value( 'uo_global_auto_complete', 'uncanny_pro_toolkit/LessonTopicAutoComplete' );
			$post_options_auto_complete    = learndash_get_setting( $lesson_post_object );

			// Is this lesson using auto-complete
			if ( isset( $post_options_auto_complete['uo_auto_complete'] ) ) {

				if ( 'disabled' === $post_options_auto_complete['uo_auto_complete'] ) {
					$maybe_redirect = true;
				}
			}

			// Is the lesson not set
			if ( ! isset( $post_options_auto_complete['uo_auto_complete'] ) ) {
				if ( '' !== $feature_auto_complete_default || 'auto_complete_only_lesson_topics_set' !== $feature_auto_complete_default ) {
					$maybe_redirect = false;
				}
			}
		}

		return $maybe_redirect;
	}

	/**
	 *
	 */
	public static function learndash_lesson_completed_filter() {
	}

	/*
	 * Since we marked the lesson complete manually in check_learndash_topic_completed()
	 * when we completed a topic, make sure we redirect to the next lesson or
	 * learndash will redirect to the current lesson
	*/
	public static function check_learndash_lesson_completed( $data ) {
	}

	/*
	 * Check if lessons and/or topics are complete
	 *
	 * @return bool || Array
	 *
	 */
	public static function check_lesson_complete( $lesson_id ) {

		$user_id = get_current_user_id();

		// Get all user quiz results
		$user_quizzes = get_user_meta( $user_id, '_sfwd-quizzes', true );
		if ( '' === $user_quizzes ) {
			$user_quizzes = array();
		}

		// Get all quizzes associated with the lesson ( topic->quizzes are not included )
		$quiz_list = learndash_get_lesson_quiz_list( $lesson_id );
		if ( '' === $quiz_list ) {
			$quiz_list = array();
		}

		// Compare amount of quizzes in lesson with the amount of matching user quizzes passed
		$amount_quizzes_in_lesson   = count( $quiz_list );
		$amount_quizzes_user_passed = 0;
		$quizzes_passed             = array();

		$quiz_list_left = array();

		if ( is_array( $quiz_list ) && ! empty( $quiz_list ) ) {
			// Loop all quizzes in lessons
			foreach ( $quiz_list as $quiz ) {
				if ( is_array( $user_quizzes ) && ! empty( $user_quizzes ) ) {
					// Loop all quizzes completed by user
					foreach ( $user_quizzes as $user_quiz ) {
						// check if lesson quiz id and completed quiz id match
						if ( $quiz['post']->ID === (int) $user_quiz['quiz'] ) {
							// Check if the quiz was passed
							if ( 1 === $user_quiz['pass'] ) {
								if ( ! key_exists( (int) $user_quiz['quiz'], $quizzes_passed ) ) {
									$quizzes_passed[ (int) $user_quiz['quiz'] ] = true;
									$amount_quizzes_user_passed ++;
								}
							} // Quiz was attempted but not passed
							else {
								array_push( $quiz_list_left, $quiz['post']->ID );
							}
						} else {
							// Quiz was not attempted
							array_push( $quiz_list_left, $quiz['post']->ID );
						}

					}
				} else {
					// User has not yet attempted any quizzes
					array_push( $quiz_list_left, $quiz['post']->ID );
				}
			}
		}

		if ( $amount_quizzes_user_passed === $amount_quizzes_in_lesson ) {
			$quizzes_completed = true;
		} else {
			$quizzes_completed = false;
		}

		// Check if all topics in lesson were completed
		// A passed quiz in a topic will automatically mark that topic complete
		$topics_completed = learndash_lesson_topics_completed( $lesson_id, false );

		$completion_status = array(
			'topics_completed'  => $topics_completed,
			'quizzes_completed' => $quizzes_completed,
			'quiz_list_left'    => $quiz_list_left,
		);

		// when all topics are completed and
		// the amount of quizzes in the lesson match the amount of quizzes passed in the lesson
		if ( true === $topics_completed || $amount_quizzes_user_passed === $amount_quizzes_in_lesson ) {
			return $completion_status;
		}

		return false;

	}
}