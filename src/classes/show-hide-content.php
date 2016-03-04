<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}

class ShowHideContent extends Config implements RequiredFunctions{


	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if( true === self::dependants_exist()){

			//add a shortcode which calls the above function
			add_shortcode('uo-show', array( __CLASS__, 'uo_show' ) );

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {

		$class_title = __( 'Show Or Hide Content', self::get_text_domain() );

		/* Sample Simple Description */
		$class_description = __( 'Sample Description A', self::get_text_domain() );

		/* Sample Simple Description with shortcode */
		$class_description = __( '
				<p>Show or hides content for logged in or logged out users by
				set for="loggedin" or for="loggedout"</p>
				<p>
				<strong>[uo-show for=""]content[/uo-show]</strong>
				</p>
				',
			self::get_text_domain() );

		/* Icon as wp dashicon */
		$class_icon = '<span class="uo_icon_dashicon dashicons dashicons-hidden"></span>';

		return array( 'title' => $class_title, 'description' => $class_description, 'dependants_exist' => self::dependants_exist(), 'icon' => $class_icon );

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist(){
		// Return true if no depency or dependency is available
		return true;
	}

	public static function uo_show ($atts, $content = null){

		//check tha the user is logged in
		if ( $atts['for']=="loggedin" ) {
			if (is_user_logged_in() ) {
				//user is logged in so show the content
				return do_shortcode($content);
			}else{
				return '';
			}
		}

		if ( $atts['for']=="loggedout") {
			if (!is_user_logged_in() ){
				return do_shortcode($content);
			}else{
				return '';
			}
		}

		return do_shortcode($content);

	}

}