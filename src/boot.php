<?php

namespace uncanny_learndash_toolkit;

/**
 * Class Boot
 *
 * @package uncanny_learndash_toolkit
 */
class Boot extends Config {

	static $upgrade_pro_link = 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/?utm_source=uncanny_toolkit&utm_medium=plugins_page&utm_content=update_to_pro';

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

		$uncanny_learndash_toolkit->admin_menu        = new AdminMenu();
		$uncanny_learndash_toolkit->install_automator = new InstallAutomator();
		add_action( 'admin_menu', array( __CLASS__, 'uo_support_menu' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'uo_frontend_assets' ) );
		// Add admin menu ajax class to load and save settings
		// parent class is Config
		add_action( 'wp_ajax_settings_save', array( get_parent_class(), 'ajax_settings_save' ) );
		add_action( 'wp_ajax_settings_load', array( get_parent_class(), 'ajax_settings_load' ) );
		add_action( 'admin_init', array( __CLASS__, 'uo_admin_help_process' ) );
		add_action(
			'wp_ajax_activate_deactivate_module',
			array(
				get_parent_class(),
				'ajax_activate_deactivate_module',
			)
		);

		add_action( 'admin_menu', array( $this, 'pro_upsell_menu' ) );
		add_action( 'admin_head', array( $this, 'adjust_pro_menu_item' ) );
		add_action( 'admin_head', array( $this, 'admin_menu_styles' ) );

		// Class Details:  Add Class to Admin Menu page
		$classes = self::get_active_classes();

		// Import Gutenberg Blocks
		require_once dirname( __FILE__ ) . '/blocks/blocks.php';
		new Blocks( UNCANNY_TOOLKIT_PREFIX, UNCANNY_TOOLKIT_VERSION, $classes );

		if ( $classes ) {

			foreach ( self::get_active_classes() as $class ) {

				// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values
				if ( strpos( $class, '\\' ) === false ) {
					$class = str_replace( 'toolkit', 'toolkit\\', $class );
				}

				$class_namespace = explode( '\\', $class );

				if ( class_exists( $class ) && __NAMESPACE__ === $class_namespace[0] ) {
					new $class();
				}
			}
		}

		// Import One Click Installer
		require_once dirname( __FILE__ ) . '/uncanny-one-click-installer/class-auto-plugin-install.php';

		//add_action( 'rest_api_init', array( $this, 'uo_register_api' ) );
		add_action( 'admin_init', array( $this, 'maybe_ask_review' ) );
		add_action( 'admin_init', array( $this, 'maybe_save_review_without_rest' ) );
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
			'uncanny-toolkit-kb',
			array(
				__CLASS__,
				'uo_support_page',
			)
		);

		add_submenu_page(
			'uncanny-toolkit',
			__( 'Uncanny Toolkit for LearnDash Support', 'uncanny-learndash-toolkit' ),
			__( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ),
			'manage_options',
			'uncanny-toolkit-plugins',
			array(
				__CLASS__,
				'uo_support_page',
			)
		);
	}

	/**
	 * @return void
	 */
	public function pro_upsell_menu() {
		if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
			return;
		}

		global $submenu;
		$url = 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/?utm_source=uncanny_toolkit&utm_medium=plugins_page&utm_content=update_to_pro';

		$submenu[ 'uncanny-toolkit' ][ 'uncanny-toolkit-pro-upgrade' ] = array(
			esc_attr__( 'Upgrade to Pro', 'uncanny-learndash-toolkit' ), 
			'manage_options',
			$url,
			'uncanny-toolkit-pro-upgrade'
		);
	}

	/**
	 * Make changes to the PRO menu item.
	 */
	public function adjust_pro_menu_item() {

		global $submenu;

		// Bail if plugin menu is not registered.
		if ( ! isset( $submenu[ 'uncanny-toolkit' ] ) ) {
			return;
		}

		$upgrade_link_position = key(
			array_filter(
				$submenu[ 'uncanny-toolkit' ],
				function( $item ) {
					return strpos( $item[3], 'uncanny-toolkit-pro-upgrade' ) !== false;
				}
			)
		);

		// Bail if "Upgrade to Pro" menu item is not registered.
		if ( $upgrade_link_position === null ) {
			return;
		}

		// Add the PRO badge to the menu item.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $submenu[ 'uncanny-toolkit' ][ $upgrade_link_position ][4] ) ) {
			$submenu[ 'uncanny-toolkit' ][ $upgrade_link_position ][4] .= ' ult-sidebar-upgrade-pro';
		} else {
			$submenu[ 'uncanny-toolkit' ][ $upgrade_link_position ][] = 'ult-sidebar-upgrade-pro';
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Output inline styles for the admin menu.
	 */
	public function admin_menu_styles() {
		$styles = 'a.ult-sidebar-upgrade-pro { background-color: #00a32a !important; color: #fff !important; font-weight: 600 !important; }';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<style>%s</style>', $styles );
	}

	/**
	 * uo_support_page
	 *
	 * @return void
	 */
	public static function uo_support_page() {
		include 'templates/admin-support.php';
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
		wp_enqueue_style( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.css', dirname( __FILE__ ) ), array(), UNCANNY_TOOLKIT_VERSION );
		wp_enqueue_script( 'uncannyowl-learndash-toolkit-free', plugins_url( 'src/assets/frontend/dist/bundle.min.js', dirname( __FILE__ ) ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION );
		wp_localize_script(
			'uncannyowl-learndash-toolkit-free',
			'UncannyToolkit',
			apply_filters(
				'uncannyowl-learndash-toolkit-js',
				array(
					'ajax'      => array(
						'url'   => admin_url( 'admin-ajax.php' ),
						'nonce' => wp_create_nonce( 'uncannyowl-learndash-toolkit' ),
					),
					'integrity' => array(
						'shouldPreventConcurrentLogin' => self::ld_is_preventing_concurrent_login(),
					),
					'i18n'      => array(
						'dismiss'                => __( 'Dismiss', 'uncanny-learndash-toolkit' ),
						'preventConcurrentLogin' => __( 'Your account has exceeded maximum concurrent login number.', 'learndash-integrity' ),
						'error'                  => array(
							'generic' => __( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' ),
						),
					),
					'modals'    => array(),
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	private static function ld_is_preventing_concurrent_login() {
		// Get option
		$option = get_option( 'learndash_settings_ld_integrity' );

		// Check if it exists and the value if "yes"
		// Condition from /learndash-integrity/includes/class-prevent-concurrent-login.php:30
		return isset( $option['prevent_concurrent_login'] ) && 'yes' == $option['prevent_concurrent_login'];
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
		$split_class_to_filename = preg_split( '#([A-Z][^A-Z]*)#', $class_to_filename, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

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
	 * @deprecated
	 */
	public function uo_register_api() {
		register_rest_route(
			UNCANNY_TOOLKIT_REST_API_END_POINT,
			'/review-banner-visibility/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'save_review_settings' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
			)
		);
	}

	/**
	 * Check current user permission.
	 *
	 * @since 3.6
	 * @deprecated
	 */
	public function check_user_permission() {
		return current_user_can( 'manage_options' );
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

			add_action(
				'admin_notices',
				function () {
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

					// Send to WordPress.org to review.
					$url_send_review = $this->get_review_rest_url( $url_send_review );

					// Send to WordPress.org support forum.
					$url_send_feedback = $this->get_review_rest_url( $url_send_feedback );

					// Reloads the page via referer with maybe-later.
					$url_maybe_later = $this->get_review_rest_url( '', 'maybe-later' );

					// Reloads the page via referer with hide-forever
					$url_hide_forever = $this->get_review_rest_url( '', 'hide-forever' );

					include Config::get_template( 'admin-review-banner.php' );

				}
			);
		}
	}

	/**
	 * Method get_review_rest_url.
	 *
	 * @param string $redirect_url The redirect url.
	 * @param string $action The action.
	 * @param string $redirect The redirect url.
	 *
	 * @return string The url of the toolkit review-banner-visibility endpoint
	 */
	public function get_review_rest_url( $redirect_url = '', $action = 'hide-forever', $redirect = 'yes' ) {

		return add_query_arg(
			array(
				'action'       => $action,
				'redirect'     => $redirect,
				'redirect_url' => rawurlencode( $redirect_url ),
			),
			admin_url( 'admin.php' )
		);

	}

	/**
	 * Rest API callback for saving user selection for review.
	 *
	 * @param object $request
	 *
	 * @return object
	 * @since 2.1.4
	 * @depecated
	 */
	public function save_review_settings( $request ) {

		// Check if its a valid request.
		$action = $request->get_param( 'action' );

		$redirect = $request->get_param( 'redirect' );

		$redirect_url = $request->get_param( 'redirect_url' );

		$visiblity_actions = array( 'maybe-later', 'hide-forever' );

		if ( in_array( $action, $visiblity_actions, true ) ) {

			update_option( '_uncanny_toolkit_review_reminder', $action );

			update_option( '_uncanny_toolkit_review_reminder_date', current_time( 'timestamp' ) );

			if ( 'yes' === $redirect ) {

				// Return the refering url if its empty.
				if ( empty( $redirect_url ) ) {

					$redirect_url = wp_get_referer();

				}

				wp_safe_redirect( esc_url( $redirect_url ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect

				exit;

			}

			return new \WP_REST_Response(
				array(
					'success' => true,
					'test'    => 1,
				),
				200
			);

		}

		return new \WP_REST_Response( array( 'success' => false ), 200 );

	}

	/**
	 * @return void
	 */
	public function maybe_save_review_without_rest() {
		if ( ! filter_has_var( INPUT_GET, 'action' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$action            = filter_input( INPUT_GET, 'action' );
		$visiblity_actions = array( 'maybe-later', 'hide-forever' );
		if ( ! in_array( $action, $visiblity_actions, true ) ) {
			return;
		}

		$redirect = filter_input( INPUT_GET, 'redirect' );

		$redirect_url = filter_input( INPUT_GET, 'redirect_url' );


		update_option( '_uncanny_toolkit_review_reminder', $action );

		update_option( '_uncanny_toolkit_review_reminder_date', current_time( 'timestamp' ) );

		if ( 'yes' === $redirect ) {

			// Return the refering url if its empty.
			if ( empty( $redirect_url ) ) {

				$redirect_url = wp_get_referer();

			}

			wp_safe_redirect( esc_url( $redirect_url ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect

			exit;

		}
		//default
		wp_safe_redirect( admin_url( 'admin.php?page=uncanny-toolkit' ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect

		exit;
	}
}
