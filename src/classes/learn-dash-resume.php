<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class LearnDashResume extends Config implements RequiredFunctions {

	static $topic_type = 'sfwd-topic';


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks(){

		if ( true === self::dependants_exist() ) {
			add_action( 'wp_head', array( __CLASS__, 'find_last_known_learndash_page' ) );
			add_shortcode( 'uo-learndash-resume', array( __CLASS__, 'learndash_resume' ) );
		}

	}


	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title = __( 'LearnDash Resume Button', Config::get_text_domain() );
		$class_description = __( 'Inserts a button that allows learners to return to the course, lesson or topic they last visited.',
				Config::get_text_domain() );
		$class_icon = '<i class="uo_icon_fa fa fa-refresh"></i>';

		return array( 	'title' => $class_title,
				'kb_link' => null,
				'description' => $class_description,
				'dependants_exist' => self::dependants_exist(),
			    'settings' => false,
				'icon' => $class_icon );

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * return boolean || string Return either true or name of function or plugin
	 */
	public static function dependants_exist() {
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		return true;
	}

	/**
	 *Adding wp_head action so that we capture the type of post / page user is on and add that to wordpress options table.
	 *
	 * @static
	 */
	public static function find_last_known_learndash_page() {
		/* declare $post as global so we get the post->ID of the current page / post */
		global $post;
		/* Limit the plugin to LearnDash specific post types */
		$learn_dash_post_types = apply_filters( 'last_known_learndash_post_types',
			array(
				'sfwd-courses',
				'sfwd-lessons',
				'sfwd-topic',
				'sfwd-quiz',
				'sfwd-certificates',
				'sfwd-certificates',
				'sfwd-assignment',
			)
		);
		if ( is_singular( $learn_dash_post_types ) ) {
			update_option( 'learndash_last_known_page', $post->ID );
		}
	}


	/**
	 *Adding [uo-learndash-resume] shortcode functionality which can be used anywhere on the website to take user back to last known page of LearnDash.
	 *
	 * @static
	 * @return string
	 */
	public static function learndash_resume() {
		$last_know_page_id = get_option( 'learndash_last_known_page' );

		// get_option returns false if option not set
		if ( false !== $last_know_page_id ) {
			$post_type   = get_post_type( $last_know_page_id ); // getting post_type of last page.
			$label       = get_post_type_object( $post_type ); // getting Labels of the post type.
			$title       = get_the_title( $last_know_page_id );
			$link_text   = apply_filters( 'learndash_resume_link_text', 'RESUME' );
			$css_classes = apply_filters( 'learndash_resume_css_classes', 'learndash-resume-button' );
			ob_start();
			printf( '<a href="%s" title="%s" class="%s">%s</a>',
				get_permalink( $last_know_page_id ),
				esc_attr(
					sprintf( _x( 'Resume %s: %s', 'LMS shortcode Resume link title "Resume post_type_name: Post_title ', Config::get_text_domain() ),
						$label->labels->singular_name,
						$title
					)
				),
				esc_attr( $css_classes ),
				//todo: wwhy in tranlation
				sprintf( _x( '<input type="submit" value="%s" name="sfwd_mark_complete">', '', Config::get_text_domain() ),
					esc_attr( $link_text )
				)
			);
			$resumelink = ob_get_contents();
			ob_end_clean();

			return $resumelink;
		}

		return '';
	}
}
