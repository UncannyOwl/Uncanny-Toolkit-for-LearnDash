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

		$class_title       = esc_html__( 'Show LearnDash Certificates', 'uncanny-learndash-toolkit' );
		$kb_link           = 'http://www.uncannyowl.com/knowledge-base/show-learndash-certificates/';
		$class_description = esc_html__( 'Displays a list of LearnDash certificates (both course and quiz) earned by the user, with the most recent at the top.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-certificate"></i>';
		$tags              = 'learndash';
		$type              = 'free';

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'tags'             => $tags,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'icon'             => $class_icon,
		);

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

		$certificate_list = '';

		/* GET Certificates For Courses*/
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
				$certificate_title = $certificate_object->post_title;
				$certificate_link  = learndash_get_course_certificate_link( $course->ID );

				if ( $certificate_link && '' !== $certificate_link ) {
					$certificate_list .= '<a target="_blank" href="' . $certificate_link . '">' . $certificate_title . '</a><br>';
				}
			}


		}

		/* GET Certificates for Quizzes*/
		$quiz_attempts = self::quiz_attempts();

		if ( ! empty( $quiz_attempts ) ) {

			$quiz_attempts = array_reverse( $quiz_attempts );

			foreach ( $quiz_attempts as $k => $quiz_attempt ) {

				if ( isset( $quiz_attempt['certificate'] ) ) {
					$certificateLink     = $quiz_attempt['certificate']['certificateLink'];
					$quiz_title_fallback = ( isset( $quiz_attempt['quiz_title'] ) ) ? $quiz_attempt['quiz_title'] : '';
					$quiz_title          = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : $quiz_title_fallback;

					if ( ! empty( $certificateLink ) ) {

						$meta               = get_post_meta( $quiz_attempt['post']->ID, '_sfwd-quiz', true );
						$certificate_id     = $meta['sfwd-quiz_certificate'];
						$certificate_object = get_post( $certificate_id );
						$certificate_title  = $certificate_object->post_title;

						$certificate_list .= '<a target="_blank" href="' . esc_url( $certificateLink ) . '">' . $certificate_title . '</a><br>';

					}
				}
			}
		}

		$certificate_list = apply_filters( 'certificate_list_shortcode', $certificate_list );

		if ( '' === $certificate_list ) {
			$certificate_list = $no_cert_message;
		}

		ob_start();
		?>
        <div class="<?php echo esc_attr( $class ); ?>">
            <div class="cert-list-title"><?php echo $title; ?></div>
            <div class="certificate-list"><?php echo $certificate_list; ?></div>
        </div>

		<?php

		$shortcode_html = ob_get_clean();

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
