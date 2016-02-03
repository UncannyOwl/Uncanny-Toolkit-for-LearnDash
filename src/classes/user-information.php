<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}

class UserInformation extends Config implements RequiredFunctions{

	/**
	 * Class constructor
	 */
	public function __construct() {

		if( true === self::dependants_exist() ){

			add_shortcode( 'first-name',  array( $this, 'first_name' ) );
			add_shortcode( 'last-name',  array( $this, 'last_name' ) );
			add_shortcode( 'display-name',  array( $this, 'display_name' ) );
			add_shortcode( 'user_email',  array( $this, 'user_email' ) );
			add_shortcode( 'user_login',  array( $this, 'user_login' ) );
			add_shortcode( 'user_id',  array( $this, 'user_id' ) );

		}

	}


	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {
		$class_title = __( 'Display User Information', Config::get_text_domain() );
		$class_description = __( '
				<p><strong>[first-name]</strong> <strong>[last-name]</strong><br>
				<strong>[display-name]</strong> <strong>[user-email]</strong><br>
				<strong>[user_login]</strong> <strong>[user_id]</strong>
				</p>
				',
			Config::get_text_domain() );
		$class_icon = '<span class="uo_icon_text">[/ ]</span>';//'<span class="dashicons dashicons-admin-users"></span>';
		return array( 'title' => $class_title, 'description' => $class_description, 'dependants_exist' => self::dependants_exist(), 'icon' => $class_icon );
	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * return boolean || string Return either true or name of function or plugin
	 */
	public static function dependants_exist(){
		return true;
	}

	public static function first_name(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->user_firstname;
	}

	public static function last_name(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->user_lastname;
	}

	public static function display_name(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->display_name;
	}

	public static function user_email(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->user_email;
	}

	public static function user_login(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->user_login;
	}

	public static function user_id(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}

}