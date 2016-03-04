<?php

namespace uncanny_learndash_public;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class WidgetCert extends \WP_Widget implements RequiredFunctions{
	static $instance;

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {
		$class_title = __( 'LearnDash Cert Widget', Config::get_text_domain() );
		$class_description = __( 'Custom Widget that displays all certificates the user has earned.', Config::get_text_domain() );
		$icon_styles = 'background: rgb(255, 255, 255); margin-top: 17px; width: 60px; padding: 2px 0;';
		$class_icon = '<img style="'. $icon_styles .'" src="'. Config::get_admin_media('LearnDash-Official-Logo.png') .'" />';

		return array( 	'title' => $class_title,
				'description' => $class_description,
				'dependants_exist' => self::dependants_exist(),
				'icon' => $class_icon );
	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * return boolean || string TRUE or name of function or plugin that is needed
	 */
	public static function dependants_exist(){
		global $learndash_post_types;
		if( !isset($learndash_post_types) ){
			return 'Plugin: LearnDash';
		}
		return true;
	}


	/**
	 * class constructor
	 */
	public function __construct() {

		// load the cert widget
		add_action( 'widgets_init', array( __CLASS__, 'register_cert_widget' ) );

		parent::__construct(
				'uncanny_lms_cert_list', // Base ID
				__( 'LMS Certificate List', Config::get_text_domain() ), // Name
				array( 'description' => __( 'The list of LMS certificates for the current user.', 'text_domain' ), ) // Args
		);
	}

	// register LMS cert widget
	public static function register_cert_widget() {
		register_widget( __NAMESPACE__ . '\WidgetCert' );
	}

	/**
	 * inject script into footer only load if needed
	 */
	public static function add_js_to_footer() {
		?>
		<script type="application/javascript">
			var row_height = jQuery('.uncanny-cert-widget-list li:first').height();
			var $list = jQuery('.uncanny-cert-widget-list');
			var row_count = $list.attr('data-row');
			var div_height = row_height * row_count + 'px';
			$list.css({height: div_height, overflow: 'hidden'});
			jQuery('.uncanny-cert-more-link').on('click', function (event) {
				event.preventDefault();
				var target_height = jQuery(this).parent().find('ul').height();
				var list_div = jQuery(this).parent().children('.uncanny-cert-widget-list');
				list_div.animate({height: target_height}, 500);
				jQuery(this).hide(); // hide as we are not toggling and this has no action
			});

		</script>
		<?php
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		/* GET Certificates For Courses*/
		$args = array(
				'post_type' => 'sfwd-courses',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'title',
				'order' => 'ASC');

		$courses = get_posts($args);

		$course_certificate_count = 0;
		$certificate_list = '';

		foreach($courses as $course) {

			$certificate_link = learndash_get_course_certificate_link( $course->ID );
			if( $certificate_link && '' !== $certificate_link ){
				$certificate_list .= '<li><a href="'.$certificate_link.'" title="'.esc_html( __( 'Your certificate for :', Config::get_text_domain() ) . $course->post_title ).'" class="count-'.$course_certificate_count.'">'.$course->post_title.'</a></li>';
				$course_certificate_count++;
			}
		}

		$quiz_attempts = self::quiz_attempts();

		printf( '<div class="uncanny-cert-widget-list uncanny-cert-widget-%d" data-row="%d">',
				count( $quiz_attempts )+$course_certificate_count,
				( count( $quiz_attempts ) > $instance['list_height'] ) ? $instance['list_height'] : count( $quiz_attempts )+$course_certificate_count
		);

		if ( ! empty( $quiz_attempts ) || '' !== $certificate_list ) {

			echo '<ul>';

			echo $certificate_list;

			$quiz_attempts = array_reverse( $quiz_attempts );

			foreach ( $quiz_attempts as $k => $quiz_attempt ) {

				$certificateLink = $quiz_attempt['certificate']['certificateLink'];
				$count           = $quiz_attempt['certificate']['count'];
				$quiz_title      = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];

				if ( ! empty( $certificateLink ) ) {
					/** @noinspection HtmlUnknownTarget */
					printf( '<li><a href="%s" title="%s" class="count-%d"> %s</a></li>',
							esc_url( $certificateLink ),
							esc_html( __( 'Your certificate for :', Config::get_text_domain() ) . $quiz_title ),
							$count+$course_certificate_count,
							esc_html( $quiz_title )
					);
				}

			}

			echo '</ul></div>';

			// add the show more link one and more certs
			if ( ( $instance['list_height'] < count( $quiz_attempts ) ) && ! empty( $instance['more_certs'] ) ) {
				printf( '<a href="#" class="uncanny-cert-more-link">%s</a>', $instance['more_certs'] );
				// and add the js to make it work
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'add_js_to_footer' ) );
			}

		} else {
			printf( '<p>%s</p></div>', esc_html( $instance['no_certs'] ) );
		}

		//echo $args['after_widget'];
	}

	/**
	 * get array of Quizzes taken
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

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 * @return string|void
	 */
	public function form( $instance ) {
		$title       = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Your certificates', Config::get_text_domain() );
		$no_certs    = ! empty( $instance['no_certs'] ) ? $instance['no_certs'] : __( 'Complete courses to earn certificates', Config::get_text_domain() );
		$more_certs  = ! empty( $instance['more_certs'] ) ? $instance['more_certs'] : __( 'see more certificates', Config::get_text_domain() );
		$list_height = ! empty( $instance['list_height'] ) ? $instance['list_height'] : _X( '5', 'list height in px', Config::get_text_domain() );
		$show_fails  = ! empty( $instance['show_fails'] ) ? (bool) $instance['show_fails'] : false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label
					for="<?php echo $this->get_field_id( 'no_certs' ); ?>"><?php _e( 'No certificates message:', Config::get_text_domain() ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'no_certs' ); ?>"
				   name="<?php echo $this->get_field_name( 'no_certs' ); ?>" type="text"
				   value="<?php echo esc_attr( $no_certs ); ?>">
		</p>
		<p>
			<label
					for="<?php echo $this->get_field_id( 'more_certs' ); ?>"><?php _e( 'More certificates message:', Config::get_text_domain() ); ?></label><br/>
			<input class="widefat" id="<?php echo $this->get_field_id( 'more_certs' ); ?>"
				   name="<?php echo $this->get_field_name( 'more_certs' ); ?>" type="text"
				   value="<?php echo esc_attr( $more_certs ); ?>">
		</p>
		<p>
			<label
					for="<?php echo $this->get_field_id( 'list_height' ); ?>"><?php _e( 'The max number of certificates shown at the start:', Config::get_text_domain() ); ?></label>
			<input class="" id="<?php echo $this->get_field_id( 'list_height' ); ?>"
				   name="<?php echo $this->get_field_name( 'list_height' ); ?>" type="text"
				   value="<?php echo absint( $list_height ); ?>">
		</p>
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'show_fails' ); ?>"
				   name="<?php echo $this->get_field_name( 'show_fails' ); ?>" type="checkbox"
				   value="true" <?php checked( true, $show_fails, true ) ?>>
			<label
					for="<?php echo $this->get_field_id( 'show_fails' ); ?>"><?php _e( 'Show Quizzes that the user failed', Config::get_text_domain() ); ?></label>

		</p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = array();
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['no_certs']    = ( ! empty( $new_instance['no_certs'] ) ) ? strip_tags( $new_instance['no_certs'] ) : '';
		$instance['more_certs']  = ( ! empty( $new_instance['more_certs'] ) ) ? strip_tags( $new_instance['more_certs'] ) : '';
		$instance['list_height'] = ( ! empty( $new_instance['list_height'] ) ) ? absint( $new_instance['list_height'] ) : '5';
		$instance['show_fails']  = ( true == $new_instance['show_fails'] ) ? true : false;

		return $instance;
	}


}