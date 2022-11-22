<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ShowCertificatesShortcode
 * @package uncanny_custom_toolkit
 */
class ShowCertificatesShortcode extends Config implements RequiredFunctions {

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

			// Show quiz and course certificates shortcode
			add_shortcode( 'uo-learndash-certificates', array( __CLASS__, 'learndash_certificates' ) );
			add_shortcode( 'uo_learndash_certificates', array( __CLASS__, 'learndash_certificates' ) );

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'show-certificates';
		$class_title       = esc_html__( 'Show Certificates', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/show-learndash-certificates/';
		$class_description = esc_html__( 'Displays a list of LearnDash certificates (both course and quiz) earned by the user, with the most recent at the top.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-certificate"></i>';
		$category          = 'learndash';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => self::get_class_settings( $class_title ),
			'icon'             => $class_icon,
		);

	}

	/**
	 * HTML for modal to create settings
	 *
	 * @param String
	 *
	 * @return string Return either false or settings html modal
	 *
	 */
	public static function get_class_settings( $class_title ) {
		// Create options
		$options = array(
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Display Certificate title instead of Course/Quiz title', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uncanny-showcertificate-show-cert-title',
			),
		);

		// Build html
		$html = self::settings_output(
			array(
				'class'   => __CLASS__,
				'title'   => $class_title,
				'options' => $options,
			) );

		return $html;
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
	 *
	 *
	 * @static
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public static function learndash_certificates( $atts ) {

		if ( ! is_user_logged_in() ) {
			return '';
		}

		if ( isset( $atts['class'] ) ) {
			$class = $atts['class'];
		} else {
			$class = 'certificate-list-container';
		}

		if ( isset( $atts['title'] ) ) {
			$title = $atts['title'];
		} else {
			$title = '';
		}

		if ( isset( $atts['no-cert-message'] ) ) {
			$no_cert_message = $atts['no-cert-message'];
		} else {
			$no_cert_message = esc_html__( 'Complete courses to earn certificates', 'uncanny-learndash-toolkit' );
		}

		$course_certificates = 'show';
		if ( isset( $atts['course_certificates'] ) ) {
			if ( in_array( $atts['course_certificates'], array('show','hide'), true ) ) {
				$course_certificates = $atts['course_certificates'];
			}
		}

		$quiz_certificates = 'show';
		if ( isset( $atts['quiz_certificates'] ) ) {
			if ( in_array( $atts['quiz_certificates'], array('show','hide'), true ) ) {
				$quiz_certificates = $atts['quiz_certificates'];
			}
		}

		$group_certificates = 'show';
		if ( isset( $atts['group_certificates'] ) ) {
			if ( in_array( $atts['group_certificates'], array('show','hide'), true ) ) {
				$group_certificates = $atts['group_certificates'];
			}
		}

		$show_cert_title  = self::get_settings_value( 'uncanny-showcertificate-show-cert-title', __CLASS__ );
		$certificate_list = '';
		$courses = array();
		$quiz_attempts = array();

		/* GET Certificates For Courses*/
		if( 'show' === $course_certificates ){
			$args = array(
				'post_type'      => 'sfwd-courses',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			);

			$courses = get_posts( $args );

			foreach ( $courses as $course ) {

				$certificate_id     = learndash_get_setting( $course->ID, 'certificate' );
				$certificate_object = get_post( $certificate_id );

				if ( ! empty( $certificate_object ) ) {
					if ( 'on' === $show_cert_title ) {
						$certificate_title = $certificate_object->post_title;
					} else {
						$certificate_title = $course->post_title;
					}

					$certificate_link = learndash_get_course_certificate_link( $course->ID );

					if ( $certificate_link && '' !== $certificate_link ) {
						$link             = apply_filters( 'uo_show_course_certificate_link', sprintf( '<a target="_blank" href="%s">%s</a>', $certificate_link, $certificate_title ), wp_get_current_user(), $course );
						$certificate_list .= $link . '<br>';
					}
				}
			}
		}

		/* GET Certificates for Quizzes*/
		if( 'show' === $quiz_certificates ){
			$quiz_attempts = self::quiz_attempts();

			if ( ! empty( $quiz_attempts ) ) {

				$quiz_attempts = array_reverse( $quiz_attempts );

				foreach ( $quiz_attempts as $k => $quiz_attempt ) {

					if ( isset( $quiz_attempt['certificate'] ) ) {
						$certificate_link    = $quiz_attempt['certificate']['certificateLink'];
						$quiz_title_fallback = ( isset( $quiz_attempt['quiz_title'] ) ) ? $quiz_attempt['quiz_title'] : '';
						$quiz_title          = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : $quiz_title_fallback;

						if ( ! empty( $certificate_link ) ) {

							if( isset($quiz_attempt['time']) ){
								$certificate_link = add_query_arg( array( 'time' => $quiz_attempt['time'] ), $certificate_link );
							}

							$meta               = get_post_meta( $quiz_attempt['post']->ID, '_sfwd-quiz', true );
							$certificate_id     = $meta['sfwd-quiz_certificate'];
							$certificate_object = get_post( $certificate_id );
							if ( 'on' === $show_cert_title ) {
								$certificate_title = $certificate_object->post_title;
							} else {
								$certificate_title = $quiz_title;
							}
							$link             = apply_filters( 'uo_show_quiz_certificate_link', sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( $certificate_link ), $certificate_title ), wp_get_current_user(), $quiz_attempt );
							$certificate_list .= $link . '<br>';
						}
					}
				}
			}
		}

		/* GET Certificates for Groups*/
		if( 'show' === $group_certificates ){

			$args = array(
				'post_type'      => 'groups',
				'posts_per_page' => 9999,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			);

			$groups = get_posts( $args );

			foreach ( $groups as $group ) {

				$certificate_id     = learndash_get_setting( $group->ID, 'certificate' );
				$certificate_object = get_post( $certificate_id );

				if ( ! empty( $certificate_object ) ) {
					if ( 'on' === $show_cert_title ) {
						$certificate_title = $certificate_object->post_title;
					} else {
						$certificate_title = $group->post_title;
					}

					$certificate_link = learndash_get_group_certificate_link( $group->ID );

					if ( $certificate_link && '' !== $certificate_link ) {
						$link             = apply_filters( 'uo_show_group_certificate_link', sprintf( '<a target="_blank" href="%s">%s</a>', $certificate_link, $certificate_title ), wp_get_current_user(), $group );
						$certificate_list .= $link . '<br>';
					}
				}
			}
		}

		$certificate_list = apply_filters_deprecated( 'certificate_list_shortcode', array( $certificate_list ), '3.6.2', 'uo_certificate_list_shortcode' );
		$certificate_list = apply_filters( 'uo_certificate_list_shortcode', $certificate_list, $courses, $quiz_attempts );

		if ( '' === $certificate_list ) {
			$certificate_list = $no_cert_message;
		}

		$shortcode_html = '';
		if( 'show' === $course_certificates ||  'show' === $quiz_certificates ||  'show' === $group_certificates ){
			ob_start();
			?>
			<div class="<?php echo esc_attr( $class ); ?>">
				<div class="cert-list-title"><?php echo $title; ?></div>
				<div class="certificate-list"><?php echo $certificate_list; ?></div>
			</div>

			<?php

			$shortcode_html = ob_get_clean();
		}

		return $shortcode_html;

	}

	/**
	 * get arrary of Quezes taken
	 * modified from code in wp-content/plugins/sfwd-lms/course_info_widget.php
	 * @return array
	 */
	private static function quiz_attempts() {

		$quiz_attempts = array();
		$current_user  = wp_get_current_user();

		if ( empty( $current_user->ID ) ) {
			return $quiz_attempts;
		}

		$user_id            = $current_user->ID;
		$quiz_attempts_meta = get_user_meta( $user_id, '_sfwd-quizzes', true );
		$count              = 0;
		if ( ! ( empty( $quiz_attempts_meta ) || false === $quiz_attempts_meta ) ) {
			foreach ( $quiz_attempts_meta as $quiz_attempt ) {
				$quiz_attempt['post'] = get_post( $quiz_attempt['quiz'] );
				$c                    = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
				if (
					get_current_user_id() == $user_id &&
					! empty( $c['certificateLink'] ) &&
					(
					( isset( $quiz_attempt['percentage'] ) &&
					  $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100
					)
					)
				) {
					$quiz_attempt['certificate']          = $c;
					$quiz_attempt['certificate']['count'] = $count;
				}

				$quiz_attempts[] = $quiz_attempt;
				$count ++;
			}
		}

		return $quiz_attempts;
	}
}
