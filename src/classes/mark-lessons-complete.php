<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}

class MarkLessonsComplete extends Config implements RequiredFunctions{


	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if( true === self::dependants_exist()){

			add_action( 'learndash_topic_completed', array( __CLASS__, 'check_learndash_topic_completed'), 10, 1);
			add_action( 'learndash_lesson_completed', array( __CLASS__, 'check_learndash_lesson_completed'), 10, 1);


		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {

		$class_title = __( 'Mark Lessons Complete', self::get_text_domain() );

		$kb_link = 'http://www.uncannyowl.com';

		/* Sample Simple Description */
		$class_description = __( 'Sample Description Mark Lessons Complete', self::get_text_domain() );

		/* Sample Simple Description with shortcode */

		/* Icon as fontawesome icon */
		$class_icon = '<i class="uo_icon_fa fa fa-check"></i>';

		return array( 	'title' => $class_title,
			'kb_link' => $kb_link, // OR set as null not to display
			'description' => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings' => false,
			'icon' => $class_icon );

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist(){

		/* Checks for LearnDash */
		global $learndash_post_types;
		if( !isset($learndash_post_types) ){
			return 'Plugin: LearnDash';
		}

		return true;
	}

	public static function check_learndash_topic_completed( $data ){

		$lesson_id = $data['lesson']->ID;

		$lesson_completed = self::check_lesson_complete( $lesson_id );

		if( false !== $lesson_completed ){

			// Check if all topics and quizzes are complete in lesson
			if( true === $lesson_completed['topics_completed'] && true === $lesson_completed['quizzes_completed']){
				learndash_process_mark_complete( null, $lesson_id);
			}

			// If quizzes are not complete
			if( is_array( $lesson_completed['quizzes_completed'] ) ){

				$Lesson_quiz_ids = $lesson_completed['quizzes_completed'];

				foreach( $Lesson_quiz_ids as $quiz_id ){

					$is_quiz_notcomplete = learndash_is_quiz_notcomplete( null, array( $quiz_id ) );
					// Redirect to first incomplete quiz in list
					if($is_quiz_notcomplete){
						wp_safe_redirect( get_permalink($quiz_id) );
						exit;
					}

				}

			}
		}

	}

	/*
	 * Since we marked the lesson complete manually in check_learndash_topic_completed()
	 * when we completed a topic, make sure we redirect to the next lesson or
	 * learndash will redirect to the current lesson
	*/
	public static function check_learndash_lesson_completed( $data ){
		learndash_get_next_lesson_redirect( $data['lesson'] );
	}

	/*
	 * Check if lessons and/or topics are complete
	 *
	 * @return bool || Array
	 *
	 */
	public static function check_lesson_complete( $lesson_id ){

		$user_id = get_current_user_id();

		// Get all user quiz results
		$user_quizzes = get_user_meta( $user_id, '_sfwd-quizzes', true );
		if( '' === $user_quizzes ){
			$user_quizzes = array();
		}

		// Get all quizzes associated with the lesson ( topic->quizzes are not included )
		$quiz_list =  learndash_get_lesson_quiz_list( $lesson_id );
		if( '' === $quiz_list ){
			$quiz_list = array();
		}

		// Compare amount of quizzes in lesson with the amount of matching user quizzes passed
		$amount_quizzes_in_lesson = count($quiz_list);
		$amount_quizzes_user_passed = 0;

		$quiz_list_left = array();
		
		// Loop all quizzes in lessons
		foreach( $quiz_list as $quiz ){

			array_push( $quiz_list_left, $quiz['post']->ID );
			// Loop all quizzes completed by user
			foreach( $user_quizzes as $user_quiz){

				// check if lesson quiz id and completed quiz id match
				if( $quiz['post']->ID === (int)$user_quiz['quiz'] ){

					// Check if the quiz was passed
					if( 1 === $user_quiz['pass'] ){
						$amount_quizzes_user_passed++;
					}

				}

			}

		}

		if( $amount_quizzes_user_passed === $amount_quizzes_in_lesson ){
			$quizzes_completed = true;
		}else{
			$quizzes_completed = $quiz_list_left;
		}

		// Check if all topics in lesson were completed
		// A passed quiz in a topic will automatically mark that topic complete
		$topics_completed = learndash_lesson_topics_completed( $lesson_id, false);

		$completion_status = array( 'topics_completed' 	=> $topics_completed,
									'quizzes_completed'	=> $quizzes_completed );

		// when all topics are completed and
		// the amount of quizzes in the lesson match the amount of quizzes passed in the lesson
		if( true === $topics_completed || $amount_quizzes_user_passed === $amount_quizzes_in_lesson ){
			return $completion_status;
		}

		return false;

	}

}