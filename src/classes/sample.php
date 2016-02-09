<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}

class Sample extends Config implements RequiredFunctions{


	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if( true === self::dependants_exist()){

			/* ADD FILTERS ACTIONS FUNCTION */

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {

		$class_title = __( 'Sample Title', self::get_text_domain() );

		/* Sample Simple Description */
		$class_description = __( 'Sample Description A', self::get_text_domain() );

		/* Sample Simple Description with shortcode */
		$class_description = __( '
				<p>Sample Description B</p>
				<p><strong>[sample-shortcode]</strong></p>
				',
			self::get_text_domain() );

		/* Icon as text - max four characters wil fit */
		$class_icon = '<span class="uo_icon_text">[/ ]</span>'; // Shortcode

		/* Icon as wp dashicon */
		$class_icon = '<span class="uo_icon_dashicon dashicons dashicons-admin-users"></span>';

		/* Icon as img */
		//icons have variable widths and hieght
		$icon_styles = 'width: 40px;  padding-top: 5px; padding-left: 9px;';
		$class_icon = '<img style="'. $icon_styles .'" src="'. self::get_admin_media('gravity-forms-icon.png') .'" />';


		return array( 	'title' => $class_title,
						'description' => $class_description,
						'dependants_exist' => self::dependants_exist(),
						'settings' => false, // OR 'settings' => self::get_class_settings( $class_title ),
						'icon' => $class_icon );

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist(){

		/* Checks for LearnDash */
		global $learndash_post_types;
		if( !isset($learndash_post_types) ){
			return 'Plugin: LearnDash';
		}

		/* Check for gravity forms */
		if( ! has_action( 'gform_loaded' ) ){
			return 'Plugin: Gravity Forms';
		}

		// Return true if no dependency or dependency is available
		return true;
	}

	/**
	* HTML for modal to create settings
	*
	* @return boolean || string Return either false or settings html modal
	*
	*/
	public static function get_class_settings( $class_title ){

		// Create options
		$options = array(

				'checkbox' => array(
						'label' => 'Settings A',
						'option_name' => 'a'
				),

				'radio' => array(
						'label' => 'Settings Gender',
						'input_name' => 'gender',
						'value' => array('Male','Female','Other')
				),

				'select' => array(
						'label' => 'Settings Car',
						'select_name' => 'car',
						'value' => array('Volvo','Saab','Mercedes','Audi')
				)

		);


		// Build html
		$html = self::settings_output(array(
				'class' => __CLASS__,
				'title' => $class_title,
				'options' => $options
		));
		return $html;
	}

}