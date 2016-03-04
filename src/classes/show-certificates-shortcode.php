<?php

namespace uncanny_learndash_public;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class ShowCertificatesShortcode extends Config implements RequiredFunctions {

	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if ( true === self::dependants_exist() ) {

			// Show quiz and course certificates shortcode
			add_shortcode( 'uo-learndash-certificates', array( __CLASS__, 'learndash_certificates' ) );
		}
	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$class_title       = __( 'Show LearnDash Certificates', Config::get_text_domain() );
		$class_description = __(
			'<p>List of all earned certificates</p>
			<p><strong>[learndash-certificates]</strong></p>
			<p><strong>[learndash-certificates title="Your Certificates" class="custom-css-class"]</strong></p>', Config::get_text_domain() );
		$class_icon        = '<span class="uo_icon_text">[/ ]</span>';//'<span class="dashicons dashicons-admin-users"></span>';
		return array( 'title' => $class_title, 'description' => $class_description, 'dependants_exist' => self::dependants_exist(), 'icon' => $class_icon );
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

		if ( isset( $atts['class'] ) ) {
			$class = $atts['class'];
		} else {
			$class = 'certificate-list-container';
		}

		if ( isset( $atts['title'] ) ) {
			$title = $atts['title'];
		} else {
			$title = __( 'Your Certificates', Config::get_text_domain() );
		}

		if ( isset( $atts['no-cert-message'] ) ) {
			$no_cert_message = $atts['no-cert-message'];
		} else {
			$no_cert_message = __( 'Complete courses to earn certificates', Config::get_text_domain() );
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
		$certificate_link = '';
		foreach ( $courses as $course ) {

			$certificate_link = learndash_get_course_certificate_link( $course->ID );
			if ( $certificate_link && '' !== $certificate_link ) {
				$certificate_list .= '<a href="' . $certificate_link . '">' . $course->post_title . '</a><br>';
			}
		}

		/* GET Certificates for Quizzes*/
		$quiz_attempts = self::quiz_attempts();

		if ( ! empty( $quiz_attempts ) ) {

			$quiz_attempts = array_reverse( $quiz_attempts );

			foreach ( $quiz_attempts as $k => $quiz_attempt ) {

				if ( isset( $quiz_attempt['certificate'] ) ) {
					$certificateLink = $quiz_attempt['certificate']['certificateLink'];
					$quiz_title_fallback = ( isset( $quiz_attempt['quiz_title'] ) ) ? $quiz_attempt['quiz_title'] : '';
					$quiz_title      = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : $quiz_title_fallback;

					if ( ! empty( $certificateLink ) ) {
						$certificate_list .= '<a href="' . esc_url( $certificate_link ) . '">' . esc_html( $quiz_title ) . '</a><br>';
					}
				}
			}
		}

		if ( '' === $certificate_list ) {
			$certificate_list = $no_cert_message;
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<div class="cert-list-title"><?php echo esc_html( $title ); ?></div>
			<div class="certificate-list"><?php echo esc_html( $certificate_list ); ?></div>
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
