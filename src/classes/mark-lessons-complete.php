<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Contains Topics Autocomplete Lesson Module
 *
 * @since   unknown
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
 *
 * @since   unknown
 * @version 3.4.2
 */
class MarkLessonsComplete extends Config implements RequiredFunctions {

	/**
	 * Lesson Redirection
	 *
	 * @var   [type]
	 *
	 * @since unnknown
	 */
	public static $lesson_redirection = null;

	/**
	 * Quiz Redirection
	 *
	 * @var   [type]
	 *
	 * @since unknown
	 */
	public static $quiz_redirection = null;

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/**
	 * Initializes frontend actions and filters
	 *
	 * @since unknown
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {
			// hook into topic completion.
			add_action( 'learndash_topic_completed', array( __CLASS__, 'check_learndash_topic_completed' ), 20, 1 );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'topics-autocomplete-lessons';
		$class_title       = esc_html__( 'Topics Autocomplete Lessons (Legacy)', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/topics-autocomplete-lessons/';
		$class_description = esc_html__( 'Automatically marks lessons as completed when all topics and quizzes for that lesson are marked complete.', 'uncanny-learndash-toolkit' );
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
	 * @return boolean || string Return either true or name of function or plugin
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
	 * Checks if a topic is completed
	 *
	 * @param array $data Completion data.
	 */
	public static function check_learndash_topic_completed( $data ) {

		$lesson_id = $data['lesson']->ID;
		$user_id   = $data['user']->ID;

		// get the lesson's completion data.
		$lesson_completed = self::check_lesson_complete( $lesson_id, $user_id );

		if ( $lesson_completed ) {

			// Check if all topics and quizzes are complete in lesson.
			if ( true === $lesson_completed['topics_completed'] && true === $lesson_completed['quizzes_completed'] ) {

				// Check if assignment is turned on.
				$maybe_complete = self::is_linked_with_assignment( $data['lesson'], true );

				if ( $maybe_complete ) {
					$mark_complete = learndash_process_mark_complete( $user_id, $lesson_id );
				} else {
					$mark_complete = false;
				}
				if ( $mark_complete ) {

					// Adding Lesson completed dummy filter so that BadgeOS ( or any other plugin ) hooking in to
					// learndash_lesson_completed can run here.
					add_filter( 'learndash_lesson_completed', array( __CLASS__, 'learndash_lesson_completed_filter' ) );
					if ( ( isset( $_POST['post'] ) && absint( $_POST['post'] ) ) && isset( $_POST['uploadfile'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$post = get_post( absint( $_POST['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$assignments_exist_fn = function_exists( 'learndash_lesson_hasassignments' ) ? 'learndash_lesson_hasassignments' : 'lesson_hasassignments';
						if ( $assignments_exist_fn( $post ) ) {
							learndash_approve_assignment( $user_id, absint( $_POST['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
						}
					}

					// only redirect if lesson does not have auto-complete on.
					if ( self::maybe_redirect( $data['lesson'] ) ) {
						if ( ! is_admin() && ! isset( $_REQUEST['doing_rest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$next_lesson = learndash_next_post_link( '', true, $data['lesson'] );

							// The do_action topic completed runs before the activity log is updated.
							self::$lesson_redirection = $data['lesson'];

							// if there's no next lesson, this is the last lesson in the course.
							if ( empty( $next_lesson ) ) {
								// complete the course too.
								learndash_process_mark_complete( $user_id, $data['course']->ID );
								// set up redirection to the course.
								self::$lesson_redirection = $data['course'];
							}


							// Do the redirect on shutdown so all processes can complete.
							add_action(
								'shutdown',
								function () {
									learndash_get_next_lesson_redirect( self::$lesson_redirection );
								}
							);

						}
					}
				}
			}

			// If quizzes are not complete.
			if ( is_array( $lesson_completed['quizzes_completed'] ) ) {

				$lesson_quiz_ids = $lesson_completed['quiz_list_left'];

				foreach ( $lesson_quiz_ids as $quiz_id ) {

					$is_quiz_notcomplete = learndash_is_quiz_notcomplete( $user_id, array( $quiz_id ) );

					// Redirect to first incomplete quiz in list.
					if ( $is_quiz_notcomplete ) {

						// only redirect if lesson does not have auto-complete on.
						if ( self::maybe_redirect( $data['lesson'] ) ) {
							if ( ! is_admin() && ! isset( $_REQUEST['doing_rest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								// The do_action topic completed runs before the activity log is updated.
								self::$quiz_redirection = $quiz_id;
								// Do the redirect on shutdown so all processes can complete.
								add_action(
									'shutdown',
									function () {
										wp_safe_redirect( get_permalink( self::$quiz_redirection ) );
									}
								);
							}
						}
					}
				}
			}
		}

	}

	/**
	 * Only redirect if the Pro module Auto complete lessons/Topics is off for lesson
	 *
	 * @param \WP_Post $lesson_post_object custom post type lesson object.
	 *
	 * @return bool
	 */
	private static function maybe_redirect( $lesson_post_object ) {
		$maybe_redirect = true;
		// is auto-complete active.
		if ( ! self::is_toolkit_module_active( 'uncanny_pro_toolkit\LessonTopicAutoComplete', true ) ) {
			return $maybe_redirect;
		}

		$maybe_redirect = false;

		$feature_auto_complete_default = self::get_settings_value( 'uo_global_auto_complete', 'uncanny_pro_toolkit/LessonTopicAutoComplete' );
		$post_options_auto_complete    = learndash_get_setting( $lesson_post_object );

		// Is this lesson using auto-complete.
		if ( isset( $post_options_auto_complete['uo_auto_complete'] ) ) {

			if ( 'disabled' === $post_options_auto_complete['uo_auto_complete'] ) {
				$maybe_redirect = true;
			}
		}

		// Is the lesson not set.
		if ( ! isset( $post_options_auto_complete['uo_auto_complete'] ) ) {
			if ( '' !== $feature_auto_complete_default || 'auto_complete_only_lesson_topics_set' !== $feature_auto_complete_default ) {
				$maybe_redirect = false;
			}
		}

		return $maybe_redirect;
	}

	/**
	 * Lesson Completed Filter
	 */
	public static function learndash_lesson_completed_filter() {
	}

	/**
	 * Since we marked the lesson complete manually in check_learndash_topic_completed()
	 * when we completed a topic, make sure we redirect to the next lesson or
	 * learndash will redirect to the current lesson.
	 *
	 * @param array $data Data.
	 */
	public static function check_learndash_lesson_completed( $data ) {
	}

	/**
	 * Check if lessons and/or topics are complete
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @return array|false
	 */
	public static function check_lesson_complete( $lesson_id, $user_id = null ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		// Get all user quiz results.
		$user_quizzes = get_user_meta( $user_id, '_sfwd-quizzes', true );
		if ( '' === $user_quizzes ) {
			$user_quizzes = array();
		}

		// Get all quizzes associated with the lesson ( topic->quizzes are not included ).
		$quiz_list = learndash_get_lesson_quiz_list( $lesson_id );
		if ( '' === $quiz_list ) {
			$quiz_list = array();
		}

		// Compare amount of quizzes in lesson with the amount of matching user quizzes passed.
		$amount_quizzes_in_lesson   = count( $quiz_list );
		$amount_quizzes_user_passed = 0;
		$quizzes_passed             = array();

		$quiz_list_left = array();

		if ( is_array( $quiz_list ) && ! empty( $quiz_list ) ) {

			// Loop all quizzes in lessons.
			foreach ( $quiz_list as $quiz ) {

				if ( is_array( $user_quizzes ) && ! empty( $user_quizzes ) ) {

					// Loop all quizzes completed by user.
					foreach ( $user_quizzes as $user_quiz ) {

						// check if lesson quiz id and completed quiz id match.
						if ( $quiz['post']->ID === (int) $user_quiz['quiz'] ) {

							// Check if the quiz was passed.
							if ( 1 === $user_quiz['pass'] ) {
								if ( ! key_exists( (int) $user_quiz['quiz'], $quizzes_passed ) ) {
									$quizzes_passed[ (int) $user_quiz['quiz'] ] = true;
									$amount_quizzes_user_passed ++;
								}
							} else { // Quiz was attempted but not passed.
								if ( ! in_array( $quiz['post']->ID, $quiz_list_left, false ) ) {
									array_push( $quiz_list_left, $quiz['post']->ID );
								}
							}
						} else { // Quiz was not attempted.
							if ( ! in_array( $quiz['post']->ID, $quiz_list_left, false ) ) {
								array_push( $quiz_list_left, $quiz['post']->ID );
							}
						}
					}
				} else { // User has not yet attempted any quizzes.
					if ( ! in_array( $quiz['post']->ID, $quiz_list_left, false ) ) {
						array_push( $quiz_list_left, $quiz['post']->ID );
					}
				}
			}
		}

		if ( $amount_quizzes_user_passed === $amount_quizzes_in_lesson ) {
			$quizzes_completed = true;
		} else {
			$quizzes_completed = false;
		}

		$course_id = learndash_get_course_id( $lesson_id );

		// Check if all topics in lesson were completed.
		// A passed quiz in a topic will automatically mark that topic complete.
		$topics_completed = self::learndash_lesson_topics_completed( $user_id, $lesson_id, $course_id, false );

		$completion_status = array(
			'topics_completed'  => $topics_completed,
			'quizzes_completed' => $quizzes_completed,
			'quiz_list_left'    => $quiz_list_left,
		);

		// when all topics are completed and
		// the amount of quizzes in the lesson match the amount of quizzes passed in the lesson.
		if ( true === $topics_completed || $amount_quizzes_user_passed === $amount_quizzes_in_lesson ) {
			return $completion_status;
		}

		return false;

	}

	/**
	 * Checks if a step is linked with an assignment.
	 *
	 * @param WP_Post $post           LearnDash step, toic/lesson.
	 * @param bool    $maybe_complete Whenther to complete the step.
	 *
	 * @return bool
	 */
	public static function is_linked_with_assignment( $post, $maybe_complete ) {
		// Check if assignment is turned on.
		$assignments_exist_fn = function_exists( 'learndash_lesson_hasassignments' ) ? 'learndash_lesson_hasassignments' : 'lesson_hasassignments';
		if ( $assignments_exist_fn( $post ) ) {
			$post_options_auto_complete = (array) learndash_get_setting( $post );
			if ( key_exists( 'auto_approve_assignment', $post_options_auto_complete ) && 'on' === $post_options_auto_complete['auto_approve_assignment'] ) {
				$maybe_complete = true;
			} else {
				$maybe_complete = false;
			}
		}

		return $maybe_complete;

	}

	/**
	 * Modified Learndash function for adding user id support.
	 * Checks if the lesson topics are completed.
	 *
	 * @param int     $user_id              User ID.
	 * @param int     $lesson_id            Lesson ID.
	 * @param int     $course_id            Course ID.
	 * @param boolean $mark_lesson_complete Optional. Whether to mark the lesson complete. Default false.
	 *
	 * @return boolean Returns true if the lesson is completed otherwise false.
	 * @since 3.3.3
	 */
	public static function learndash_lesson_topics_completed( $user_id, $lesson_id, $course_id, $mark_lesson_complete = false ) {
		$topics = learndash_get_topic_list( $lesson_id, $course_id );

		if ( empty( $topics[0]->ID ) ) {
			return true;
		}

		$progress = learndash_get_course_progress( $user_id, $topics[0]->ID );

		if ( empty( $progress['posts'] ) || ! is_array( $progress['posts'] ) ) {
			return false;
		}

		foreach ( $progress['posts'] as $topic ) {
			if ( empty( $topic->completed ) ) {
				return false;
			}
		}

		if ( $mark_lesson_complete ) {
			learndash_process_mark_complete( $user_id, $lesson_id );
		}

		return true;
	}
}
