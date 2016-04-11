<?php

namespace uncanny_learndash_toolkit;

if (!defined('WPINC')) {
	die;
}

/**
 * Class WidgetCert
 * @package uncanny_custom_toolkit
 */
class WidgetCert extends \WP_Widget implements RequiredFunctions
{
	static $instance;

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details()
	{
		$class_title = esc_html__('LearnDash Certificate Widget', Config::get_text_domain());
		$kb_link = null;
		$class_description = esc_html__('Custom Widget that displays all certificates the user has earned.', Config::get_text_domain());
		$class_icon = '<i class="uo_icon_fa fa fa-bookmark "></i>';

		return array(
			'title' => $class_title,
			'kb_link' => $kb_link, // OR set as null not to display
			'description' => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings' => false,
			'icon' => $class_icon,
		);
	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * return boolean || string TRUE or name of function or plugin that is needed
	 */
	public static function dependants_exist()
	{
		global $learndash_post_types;
		if (!isset($learndash_post_types)) {
			return 'Plugin: LearnDash';
		}

		return true;
	}


	/**
	 * class constructor
	 */
	public function __construct()
	{

		// load the cert widget
		add_action('widgets_init', array(__CLASS__, 'register_cert_widget'));

		parent::__construct(
			'uncanny_lms_cert_list', // Base ID
				esc_html__('Certificates - Uncanny Toolkit', Config::get_text_domain()), // Name
			array('description' => esc_html__('Adds a new certificate widget that outputs links to all LearnDash certificates earned by the user.', 'text_domain')) // Args
		);
	}

	// register LMS cert widget
	public static function register_cert_widget()
	{
		register_widget(__NAMESPACE__ . '\WidgetCert');
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
	public function widget($args, $instance)
	{

		if (!is_user_logged_in()) {
			return '';
		}

		echo $args['before_widget'];

		if (!empty($instance['title'])) {
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}

		/* GET Certificates For Courses*/
		$args = array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC',
		);

		$courses = get_posts($args);

		$certificate_list = '';
		$certificate_titles = Array();

		foreach ($courses as $course) {

			$certificate_id = learndash_get_setting($course->ID, 'certificate');
			$certificate_object = get_post($certificate_id);
			$certificate_title = $certificate_object->post_title;
			$certificate_link = learndash_get_course_certificate_link($course->ID);

			if ($certificate_link && '' !== $certificate_link) {

				if ($certificate_link && '' !== $certificate_link) {
					if (!in_array($certificate_title, $certificate_titles)) {
						$certificate_list .= '<li><a target="_blank" href="' . $certificate_link . '" title="' . esc_html__('Your certificate for :', Config::get_text_domain() . $course->post_title) . '">' . $certificate_title . '</a></li>';
						array_push($certificate_titles, $certificate_title);
					}
				}


			}
		}

		$quiz_attempts = self::quiz_attempts();

		echo '<div class="uncanny-cert-widget-list">';

		if (!empty($quiz_attempts) || '' !== $certificate_list) {

			echo '<ul>';

			echo $certificate_list;

			$quiz_attempts = array_reverse($quiz_attempts);

			foreach ($quiz_attempts as $k => $quiz_attempt) {

				$certificateLink = $quiz_attempt['certificate']['certificateLink'];
				$quiz_title = !empty($quiz_attempt['post']->post_title) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];

				if (!empty($certificateLink)) {
					$meta = get_post_meta($quiz_attempt['post']->ID, '_sfwd-quiz', true);
					$certificate_id = $meta['sfwd-quiz_certificate'];
					$certificate_object = get_post($certificate_id);
					$certificate_title = $certificate_object->post_title;

					if (!in_array($certificate_title, $certificate_titles)) {
						printf('<li><a target="_blank" href="%s" title="%s" > %s</a></li>',
							esc_url($certificateLink),
							esc_html__('Your certificate for :', Config::get_text_domain() . $quiz_title),
							esc_html($certificate_title)
						);
						array_push($certificate_titles, $certificate_title);
					}
				}
			}

			echo '</ul></div>';

		} else {
			printf('<p>%s</p></div>', esc_html($instance['no_certs']));
		}

		//echo $args['after_widget'];
	}

	/**
	 * get array of Quizzes taken
	 * modified from code in wp-content/plugins/sfwd-lms/course_info_widget.php
	 * @return array
	 */
	private static function quiz_attempts()
	{

		$quiz_attempts = array();
		$current_user = wp_get_current_user();

		if (empty($current_user->ID)) {
			return $quiz_attempts;
		}

		$user_id = $current_user->ID;
		$quiz_attempts_meta = get_user_meta($user_id, '_sfwd-quizzes', true);
		if (!(empty($quiz_attempts_meta) || false === $quiz_attempts_meta)) {
			foreach ($quiz_attempts_meta as $quiz_attempt) {
				$quiz_attempt['post'] = get_post($quiz_attempt['quiz']);
				$c = learndash_certificate_details($quiz_attempt['quiz'], $user_id);
				if (
					get_current_user_id() === $user_id &&
					!empty($c['certificateLink']) &&
					(
					(isset($quiz_attempt['percentage']) &&
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
	public function form($instance)
	{
		$title = !empty($instance['title']) ? $instance['title'] : esc_html__('Your certificates', Config::get_text_domain());
		$no_certs = !empty($instance['no_certs']) ? $instance['no_certs'] : esc_html__('Complete courses to earn certificates', Config::get_text_domain());

		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
				   name="<?php echo $this->get_field_name('title'); ?>" type="text"
				   value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id('no_certs'); ?>"><?php esc_html_e('No certificates message:', Config::get_text_domain()); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('no_certs'); ?>"
				   name="<?php echo $this->get_field_name('no_certs'); ?>" type="text"
				   value="<?php echo esc_attr($no_certs); ?>">
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
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['no_certs'] = (!empty($new_instance['no_certs'])) ? strip_tags($new_instance['no_certs']) : '';

		return $instance;
	}
}
