<?php

namespace uncanny_learndash_toolkit;

/**
 * Class Boot
 * @package uncanny_learndash_toolkit
 */
class Boot extends Config {

	/**
	 * class constructor
	 */
	public function __construct() {

		global $uncanny_learndash_toolkit;

		if ( ! isset( $uncanny_learndash_toolkit ) ) {
			$uncanny_learndash_toolkit = new \stdClass();
		}

		// We need to check if spl auto loading is available when activating plugin
		// Plugin will not activate if SPL extension is not enabled by throwing error
		if ( ! extension_loaded( 'SPL' ) ) {
			$spl_error = esc_html__( 'Please contact your hosting company to update to php version 5.3+ and enable spl extensions.', 'uncanny-learndash-toolkit' );
			trigger_error( $spl_error, E_USER_ERROR );
		}

		spl_autoload_register( array( __CLASS__, 'auto_loader' ) );

		$uncanny_learndash_toolkit->admin_menu = new AdminMenu;
		add_action( 'admin_menu', array( __CLASS__, 'uo_support_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'uo_admin_support_css' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'uo_frontend_assets' ) );
		// Add admin menu ajax class to load and save settings
		add_action( 'wp_ajax_settings_save', array(
			get_parent_class(),
			'ajax_settings_save',
		) );// parent class is Config
		add_action( 'wp_ajax_settings_load', array(
			get_parent_class(),
			'ajax_settings_load',
		) );// parent class is Config
		add_action( 'admin_init', array( __CLASS__, 'uo_admin_help_process' ) );

		add_action( 'wp_ajax_activate_deactivate_module', array(
			get_parent_class(),
			'ajax_activate_deactivate_module',
		) );// parent class is Config

		// Class Details:  Add Class to Admin Menu page
		$classes = self::get_active_classes();

		// Import Gutenberg Blocks
		require_once( dirname( __FILE__ ) . '/blocks/blocks.php' );
		new Blocks( UNCANNY_TOOLKIT_PREFIX, UNCANNY_TOOLKIT_VERSION, $classes );

		if ( $classes ) {

			foreach ( self::get_active_classes() as $class ) {

				// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values
				if ( strpos( $class, '\\' ) === false ) {
					$class = str_replace( 'toolkit', 'toolkit\\', $class );
				}

				$class_namespace = explode( '\\', $class );

				if ( class_exists( $class ) && __NAMESPACE__ === $class_namespace[0] ) {
					new $class;
				}
			}
		}
	}

	/**
	 *
	 */
	public static function uo_support_menu() {
		add_submenu_page(
			'uncanny-toolkit',
			__( 'Uncanny LearnDash Toolkit Support', 'uncanny-learndash-toolkit' ),
			__( 'Help', 'uncanny-learndash-toolkit' ),
			'manage_options',
			'uncanny-toolkit-kb', array(
			__CLASS__,
			'uo_support_page',
		) );

		add_submenu_page(
			'uncanny-toolkit',
			__( 'Uncanny LearnDash Toolkit Support', 'uncanny-learndash-toolkit' ),
			__( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ),
			'manage_options',
			'uncanny-toolkit-plugins', array(
			__CLASS__,
			'uo_support_page',
		) );
	}

	/**
	 *
	 */
	public static function uo_support_page() {
		include( 'templates/admin-support.php' );
	}

	public static function uo_admin_help_process() {
		if ( isset( $_POST['is_uncanny_help'] ) && check_admin_referer( 'uncanny0w1', 'is_uncanny_help' ) ) {
			$name     = esc_html( $_POST['fullname'] );
			$email    = esc_html( $_POST['email'] );
			$website  = esc_html( $_POST['website'] );
			$message  = esc_html( $_POST['message'] );
			$siteinfo = stripslashes( $_POST['siteinfo'] );
			if ( isset( $_POST['site-data'] ) && 'yes' === $_POST['site-data'] ) {
				$message = "<h3>Message:</h3><p>{$message}</p><br /><hr /><h3>User Site Information:</h3>{$siteinfo}";
			}

			$to        = 'support.41077.bb1dda3d33afb598@helpscout.net';
			$subject   = esc_html( $_POST['subject'] );
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$headers[] = 'From: ' . $name . ' <' . $email . '>';
			$headers[] = 'Reply-To:' . $name . ' <' . $email . '>';
			wp_mail( $to, $subject, $message, $headers );
			if ( isset( $_POST['page'] ) && isset( $_POST['tab'] ) ) {
				$url = admin_url( 'admin.php' ) . '?page=' . esc_html( $_POST['page'] ) . '&tab=' . esc_html( $_POST['tab'] ) . '&sent=1&wpnonce=' . wp_create_nonce();
				wp_safe_redirect( $url );
				exit;
			}
		}
	}

	public static function uo_frontend_assets() {
		wp_enqueue_style( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.css', dirname( __FILE__ ) ), [], UNCANNY_TOOLKIT_VERSION );

		wp_enqueue_script( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.js', dirname( __FILE__ ) ), [], UNCANNY_TOOLKIT_VERSION );
	}

	/**
	 *
	 */
	public static function uo_admin_support_css() {
		$pages_to_include = [ 'uncanny-toolkit-plugins', 'uncanny-toolkit-kb' ];
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages_to_include ) ) {
			wp_enqueue_style( 'uncannyowl-core', 'https://uncannyowl.com/wp-content/mu-plugins/uncanny-plugins-core/dist/bundle.min.css', array(), UNCANNY_TOOLKIT_VERSION );

			wp_enqueue_script( 'uncannyowl-core', 'https://uncannyowl.com/wp-content/mu-plugins/uncanny-plugins-core/dist/bundle.min.js', array( 'jquery' ), UNCANNY_TOOLKIT_VERSION );
		}
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $class
	 */
	public static function auto_loader( $class ) {

		// Remove Class's namespace eg: my_namespace/MyClassName to MyClassName
		$class = str_replace( self::get_namespace(), '', $class );
		$class = str_replace( '\\', '', $class );

		// First Character of class name to lowercase eg: MyClassName to myClassName
		$class_to_filename = lcfirst( $class );

		// Split class name on upper case letter eg: myClassName to array( 'my', 'Class', 'Name')
		$split_class_to_filename = preg_split( '#([A-Z][^A-Z]*)#', $class_to_filename, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		if ( 1 <= count( $split_class_to_filename ) ) {
			// Split class name to hyphenated name eg: array( 'my', 'Class', 'Name') to my-Class-Name
			$class_to_filename = implode( '-', $split_class_to_filename );
		}
		$file_name = 'interfaces/' . strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include_once $file_name;
		}
		// Create file name that will be loaded from the classes directory eg: my-Class-Name to my-class-name.php
		$file_name = 'classes/' . strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include_once $file_name;
		}
		$file_name = strtolower( $class_to_filename ) . '.php';
		if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
			include_once $file_name;
		}

	}
}





