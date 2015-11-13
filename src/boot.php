<?php

namespace uncanny_learndash_public;

use uncanny_learndash_public;

class Boot {
	static $instance;
	private static $active_classes;

	/**
	 * Call this method to get singleton
	 * @return Boot $instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * class constructor
	 */
	private function __construct() {

		global $uncanny_learndash_public;

		if ( ! isset( $uncanny_learndash_public ) ) {
			$uncanny_learndash_public = new \stdClass();
		}

		// We need to check if spl auto loading is available when activating plugin
		// Plugin will not activate if SPL extension is not enabled by throwing error
		if( ! extension_loaded("SPL") ) {
			$spl_error = __( "Please contact your hosting company to update to php version 5.3+ and enable spl extensions.", Config::get_text_domain() );
			trigger_error( $spl_error, E_USER_ERROR );
		}

		// Classes(functionality) must be activated in the Plugins options page in order to be loaded
		self::$active_classes = (array) get_option( 'uncanny_public_active_classes', array() );

		spl_autoload_register( array(__CLASS__, 'auto_loader'));

		// Classes that are 'Must Use' are manually added
		self::$active_classes['AdminMenu'] = '1';

		$uncanny_learndash_public->admin_menu = new AdminMenu;

		/* LOAD: LearndashGroupUserProfile*/
		// Class Details:  Add Class to Admin Menu page
		$classes = Config::get_available_classes();
		if ( $classes ) {
			foreach ( Config::get_available_classes() as $class ) {
				if( class_exists( $class ) ){
					new $class;
				}

			}
		}

	}

	/*
	 * @param String $class {namespace}/{class name}
	 */
	public static function auto_loader( $class ) {

		// Remove Class's namespace eg: my_namespace/MyClassName to MyClassName
		$class = str_replace( Config::get_namespace(), '', $class );
		$class = str_replace( '\\', '', $class );

		// First Character of class name to lowercase eg: MyClassName to myClassName
		$class_to_filename = lcfirst( $class );

		// Split class name on upper case letter eg: myClassName to array( 'my', 'Class', 'Name')
		$split_class_to_filename = preg_split( '#([A-Z][^A-Z]*)#', $class_to_filename, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		if ( 1 <= count( $split_class_to_filename ) ) {
			// Split class name to hyphenated name eg: array( 'my', 'Class', 'Name') to my-Class-Name
			$class_to_filename = implode( '-', $split_class_to_filename );
		}
		$file_name = 'abstracts/' . strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include 'abstracts/' . strtolower( $class_to_filename ) . '.php';
			// Manually debug to check if class is loading or not
			//echo '<pre>'; var_dump( 'classes/' . strtolower( $class_to_filename ) . '.php is ' . $class .' '. self::$active_classes ); echo '</pre>';
		}
		// Create file name that will be loaded from the classes directory eg: my-Class-Name to my-class-name.php
		$file_name = 'classes/' . strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include 'classes/' . strtolower( $class_to_filename ) . '.php';
			// Manually debug to check if class is loading or not
			//echo '<pre>'; var_dump( 'classes/' . strtolower( $class_to_filename ) . '.php is ' . $class .' '. self::$active_classes ); echo '</pre>';
		}
		$file_name =  strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ){
			include '' . strtolower( $class_to_filename ) . '.php';
			// Manually debug to check if class is loading or not
			//echo '<pre>'; var_dump( 'classes/' . strtolower( $class_to_filename ) . '.php is ' . $class .' '. self::$active_classes ); echo '</pre>';
		}
	}
}





