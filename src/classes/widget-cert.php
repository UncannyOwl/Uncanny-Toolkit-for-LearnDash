<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WidgetCert
 *
 * @package uncanny_custom_toolkit
 */
class WidgetCert extends \WP_Widget implements RequiredFunctions {

	static $instance;
	static $order;
	static $order_by;

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'certificate-widget';
		$class_title       = esc_html__( 'Certificate Widget', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/learndash-certificate-widget/';
		$class_description = esc_html__( 'Custom Widget that displays all certificates the user has earned.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-bookmark "></i>';
		$category          = 'learndash';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
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
	 */
	public static function get_class_settings( $class_title ) {
		// Create options
		$options = array(
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Display Certificate title instead of Course/Quiz title', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uncanny-widgetcertificate-show-cert-title',
			),
		);

		// Build html
		$html = Config::settings_output(
			array(
				'class'   => __CLASS__,
				'title'   => $class_title,
				'options' => $options,
			)
		);

		return $html;
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
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @return null
	 * @see WP_Widget::widget()
	 */
	public function widget( $args, $instance ) {

		if ( ! is_user_logged_in() || true !== self::dependants_exist() ) {
			return '';
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		self::$order    = ! empty( $instance['order'] ) ? $instance['order'] : 'ASC';
		self::$order_by = ! empty( $instance['order_by'] ) ? $instance['order_by'] : 'title';

		/* GET Certificates For Courses*/
		$post_args = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		echo '<div class="uncanny-cert-widget-list">';

		echo '<ul>';

		$courses         = get_posts( $post_args );
		$show_cert_title = Config::get_settings_value( 'uncanny-widgetcertificate-show-cert-title', __CLASS__ );

		$certificate_list = array();

		foreach ( $courses as $course ) {

			$certificate_id     = learndash_get_setting( $course->ID, 'certificate' );
			$certificate_object = get_post( $certificate_id );

			if ( ! empty( $certificate_object ) ) {

				if ( 'on' === $show_cert_title ) {
					$certificate_title = $certificate_object->post_title;
				} else {
					$certificate_title = $course->post_title;
				}
				$certificate_link = learndash_get_course_certificate_link( $course->ID, get_current_user_id() );

				if ( $certificate_link && '' !== $certificate_link ) {

					$date_earned                     = learndash_user_get_course_completed_date( get_current_user_id(), $course->ID );
					$certificate_list[ $course->ID ] = array(
						'title'       => $certificate_title,
						'date_earned' => $date_earned,
						'link'        => sprintf(
							'<li><a %s target="_blank" href="%s" title="%s %s" >%s</a></li>',
							'data-date-earned="' . $date_earned . '"',
							esc_url( $certificate_link ),
							esc_html__( 'Your certificate for :', 'uncanny-learndash-toolkit' ),
							$course->post_title,
							esc_html( $certificate_title )
						),
					);
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
						$date_earned        = $quiz_attempt['completed'];

						if ( 'on' === $show_cert_title ) {
							$certificate_title = $certificate_object->post_title;
						} else {
							$certificate_title = $quiz_title;
						}

						$certificate_list[ $quiz_attempt['post']->ID ] = array(
							'title'       => $certificate_title,
							'date_earned' => $date_earned,
							'link'        => sprintf(
								'<li><a %s target="_blank" href="%s" title="%s %s" >%s</a></li>',
								'data-date-earned="' . $date_earned . '"',
								esc_url( $certificateLink ),
								esc_html__( 'Your certificate for :', 'uncanny-learndash-toolkit' ),
								$quiz_title,
								esc_html( $certificate_title )
							),
						);
					}
				}
			}
		}

		$group_args = array(
			'post_type'      => 'groups',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$groups = get_posts( $group_args );

		foreach ( $groups as $group ) {

			$certificate_id     = learndash_get_setting( $group->ID, 'certificate' );
			$certificate_object = get_post( $certificate_id );

			if ( ! empty( $certificate_object ) ) {

				if ( 'on' === $show_cert_title ) {
					$certificate_title = $certificate_object->post_title;
				} else {
					$certificate_title = $group->post_title;
				}
				$certificate_link = learndash_get_group_certificate_link( $group->ID, get_current_user_id() );

				if ( $certificate_link && '' !== $certificate_link ) {

					$date_earned                    = learndash_user_get_course_completed_date( get_current_user_id(), $group->ID );
					$certificate_list[ $group->ID ] = array(
						'title'       => $certificate_title,
						'date_earned' => $date_earned,
						'link'        => sprintf(
							'<li><a %s target="_blank" href="%s" title="%s %s" >%s</a></li>',
							'data-date-earned="' . $date_earned . '"',
							esc_url( $certificate_link ),
							esc_html__( 'Your certificate for:', 'uncanny-learndash-toolkit' ),
							$group->post_title,
							esc_html( $certificate_title )
						),
					);
				}
			}
		}

		$certificate_list = apply_filters( 'certificate_list_widget', $certificate_list );

		if ( empty( $certificate_list ) ) {
			printf( '<p>%s</p>', esc_html( $instance['no_certs'] ) );
		} else {
			// sort by name
			usort( $certificate_list, array( __CLASS__, 'cmp' ) );

			foreach ( $certificate_list as $certificate ) {
				echo $certificate['link'];
			}
		}

		echo '</ul>';

		//close .uncanny-cert-widget-list
		echo '</div>';

		echo $args['after_widget'];

		return null;
	}

	public static function cmp( $a, $b ) {

		switch ( self::$order_by ) {
			case 'title':
				if ( 'DESC' === self::$order ) {
					return strcmp( strtolower( $b['title'] ), strtolower( $a['title'] ) );
				}
				if ( 'ASC' === self::$order ) {
					// Default to sort by start date asc
					return strcmp( strtolower( $a['title'] ), strtolower( $b['title'] ) );
				}
				break;
			case 'date_earned':
				if ( 'DESC' === self::$order ) {
					return ( $a['date_earned'] > $b['date_earned'] ) ? - 1 : 1;
				}
				if ( 'ASC' === self::$order ) {
					// Default to sort by start date asc
					return ( $a['date_earned'] < $b['date_earned'] ) ? - 1 : 1;
				}
				break;
		}
	}

	/**
	 * get array of Quizzes taken
	 * modified from code in wp-content/plugins/sfwd-lms/course_info_widget.php
	 *
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
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 * @see WP_Widget::form()
	 */
	public function form(
		$instance
	) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Your certificates', 'uncanny-learndash-toolkit' );
		$no_certs = ! empty( $instance['no_certs'] ) ? $instance['no_certs'] : esc_html__( 'Complete courses to earn certificates', 'uncanny-learndash-toolkit' );

		$order    = ! empty( $instance['order'] ) ? $instance['order'] : 'ASC';
		$order_by = ! empty( $instance['order_by'] ) ? $instance['order_by'] : 'title';

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
		<p>
			<label
					for="<?php echo $this->get_field_id( 'order' ); ?>"><?php esc_html_e( 'Certificate Order:', 'uncanny-learndash-toolkit' ); ?></label><br>
			<input id="<?php echo $this->get_field_id( 'order' ); ?>"
				   name="<?php echo $this->get_field_name( 'order' ); ?>"
				   class="widefat"
				   type="radio"
				<?php echo ( 'ASC' === $order ) ? 'checked' : ''; ?>
				   value="ASC"> <?php esc_html_e( 'Ascending', 'uncanny-learndash-toolkit' ); ?><br>
			<input id="<?php echo $this->get_field_id( 'order' ); ?>"
				   name="<?php echo $this->get_field_name( 'order' ); ?>"
				   class="widefat"
				   type="radio"
				<?php echo ( 'DESC' === $order ) ? 'checked' : ''; ?>
				   value="DESC"> <?php esc_html_e( 'Descending', 'uncanny-learndash-toolkit' ); ?>
		</p>
		<p>
			<label
					for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php esc_html_e( 'Certificate Order By:', 'uncanny-learndash-toolkit' ); ?></label><br>

			<input id="<?php echo $this->get_field_id( 'order_by' ); ?>"
				   name="<?php echo $this->get_field_name( 'order_by' ); ?>"
				   class="widefat"
				   type="radio"
				<?php echo ( 'title' === $order_by ) ? 'checked' : ''; ?>
				   value="title"> <?php esc_html_e( 'Title', 'uncanny-learndash-toolkit' ); ?><br>

			<input id="<?php echo $this->get_field_id( 'order_by' ); ?>"
				   name="<?php echo $this->get_field_name( 'order_by' ); ?>"
				   class="widefat"
				   type="radio"
				<?php echo ( 'date_earned' === $order_by ) ? 'checked' : ''; ?>
				   value="date_earned"> <?php esc_html_e( 'Date Earned', 'uncanny-learndash-toolkit' ); ?><br>

		</p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 */
	public function update(
		$new_instance, $old_instance
	) {
		$instance             = array();
		$instance['title']    = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['no_certs'] = ( ! empty( $new_instance['no_certs'] ) ) ? strip_tags( $new_instance['no_certs'] ) : '';
		$instance['order']    = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : '';
		$instance['order_by'] = ( ! empty( $new_instance['order_by'] ) ) ? strip_tags( $new_instance['order_by'] ) : '';

		return $instance;
	}
}
