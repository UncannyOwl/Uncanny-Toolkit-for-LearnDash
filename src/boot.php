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

		$uncanny_learndash_toolkit->admin_menu        = new AdminMenu;
		$uncanny_learndash_toolkit->install_automator = new InstallAutomator;
		add_action( 'admin_menu', array( __CLASS__, 'uo_support_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'uo_admin_support_css' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'uo_frontend_assets' ) );
		// Add admin menu ajax class to load and save settings
		// parent class is Config
		add_action( 'wp_ajax_settings_save', array( get_parent_class(), 'ajax_settings_save' ) );
		add_action( 'wp_ajax_settings_load', array( get_parent_class(), 'ajax_settings_load' ) );
		add_action( 'admin_init', array( __CLASS__, 'uo_admin_help_process' ) );
		add_action( 'wp_ajax_activate_deactivate_module', array(
			get_parent_class(),
			'ajax_activate_deactivate_module',
		) );

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

		// Import One Click Installer
		require_once( dirname( __FILE__ ) . '/uncanny-one-click-installer/class-auto-plugin-install.php' );

		add_action( 'rest_api_init', [ $this, 'uo_register_api' ] );
		add_action( 'admin_init', [ $this, 'maybe_ask_review' ] );
	}

	/**
	 * uo_support_menu
	 *
	 * @return void
	 */
	public static function uo_support_menu() {
		add_submenu_page(
			'uncanny-toolkit',
			__( 'Uncanny Toolkit for LearnDash Support', 'uncanny-learndash-toolkit' ),
			__( 'Help', 'uncanny-learndash-toolkit' ),
			'manage_options',
			'uncanny-toolkit-kb', array(
			__CLASS__,
			'uo_support_page',
		) );

		add_submenu_page(
			'uncanny-toolkit',
			__( 'Uncanny Toolkit for LearnDash Support', 'uncanny-learndash-toolkit' ),
			__( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ),
			'manage_options',
			'uncanny-toolkit-plugins', array(
			__CLASS__,
			'uo_support_page',
		) );
	}

	/**
	 * uo_support_page
	 *
	 * @return void
	 */
	public static function uo_support_page() {
		include( 'templates/admin-support.php' );
	}

	/**
	 * uo_admin_help_process
	 *
	 * @return void
	 */
	public static function uo_admin_help_process() {
		if ( isset( $_POST['is_uncanny_help'] ) && check_admin_referer( 'uncanny0w1', 'is_uncanny_help' ) ) {
			$name        = sanitize_text_field( $_POST['fullname'] );
			$email       = sanitize_email( $_POST['email'] );
			$website     = esc_url_raw( $_POST['website'] );
			$license_key = sanitize_text_field( $_POST['license_key'] );
			$message     = sanitize_textarea_field( $_POST['message'] );
			$siteinfo    = stripslashes( $_POST['siteinfo'] );
			$message     = '<h3>Message:</h3><br/>' . wpautop( $message );
			if ( isset( $_POST['website'] ) && ! empty( sanitize_text_field( $website ) ) ) {
				$message .= '<hr /><strong>Website:</strong> ' . $website;
			}
			if ( isset( $_POST['license_key'] ) && ! empty( sanitize_text_field( $license_key ) ) ) {
				$message .= '<hr /><strong>License:</strong> <a href="https://www.uncannyowl.com/wp-admin/edit.php?post_type=download&page=edd-licenses&s=' . $license_key . '" target="_blank">' . $license_key . '</a>';
			}
			if ( isset( $_POST['site-data'] ) && 'yes' === sanitize_text_field( $_POST['site-data'] ) ) {
				$message = "$message<hr /><h3>User Site Information:</h3><br />{$siteinfo}";
			}

			$to        = 'support.41077.bb1dda3d33afb598@helpscout.net';
			$subject   = sanitize_text_field( $_POST['subject'] );
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

	/**
	 * uo_frontend_assets
	 *
	 * @return void
	 */
	public static function uo_frontend_assets() {
		wp_enqueue_style( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.css', dirname( __FILE__ ) ), [], UNCANNY_TOOLKIT_VERSION );
		wp_enqueue_script( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.js', dirname( __FILE__ ) ), [ 'jquery' ], UNCANNY_TOOLKIT_VERSION );
		wp_localize_script( 'uncannyowl-learndash-toolkit-free', 'UncannyToolkit', apply_filters( 'uncannyowl-learndash-toolkit-js', [
			'ajax'   => [
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'uncannyowl-learndash-toolkit' ),
			],
			'integrity' => array(
				'shouldPreventConcurrentLogin' => self::ld_is_preventing_concurrent_login()
			),
			'i18n'   => [
				'dismiss' => __( 'Dismiss', 'uncanny-learndash-toolkit' ),
				'preventConcurrentLogin' => __( 'Your account has exceeded maximum concurrent login number.', 'learndash-integrity' ),
				'error'   => [
					'generic' => __( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' ),
				],
			],
			'modals' => [],
		] ) );
	}

	private static function ld_is_preventing_concurrent_login() {
		// Get option
		$option = get_option( 'learndash_settings_ld_integrity' );

		// Check if it exists and the value if "yes"
		// Condition from /learndash-integrity/includes/class-prevent-concurrent-login.php:30
		return isset( $option[ 'prevent_concurrent_login' ] ) && 'yes' == $option[ 'prevent_concurrent_login' ];
	}

	/**
	 * uo_admin_support_css
	 *
	 * @return void
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

	/**
	 * Register rest api calls for misc tasks.
	 *
	 * @since 3.3
	 */
	public function uo_register_api() {
		register_rest_route( UNCANNY_TOOLKIT_REST_API_END_POINT, '/review-banner-visibility/', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'save_review_settings' ],
			'permission_callback' => '__return_true',
		] );
	}


	/**
	 * Admin notice for review this plugin.
	 *
	 * @since 3.3
	 */
	public function maybe_ask_review() {

		// check plugin install date
		$review_time = get_option( '_uncanny_toolkit_review_time', '' );

		if ( empty( $review_time ) ) {
			$review_time = current_time( 'timestamp' );
			update_option( '_uncanny_toolkit_review_time', $review_time );
		}

		$current_date = current_time( 'timestamp' );
		$days_after   = 10;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ceil( ( $current_date - $review_time ) / 86400 ) > $days_after ) {

			$_is_reminder   = get_option( '_uncanny_toolkit_review_reminder', '' );
			$_reminder_date = get_option( '_uncanny_toolkit_review_reminder_date', current_time( 'timestamp' ) );

			if ( ! empty( $_is_reminder ) && 'hide-forever' === $_is_reminder ) {
				return;
			}

			if ( ! empty( $_is_reminder ) && 'maybe-later' === $_is_reminder ) {
				// check reminder date difference
				if ( ceil( ( $current_date - $_reminder_date ) / 86400 ) < $days_after ) {
					return;
				}
			}

			add_action( 'admin_notices', function () {
				// Get data about Toolkit version
				$is_pro  = defined( 'UNCANNY_TOOLKIT_PRO_VERSION' );
				$version = $is_pro ? UNCANNY_TOOLKIT_PRO_VERSION : UNCANNY_TOOLKIT_VERSION;

				// Send review URL
				$url_send_review = 'https://wordpress.org/support/plugin/uncanny-learndash-toolkit/reviews/#new-post';

				// Send feedback URL
				if ( $is_pro ) {
					$url_send_feedback_plugin = 'Uncanny%20LearnDash%20Toolkit%20Pro';
					$url_send_feedback_source = 'uncanny_learndash_toolkit_pro';
					$url_send_feedback        = 'https://www.uncannyowl.com/request-plugin-feature/?plugin=' . $url_send_feedback_plugin . '&utm_source=' . $url_send_feedback_source . '&utm_medium=review_banner';
				} else {
					$url_send_feedback = 'https://wordpress.org/support/plugin/uncanny-learndash-toolkit/#new-topic-0';
				}

				include Config::get_template( 'admin-review-banner.php' );

			} );
		}
	}

	/**
	 * Rest API callback for saving user selection for review.
	 *
	 * @param object $request
	 *
	 * @return object
	 * @since 2.1.4
	 */
	public function save_review_settings( $request ) {
		// check if its a valid request.
		$data = $request->get_params();
		if ( isset( $data['action'] ) && ( 'maybe-later' === $data['action'] || 'hide-forever' === $data['action'] ) ) {
			update_option( '_uncanny_toolkit_review_reminder', $data['action'] );
			update_option( '_uncanny_toolkit_review_reminder_date', current_time( 'timestamp' ) );

			return new \WP_REST_Response( [ 'success' => true ], 200 );
		}

		return new \WP_REST_Response( [ 'success' => false ], 200 );
	}
}
