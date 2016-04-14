<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Sample
 * @package uncanny_custom_toolkit
 */
class Sample extends Config implements RequiredFunctions {

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

			/* ADD FILTERS ACTIONS FUNCTION */

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title = esc_html__( 'Sample Title', 'uncanny-learndash-toolkit' );

		$kb_link = 'http://www.uncannyowl.com';

		/* Sample Simple Description with shortcode */
		$class_description = esc_html__( 'Sample Description B</p>', 'uncanny-learndash-toolkit' );

		/* Icon as text - max four characters wil fit */
		$class_icon = '<span class="uo_icon_text">[ /]</span>'; // Shortcode

		/* Icon as wp dashicon */
		$class_icon = '<span class="uo_icon_dashicon dashicons dashicons-admin-users"></span>';

		/* Icon as fontawesome icon */
		$class_icon = '<i class="uo_icon_fa fa fa-calendar"></i>';

		/* Icon as img */
		//icons have variable widths and hieght
		$icon_styles = 'width: 40px;  padding-top: 5px; padding-left: 9px;';
		$class_icon  = '<img style="' . $icon_styles . '" src="' . self::get_admin_media( 'gravity-forms-icon.png' ) . '" />';


		return array(
			'title'            => $class_title,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			/*'settings' => false, // OR */
			'settings'         => self::get_class_settings( $class_title ),
			'icon'             => $class_icon,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {

		/* Checks for LearnDash */
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		/* Check for gravity forms */
		if ( ! has_action( 'gform_loaded' ) ) {
			return 'Plugin: Gravity Forms';
		}

		// Return true if no dependency or dependency is available
		return true;


	}


	/**
	 * HTML for modal to create settings
	 *
	 * @param $class_title
	 *
	 * @return string
	 */
	public static function get_class_settings( $class_title ) {

		// Get pages to populate drop down
		$args = array(
			'sort_order'  => 'asc',
			'sort_column' => 'post_title',
			'post_type'   => 'page',
			'post_status' => 'publish',
		);

		$pages     = get_pages( $args );
		$drop_down = array();
		array_push( $drop_down, array( 'value' => 0, 'text' => '- Select Page -' ) );

		foreach ( $pages as $page ) {
			array_push( $drop_down, array( 'value' => $page->ID, 'text' => $page->post_title ) );
		}

		// Create options
		$options = array(

			array(
				'type'       => 'html',
				'class'      => 'uo-additional-information',
				'inner_html' => __( '<div>Some Extra Information for the user</div>', 'uncanny-learndash-toolkit' ),
			),

			array(
				'type'        => 'checkbox',
				'label'       => 'Settings A',
				'option_name' => 'a',
			),

			array(
				'type'        => 'text',
				'label'       => 'Settings B',
				'option_name' => 'b',
			),

			array(
				'type'       => 'radio',
				'label'      => 'Settings Gender',
				'radio_name' => 'uo_gender',
				'radios'     => array(
					array( 'value' => 'male', 'text' => 'Male' ),
					array( 'value' => 'female', 'text' => 'Female' ),
					array( 'value' => 'other', 'text' => 'Other' ),
				),
			),

			array(
				'type'        => 'select',
				'label'       => 'Settings Car',
				'select_name' => 'car',
				'options'     => array(
					array( 'value' => 'volvo', 'text' => 'Volvo' ),
					array( 'value' => 'saab', 'text' => 'Saab' ),
					array( 'value' => 'ford', 'text' => 'Ford' ),
				),
			),

			array(
				'type'        => 'select',
				'label'       => 'Login Page',
				'select_name' => 'login_page',
				'options'     => $drop_down,
			),

		);

		// Build html
		$html = self::settings_output( array(
			'class'   => __CLASS__,
			'title'   => $class_title,
			'options' => $options,
		) );

		return $html;
	}
}
