<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}

class ShowCertificatesShortcode extends Config implements RequiredFunctions{

	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if( true === self::dependants_exist() ){

			// Show quiz and course certificates shortcode
			add_shortcode( 'uo-learndash-certificates', array( __CLASS__, 'learndash_certificates' ) );

		}
	}

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {
		$class_title = __( 'Show LearnDash Certificates', Config::get_text_domain() );
		$class_description = __( '
					<p>List of all earned certificates</p>
					<p><strong>[learndash-certificates]</strong></p>
					<p><strong>[learndash-certificates title="Your Certificates" class="custom-css-class"]</strong></p>'
			, Config::get_text_domain() );
		$class_icon = '<span class="uo_icon_text">[/ ]</span>';//'<span class="dashicons dashicons-admin-users"></span>';
		return array( 'title' => $class_title, 'description' => $class_description, 'dependants_exist' => self::dependants_exist(), 'icon' => $class_icon );
	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * return boolean || string Return either true or name of function or plugin
	 */
	public static function dependants_exist(){
		global $learndash_post_types;
		if( !isset($learndash_post_types) ){
			return 'Plugin: LearnDash';
		}
		return true;
	}
	public static function learndash_certificates($atts){

		if( isset($atts['class']) ){
			$class = $atts['class'];
		}else{
			$class = 'certificate-list-container';
		}

		if( isset($atts['title']) ){
			$title = $atts['title'];
		}else{
			$title = 'Your Certificates';
		}

		$args = array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC');

		$courses = get_posts($args);

		$certificate_list = '';
		foreach($courses as $course) {

			$certificate_link = learndash_get_course_certificate_link( $course->ID );
			if( $certificate_link && '' !== $certificate_link ){
				$certificate_list .= '<a href="'.$certificate_link.'">'.$course->post_title.'</a>';
			}
		}

		ob_start();
		?>
			<div class="<?php echo $class; ?>">
				<div class="cert-list-title"><?php echo $title; ?></div>
				<div class="certificate-list"><?php echo $certificate_list; ?></div>
			</div>

		<?php

		$shortcode_html = ob_get_clean();
		return $shortcode_html;

	}

	public static function learndash_certificates_quizzes($atts){

		$atts = shortcode_atts(
			array(
				'list_height'  => 5,
				'show_fails'  => false,
				'more_certs'    => 'yes',
				'no_certs'    => 'You have not earned any yet'
			),
			$atts, 'learndash-certificates' );
		ob_start();
		$quiz_attempts = self::quiz_attempts();
		//var_dump($quiz_attempts );
		/*if ( ! array_key_exists( 'list_height', $instance ) ) {
			$instance['list-height'] = 5;

		}*/

		printf( '<div class="uncanny-cert-widget-list uncanny-cert-widget-%d" data-row="%d">',
			count( $quiz_attempts ),
			( count( $quiz_attempts ) > $atts['list_height'] ) ? $atts['list_height'] : count( $quiz_attempts )
		);
		var_dump( $quiz_attempts );
		if ( ! empty( $quiz_attempts ) ) {
			echo '<ul>';
			$quiz_attempts = array_reverse( $quiz_attempts );
			foreach ( $quiz_attempts as $k => $quiz_attempt ) {

				if( isset($quiz_attempt["certificate"]) ) {

					$certificateLink = $quiz_attempt["certificate"]["certificateLink"];
					$count = $quiz_attempt["certificate"]["count"];
					$quiz_title = !empty($quiz_attempt["post"]->post_title) ? $quiz_attempt["post"]->post_title : @$quiz_attempt['quiz_title'];

					if (!empty($certificateLink)) {
						printf('<li><a href="%s" title="%s" class="count-%d"> %s</a></li>',
							esc_url($certificateLink),
							esc_html(__('your certificate for :', Config::get_text_domain()) . $quiz_title),
							$count,
							esc_html($quiz_title)
						);
					} else {
						// show the failers
						if (true === $atts['show_fails']) {
							printf('<li>%s</li>', esc_html($quiz_title));
						} else {
							//remove so its not counted
							unset($quiz_attempts[$k]);
						}
					}
				}else{
					printf( '<p>%s</p></div>', esc_html( $atts['no_certs'] ) );
				}

			}
			echo '</ul></div>';

			// add the show more link one and more certs
			if ( ( $atts['list_height'] < count( $quiz_attempts ) ) && ! empty( $atts['more_certs'] ) ) {
				printf( '<a href="#" class="uncanny-cert-more-link">%s</a>', $atts['more_certs'] );
				// and add the js to make it work
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'add_js_to_footer' ) );
			}
		} else {
			printf( '<p>%s</p></div>', esc_html( $atts['no_certs'] ) );
		}

		$contents = ob_get_clean();

		return $contents;
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
					$user_id == get_current_user_id() &&
					! empty( $c["certificateLink"] ) &&
					(
					( isset( $quiz_attempt['percentage'] ) &&
						$quiz_attempt['percentage'] >= $c["certificate_threshold"] * 100
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