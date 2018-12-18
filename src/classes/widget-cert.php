<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WidgetCert
 * @package uncanny_custom_toolkit
 */
class WidgetCert extends \WP_Widget implements RequiredFunctions {
	static $instance;

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$class_title       = esc_html__( 'Certificate Widget', 'uncanny-learndash-toolkit' );
		$kb_link           = 'http://www.uncannyowl.com/knowledge-base/learndash-certificate-widget/';
		$class_description = esc_html__( 'Custom Widget that displays all certificates the user has earned.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-bookmark "></i>';
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
	 * return boolean || string TRUE or name of function or plugin that is needed
	 */
	public static function dependants_exist() {
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
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
			esc_html__( 'Certificates - Uncanny Toolkit', 'uncanny-learndash-toolkit' ), // Name
			array( 'description' => esc_html__( 'Adds a new certificate widget that outputs links to all LearnDash certificates earned by the user.', 'text_domain' ) ) // Args
		);
	}

	// register LMS cert widget
	public static function register_cert_widget() {
		register_widget( __NAMESPACE__ . '\WidgetCert' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @return null
	 */
	public function widget( $args, $instance ) {

		if ( ! is_user_logged_in() ) {
			return '';
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		/* GET Certificates For Courses*/
		$post_args = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		echo '<div class="uncanny-cert-widget-list">';

		echo '<ul>';

		$courses = get_posts( $post_args );

		$certificate_list = '';

		foreach ( $courses as $course ) {

			$certificate_id     = learndash_get_setting( $course->ID, 'certificate' );
			$certificate_object = get_post( $certificate_id );

			if ( ! empty( $certificate_object ) ) {
				$certificate_title = $certificate_object->post_title;
				$certificate_link  = learndash_get_course_certificate_link( $course->ID );

				if ( $certificate_link && '' !== $certificate_link ) {
					$certificate_list .= '<li><a target="_blank" href="' . $certificate_link . '" title="' . esc_html__( 'Your certificate for: ', 'uncanny-learndash-toolkit' ) . $course->post_title . '">' . $certificate_title . '</a></li>';
				}
			}
		}

		$quiz_attempts = self::quiz_attempts();

		if ( ! empty( $quiz_attempts ) ) {

			$quiz_attempts = array_reverse( $quiz_attempts );

			foreach ( $quiz_attempts as $k => $quiz_attempt ) {

				if ( isset( $quiz_attempt['certificate'] ) ) {

					$certificateLink = $quiz_attempt['certificate']['certificateLink'];
					$quiz_title      = ! empty( $quiz_attempt['post']->post_title ) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];

					if ( ! empty( $certificateLink ) ) {
						$meta               = get_post_meta( $quiz_attempt['post']->ID, '_sfwd-quiz', true );
						$certificate_id     = $meta['sfwd-quiz_certificate'];
						$certificate_object = get_post( $certificate_id );
						$certificate_title  = $certificate_object->post_title;

						$certificate_list .= sprintf( '<li><a target="_blank" href="%s" title="%s %s" >%s</a></li>',
							esc_url( $certificateLink ),
							esc_html__( 'Your certificate for :', 'uncanny-learndash-toolkit' ),
							$quiz_title,
							esc_html( $certificate_title )
						);
					}
				}
			}
		}

		$certificate_list = apply_filters( 'certificate_list_widget', $certificate_list );

		if ( '' === $certificate_list ) {
			printf( '<p>%s</p>', esc_html( $instance['no_certs'] ) );
		} else {
			echo $certificate_list;
		}


		echo '</ul>';

		//close .uncanny-cert-widget-list
		echo '</div>';

		echo $args['after_widget'];

		return null;
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
		if ( ! ( empty( $quiz_attempts_meta ) || false === $quiz_attempts_meta ) ) {
			foreach ( $quiz_attempts_meta as $quiz_attempt ) {
				$quiz_attempt['post'] = get_post( $quiz_attempt['quiz'] );
				$c                    = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
				if (
					get_current_user_id() === $user_id &&
					! empty( $c['certificateLink'] ) &&
					(
					( isset( $quiz_attempt['percentage'] ) &&
					  $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100
					)
					)
				) {
					$quiz_attempt['certificate'] = $c;
				}

				$quiz_attempts[] = $quiz_attempt;
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
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Your certificates', 'uncanny-learndash-toolkit' );
		$no_certs = ! empty( $instance['no_certs'] ) ? $instance['no_certs'] : esc_html__( 'Complete courses to earn certificates', 'uncanny-learndash-toolkit' );

		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label
                    for="<?php echo $this->get_field_id( 'no_certs' ); ?>"><?php esc_html_e( 'No certificates message:', 'uncanny-learndash-toolkit' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'no_certs' ); ?>"
                   name="<?php echo $this->get_field_name( 'no_certs' ); ?>" type="text"
                   value="<?php echo esc_attr( $no_certs ); ?>">
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
		$instance             = array();
		$instance['title']    = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['no_certs'] = ( ! empty( $new_instance['no_certs'] ) ) ? strip_tags( $new_instance['no_certs'] ) : '';

		return $instance;
	}
}
