<?php

namespace uncanny_learndash_toolkit;

use ReflectionClass;

/**
 * Class AdminMenu
 *
 * @package uncanny_learndash_toolkit
 */
class AdminMenu extends Boot {

	/**
	 * @var bool
	 */
	public static $is_pro_active = false;
	/**
	 * @var array
	 */
	public static $modules = [];

	/**
	 * class constructor
	 */
	public function __construct() {
		// Setup Theme Options Page Menu in Admin
		if ( is_admin() ) {
			add_action( 'admin_menu', array(
				__CLASS__, 'register_options_menu_page',
			) );
			add_action( 'admin_init', array(
				__CLASS__, 'register_options_menu_page_settings',
			) );
			add_action( 'admin_enqueue_scripts', array(
				__CLASS__, 'scripts',
			) );

			// Filter notifications
			add_action( 'admin_head', array( 
				__CLASS__, 'maybe_filter_non_toolkit_admin_notices' 
			), PHP_INT_MAX );
			add_action( 'admin_print_scripts', array( 
				__CLASS__, 'maybe_filter_non_toolkit_admin_notices' 
			), PHP_INT_MAX );
		}

	}

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = esc_html__( 'Uncanny Toolkit for LearnDash', 'uncanny-learndash-toolkit' );
		$menu_title = esc_html__( 'Uncanny Toolkit', 'uncanny-learndash-toolkit' );
		$capability = 'manage_options';
		$menu_slug  = 'uncanny-toolkit';
		$function   = array( __CLASS__, 'options_menu_page_output' );

		// Menu Icon blends into sidebar when the default admin color scheme is used
		//$admin_color_scheme = get_user_meta( get_current_user_id(), 'admin_color', true );
		$icon_url = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDU4MSA2NDAiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDU4MSA2NDAiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTUyNi40IDM0LjFjMC42IDUgMSAxMC4xIDEuMyAxNS4xIDAuNSAxMC4zIDEuMiAyMC42IDAuOCAzMC45LTAuNSAxMS41LTEgMjMtMi4xIDM0LjQtMi42IDI2LjctNy44IDUzLjMtMTYuNSA3OC43LTcuMyAyMS4zLTE3LjEgNDEuOC0yOS45IDYwLjQtMTIgMTcuNS0yNi44IDMzLTQzLjggNDUuOS0xNy4yIDEzLTM2LjcgMjMtNTcuMSAyOS45LTI1LjEgOC41LTUxLjUgMTIuNy03Ny45IDEzLjggNzAuMyAyNS4zIDEwNi45IDEwMi44IDgxLjYgMTczLjEtMTguOSA1Mi42LTY4LjEgODguMS0xMjQgODkuNWgtNi4xYy0xMS4xLTAuMi0yMi4xLTEuOC0zMi45LTQuNy0yOS40LTcuOS01NS45LTI2LjMtNzMuNy01MC45LTI5LjItNDAuMi0zNC4xLTkzLjEtMTIuNi0xMzgtMjUgMjUuMS00NC41IDU1LjMtNTkuMSA4Ny40LTguOCAxOS43LTE2LjEgNDAuMS0yMC44IDYxLjEtMS4yLTE0LjMtMS4yLTI4LjYtMC42LTQyLjkgMS4zLTI2LjYgNS4xLTUzLjIgMTIuMi03OC45IDUuOC0yMS4yIDEzLjktNDEuOCAyNC43LTYwLjlzMjQuNC0zNi42IDQwLjYtNTEuM2MxNy4zLTE1LjcgMzcuMy0yOC4xIDU5LjEtMzYuOCAyNC41LTkuOSA1MC42LTE1LjIgNzYuOC0xNy4yIDEzLjMtMS4xIDI2LjctMC44IDQwLjEtMi4zIDI0LjUtMi40IDQ4LjgtOC40IDcxLjMtMTguMyAyMS05LjIgNDAuNC0yMS44IDU3LjUtMzcuMiAxNi41LTE0LjkgMzAuOC0zMi4xIDQyLjgtNTAuOCAxMy0yMC4yIDIzLjQtNDIuMSAzMS42LTY0LjcgNy42LTIxLjEgMTMuNC00Mi45IDE2LjctNjUuM3ptLTI3OS40IDMyOS41Yy0xOC42IDEuOC0zNi4yIDguOC01MC45IDIwLjQtMTcuMSAxMy40LTI5LjggMzIuMi0zNi4yIDUyLjktNy40IDIzLjktNi44IDQ5LjUgMS43IDczIDcuMSAxOS42IDE5LjkgMzcuMiAzNi44IDQ5LjYgMTQuMSAxMC41IDMwLjkgMTYuOSA0OC40IDE4LjZzMzUuMi0xLjYgNTEtOS40YzEzLjUtNi43IDI1LjQtMTYuMyAzNC44LTI4LjEgMTAuNi0xMy40IDE3LjktMjkgMjEuNS00NS43IDQuOC0yMi40IDIuOC00NS43LTUuOC02Ni45LTguMS0yMC0yMi4yLTM3LjYtNDAuMy00OS4zLTE4LTExLjctMzkuNS0xNy02MS0xNS4xeiIgZmlsbD0iIzgyODc4QyIvPjxwYXRoIGQ9Im0yNDIuNiA0MDIuNmM2LjItMS4zIDEyLjYtMS44IDE4LjktMS41LTExLjQgMTEuNC0xMi4yIDI5LjctMS44IDQyIDExLjIgMTMuMyAzMS4xIDE1LjEgNDQuNCAzLjkgNS4zLTQuNCA4LjktMTAuNCAxMC41LTE3LjEgMTIuNCAxNi44IDE2LjYgMzkuNCAxMSA1OS41LTUgMTguNS0xOCAzNC42LTM1IDQzLjUtMzQuNSAxOC4yLTc3LjMgNS4xLTk1LjUtMjkuNS0xLTItMi00LTIuOS02LjEtOC4xLTE5LjYtNi41LTQzIDQuMi02MS4zIDEwLTE3IDI2LjgtMjkuMiA0Ni4yLTMzLjR6IiBmaWxsPSIjODI4NzhDIi8+PC9zdmc+';

		$position = 81; // 81 - Above Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, null, $icon_url, $position );

		add_submenu_page( $menu_slug, __( 'Modules', 'uncanny-learndash-toolkit' ), __( 'Modules', 'uncanny-learndash-toolkit' ), 'manage_options', $menu_slug, $function );


	}

	/*
	* Whitelisted Options that are saved on the page
	*/
	/**
	 *
	 */
	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_toolkit-group', 'uncanny_toolkit_active_classes' );
	}

	/**
	 * @param $hook
	 */
	public static function scripts( $hook ) {
		// Load global admin assets
		wp_enqueue_style( 'ult-admin-global', Config::get_admin_css( 'global.css' ), array(), UNCANNY_TOOLKIT_VERSION );
		wp_enqueue_script( 'ult-admin-global', Config::get_admin_js( 'global.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION );

		wp_localize_script( 'ult-admin-global', 'UncannyToolkitGlobal', [
			'rest' => [
				'url'   => esc_url_raw( rest_url() . UNCANNY_TOOLKIT_REST_API_END_POINT ),
				'nonce' => \wp_create_nonce( 'wp_rest' ),
			],
			'ajax' => array(
				'nonce' => wp_create_nonce( 'uncanny_toolkit' )
			)
		] );

		// Target Toolkit pages
		if ( strpos( $hook, 'uncanny-toolkit' ) || strpos( $hook, 'uncanny-toolkit-kb' ) || strpos( $hook, 'uncanny-toolkit-plugins' ) || strpos( $hook, 'uncanny-toolkit-license' ) ) {
			// Main CSS file
			wp_enqueue_style( 'ult-admin', Config::get_admin_css( 'style.css' ), array(), UNCANNY_TOOLKIT_VERSION );

			// Main JS
			wp_enqueue_script( 'ult-admin-functions', Config::get_admin_js( 'functions.js' ), array(
				'jquery',
				'ult-fuse',
				'ult-select2',
			), UNCANNY_TOOLKIT_VERSION, true );

			// Libraries
			wp_enqueue_style( 'ult-select2', Config::get_vendor( 'select2/css/select2.min.css' ), array(), UNCANNY_TOOLKIT_VERSION );
			wp_enqueue_script( 'ult-fuse', Config::get_vendor( 'fuse/js/fuse.min.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
			wp_enqueue_script( 'ult-select2', Config::get_vendor( 'select2/js/select2.min.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

	}

	/**
	 *
	 */
	public static function options_menu_page_output() {

		// Scan plugins directory for custom plugin
		$uo_custom_classes['path']      = self::check_for_other_uo_plugin_classes( 'custom' );
		$uo_custom_classes['namespace'] = 'uncanny_custom_toolkit';

		// Scan plugins directory for pro plugin
		$uo_pro_classes['path']      = self::check_for_other_uo_plugin_classes( 'pro' );
		$uo_pro_classes['namespace'] = 'uncanny_pro_toolkit';

		if ( ! $uo_pro_classes['path'] ) {
			self::get_psuedo_pro_modules();
		} else {
			self::$is_pro_active = true;
		}

		// Get Available Classes from UO-Public
		$classes_available = self::get_available_classes(
			array(
				$uo_custom_classes,
				$uo_pro_classes,
			)
		);

		// Get an array of options from the database
		$active_classes = Config::get_active_classes();

		self::create_modules( $classes_available, $active_classes );

		?>

		<div class="wrap">
			<div class="uo-plugins-header">
				<div class="uo-plugins-header__title">
					Uncanny Toolkit for LearnDash
				</div>
				<div class="uo-plugins-header__author">
					<span><?php _e( 'by', 'uncanny-learndash-toolkit' ); ?></span>
					<a href="<?php echo Config::utm_parameters( 'https://uncannyowl.com', 'header', 'logo' ); ?>"
					   target="_blank" class="uo-plugins-header__logo">
						<img
							src="<?php echo esc_url( Config::get_admin_media( 'uncanny-owl-logo.svg' ) ); ?>"
							alt="Uncanny Owl">
					</a>
				</div>
				<?php do_action('uncanny_toolkit_dashboard_header_after'); ?>
			</div>

			<div class="uo-plugins-tabs">
				<?php include( Config::get_template( 'admin-tabs.php' ) ) ?>
			</div>

			<?php include( Config::get_template( 'admin-modules.php' ) ) ?>
		</div>

		<?php self::create_features( $classes_available, $active_classes );

	}

	/*
	 * Load Scripts
	 */
	/**
	 * @param $uo_plugin
	 *
	 * @return bool|string
	 */
	private static function check_for_other_uo_plugin_classes( $uo_plugin ) {

		// plugins dir
		$directory_contents = scandir( WP_PLUGIN_DIR );

		// loop through all contents
		foreach ( $directory_contents as $content ) :

			// exclude parent directories
			if ( '.' !== $content or '..' !== $content ) :

				// create absolute path
				$plugin_dir = WP_PLUGIN_DIR . '/' . $content;

				if ( is_dir( $plugin_dir ) ) {

					if ( 'pro' === $uo_plugin ) {
						if ( 'uo-plugin-pro' === $content || 'uncanny-toolkit-pro' === $content ) {
							// Check if plugin is active
							if ( is_plugin_active( $content . '/uncanny-toolkit-pro.php' ) ) {
								return $plugin_dir . '/src/classes/';
							}
						}
					}

					if ( 'custom' === $uo_plugin ) {

						$explode_directory = explode( '-', $content );
						if ( 3 === count( $explode_directory ) ) {
							// custom plugin directory is may be prefixed with client name
							// check suffix uo-custom-plugin
							if ( in_array( 'uo', $explode_directory, true ) && in_array( 'custom', $explode_directory, true ) && in_array( 'plugin', $explode_directory, true ) ) {
								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-toolkit-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}
							}

							if ( 'uncanny-toolkit-custom' === $content ) {
								// Check if plugin is active
								if ( is_plugin_active( $content . '/uncanny-toolkit-custom.php' ) ) {
									return $plugin_dir . '/src/classes/';
								}
							}
						}
					}
				}
			endif;
		endforeach;

		return false;
	}

	/**
	 * Populates an array of classes in internal and external file in the
	 * classes folder
	 *
	 * @param array $external_classes
	 *
	 * @return array
	 */
	public static function get_available_classes( $external_classes = [] ) {

		$class_details = array();

		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';

		$files = scandir( $path );

		$internal_details = self::get_class_details( $path, $files, __NAMESPACE__ );

		$class_details = array_merge( $class_details, $internal_details );

		if ( ! empty( $external_classes ) ) {
			foreach ( $external_classes as $external_class ) {
				// Make sure that the external path being loaded exists
				if ( false !== $external_class['path'] ) {
					$external_files   = scandir( $external_class['path'] );
					$external_details = self::get_class_details( $external_class['path'], $external_files, $external_class['namespace'] );
					$class_details    = array_merge( $class_details, $external_details );
				}
			}
		}

		return $class_details;
	}

	/*
	 * Add add-ons to options page
	 *
	 */

	/**
	 * @param $path
	 * @param $files
	 * @param $name_space
	 *
	 * @return array
	 */
	private static function get_class_details( $path, $files, $name_space ) {

		$details = array();

		foreach ( $files as $file ) {
			if ( is_dir( $path . $file ) || '..' === $file || '.' === $file ) {
				continue;
			}

			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = $name_space . '\\' . str_replace( ' ', '', $class_name );

			if ( ! class_exists( $class_name ) ) {
				//Possible blank screen error debug
				//var_dump($class_name);
				continue;
			}

			$class_name = get_class( new $class_name() );
			// test for required functions
			$class = new ReflectionClass( $class_name );

			if ( $class->implementsInterface( 'uncanny_learndash_toolkit\RequiredFunctions' ) ) {
				$details[ $class_name ] = $class_name::get_details();
			} else {
				$details[ $class_name ] = false;
			}
		}

		return $details;

	}

	/**
	 * @param $classes_available
	 * @param $active_classes
	 */
	public static function create_features( $classes_available, $active_classes ) {

		$active_classes = Config::stripslashes_deep( $active_classes );
		$modal_html     = '';
		foreach ( $classes_available as $key => $class ) {
			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				$class['settings'] = array();
			}
			if ( empty( $class['settings'] ) ) {
				$class['settings']['modal'] = '';
				$class['settings']['link']  = '';
			}
			// Setting Modal Popup
			$modal_html .= $class['settings']['modal'];
		}

		$add_on_titles = array();
		foreach ( $classes_available as $key => $row ) {

			$add_on_titles[ $key ] = $row['title'];
		}
		array_multisort( $add_on_titles, SORT_ASC, $classes_available );

		foreach ( $classes_available as $key => $class ) {

			if ( 'uncanny_learndash_toolkit\Sample' === $key || 'uncanny_custom_toolkit\Sample' === $key || 'uncanny_pro_toolkit\Sample' === $key ) {
				continue;
			}

			$class_name                           = $key;
			self::$modules[ $key ]['class_name']  = $class_name;
			self::$modules[ $key ]['settings_id'] = str_replace( __NAMESPACE__, '', stripslashes( $key ) );

			if ( false === $class ) {
				continue;
			}

			$dependants_exist = $class['dependants_exist'];

			self::$modules[ $key ]['is_active'] = false;
			if ( isset( $active_classes[ $class_name ] ) || isset( $active_classes[ stripslashes( $class_name ) ] ) ) {
				self::$modules[ $key ]['is_active'] = true;
			}

			self::$modules[ $key ]['is_active']       = true;
			self::$modules[ $key ]['cant_use_notice'] = '';


			if ( true !== $dependants_exist ) {
				self::$modules[ $key ]['can_use']         = false;
				self::$modules[ $key ]['cant_use_notice'] = $dependants_exist;
			}

			self::$modules[ $key ]['is_pro'] = false;
			if ( key_exists( 'type', $class ) && ! empty( $class['type'] ) ) {
				if ( 'pro' === $class['type'] ) {
					self::$modules[ $key ]['is_pro'] = true;
				}
			}
		}

		echo $modal_html;
	}

	/**
	 * @param $classes_available
	 * @param $active_classes
	 */
	public static function create_modules( $classes_available, $active_classes ) {

		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		//if ( function_exists( 'get_magic_quotes_gpc' ) ) {
		//if ( get_magic_quotes_gpc() ) {
		//strip slashes from all keys in array
		$active_classes = Config::stripslashes_deep( $active_classes );
		//}
		//}

		$add_on_titles = array();
		foreach ( $classes_available as $key => $row ) {

			$add_on_titles[ $key ] = $row['title'];
		}

		array_multisort( $add_on_titles, SORT_ASC, $classes_available );

		foreach ( $classes_available as $key => $class ) {

			if ( 'uncanny_learndash_toolkit\Sample' === $key || 'uncanny_custom_toolkit\Sample' === $key || 'uncanny_pro_toolkit\Sample' === $key ) {
				continue;
			}

			$class_name = $key;

			// Add module ID and UTM ID
			if ( isset( $class['id'] ) ) {
				self::$modules[ $key ]['id']     = $class['id'];
				self::$modules[ $key ]['utm_id'] = str_replace( '-', '_', $class['id'] );
			} else {
				self::$modules[ $key ]['id'] = str_replace( '\\', '_', $key ) . '-' . uniqid();
			}

			self::$modules[ $key ]['class_name']   = $class_name;
			self::$modules[ $key ]['settings_id']  = str_replace( __NAMESPACE__, '', stripslashes( $key ) );
			self::$modules[ $key ]['has_settings'] = true;
			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				self::$modules[ $key ]['has_settings'] = false;
			}

			self::$modules[ $key ]['is_active'] = false;
			if ( isset( $active_classes[ $class_name ] ) || isset( $active_classes[ stripslashes( $class_name ) ] ) ) {
				self::$modules[ $key ]['is_active'] = true;
			}

			$dependants_exist                         = $class['dependants_exist'];
			self::$modules[ $key ]['can_use']         = true;
			self::$modules[ $key ]['cant_use_notice'] = '';
			if ( true !== $dependants_exist ) {
				self::$modules[ $key ]['can_use']         = false;
				self::$modules[ $key ]['cant_use_notice'] = $dependants_exist;
				if ( 0 === strpos( self::$modules[ $key ]['cant_use_notice'], 'Plugin:' ) ) {
					self::$modules[ $key ]['cant_use_notice'] .= ' is needed for this addon.';
				}
			}

			// Setting Modal Popup
			self::$modules[ $key ]['version'] = '';
			if ( key_exists( 'type', $class ) && ! empty( $class['type'] ) ) {
				self::$modules[ $key ]['version'] = $class['type'];
			}

			self::$modules[ $key ]['title'] = $class['title'];

			// Link to KB for Feature
			if ( null !== $class['kb_link'] ) {
				self::$modules[ $key ]['kb_link'] = $class['kb_link'];
			}

			self::$modules[ $key ]['description'] = $class['description'];

			if ( isset( $class['category'] ) && ! empty( $class['category'] ) ) {
				self::$modules[ $key ]['category'] = $class['category'];
			} else {
				self::$modules[ $key ]['category'] = '';
			}
		}
	}

	/**
	 *
	 */
	public static function get_psuedo_pro_modules() {

		$pro_modules = [
			[
				'id'          => 'download-certificates-in-bulk',
				'title'       => esc_html__( 'Download certificates in bulk', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Generate certificate PDF files for a group, course or quiz and download them in bulk as a Group Leader or administrator.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/download-certificates-in-bulk/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'days-until-course-expiry',
				'title'       => esc_html__( 'Days Until Course Expiry', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Use this shortcode to display the number of days until the learner\'s access expires for the current course. This is a useful shortcode to include on course pages.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/days-until-course-expiry/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'email-course-certificates',
				'title'       => esc_html__( 'Email Course Certificates', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Sends a copy of certificate earned from course completion and saves certificates on the server.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/send-course-certificates-email/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'simple-course-timer',
				'title'       => esc_html__( 'Simple Course Timer', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Tracks time spent in all LearnDash courses and detects when a user is idle. Course completion time and total course time are both added to LearnDash reports. Enables blocking access to quizzes until minimum time spent in course.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/simple-course-timer/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'single-page-courses',
				'title'       => esc_html__( 'Single Page Courses', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Create independent course pages that don\'t require lessons or topics.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/single-page-courses/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'email-quiz-certificates',
				'title'       => esc_html__( 'Email Quiz Certificates', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Sends a copy of certificates earned from quiz completion and saves certificates on the server.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/send-certificates-by-email/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'autocomplete-lessons-topics-on-gravity-forms-submission',
				'title'       => esc_html__( 'Autocomplete Lessons & Topics on Gravity Form Submission', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Automatically mark LearnDash lessons and topics as completed when the user submits Gravity Forms.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/gravity-forms-auto-completes-lessons-topics/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'improved-group-leader-interface',
				'title'       => esc_html__( 'Improved Group Leader Interface', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Enhances the experience of LearnDash Group Leaders by providing direct access to reports and removing unnecessary distractions from the admin panel.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/group-leader-access/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'group-logo-list',
				'title'       => esc_html__( 'Group Logo/List', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Add group-specific logos to any page, including registration pages. A shortcode to list a user\'s LearnDash Groups is also available.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/ld-group-logo-list/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'import-users',
				'title'       => esc_html__( 'Import Users', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Create or update users and assign them to courses and LearnDash Groups from a CSV file.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/import-learndash-users/',
				'category'    => 'wordpress',
			],
			[
				'id'          => 'group-expiration',
				'title'       => esc_html__( 'Group Expiration', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Set expiration dates for LearnDash groups so that course enrolment for the group is removed on the specified day. Reminder emails can be sent to users advising them of group expiration.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/learndash-group-expiration/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'group-registration',
				'title'       => esc_html__( 'Group Registration', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Allow users to add themselves directly to LearnDash Groups on registration by assigning each group a unique registration URL. Users can also change or add groups themselves by visiting group URLs.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/group-sign-up/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'course-dashboard',
				'title'       => esc_html__( 'Course Dashboard', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Use the [uo_dashboard] shortcode to display the list of enrolled courses for the current user. This is essentially a modified version of the [ld_profile] shortcode without profile data.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/learndash-course-dashboard/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'duplicate-pages-posts',
				'title'       => esc_html__( 'Duplicate Pages & Posts', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Easily clone pages, posts, LearnDash courses, lessons, topics, quizzes and more. This plugin handles quiz duplication properly.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/duplicate-pages-posts/',
				'category'    => 'wordpress',
			],
			[
				'id'          => 'reset-progress-button',
				'title'       => esc_html__( 'Reset Progress Button', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Inserts a button that allows learners to reset the course.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/learndash-reset/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'lesson-topic-quiz-table-colors-legacy',
				'title'       => esc_html__( 'Lesson/Topic/Quiz Table Colors', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Change the background and header text color of LearnDash tables. This module modifies the course, lesson, topic, quiz, and Pro Dashboard tables.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/learndash-table-colors/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'learner-transcript',
				'title'       => esc_html__( 'Learner Transcript', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Add printable transcripts to the front end for your learners. This is a great way for learners to have a record of all course progress and overall standing.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/learner-transcript/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'autocomplete-lessons-topics',
				'title'       => esc_html__( 'Autocomplete Lessons & Topics', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Automatically mark all lessons and topics as completed on user visit and remove Mark Complete buttons. Global settings can be overridden for individual lessons and topics.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'autocomplete-lessons-topics-on-quiz-results-page',
				'title'       => esc_html__( 'Autocomplete Lessons & Topics on Quiz Results Page', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Automatically mark LearnDash lessons and topics as completed when the user reaches the quiz results page with a passing mark.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics-on-quiz-completion/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'enhanced-lessons-topics-grid',
				'title'       => esc_html__( 'Enhanced Lessons/Topics Grid', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Add a highly customizable grid of LearnDash lessons/topics to the front end, learner dashboard or anywhere you want. This is a great tool for sites with a large number of lessons/topics.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/enhanced-lessons-topics-grid/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'sample-lesson-label-legacy',
				'title'       => esc_html__( 'Sample Lesson Label', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Add visual label to sample lessons.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/sample-lesson-label/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'enhanced-course-grid',
				'title'       => esc_html__( 'Enhanced Course Grid', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Add a highly customizable grid of LearnDash courses to the front end, learner dashboard or anywhere you want. This is a great tool for sites with a large number of courses.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/enhanced-course-grid/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'drip-lessons-by-group',
				'title'       => esc_html__( 'Drip Lessons by Group', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Unlock access to LearnDash lessons by setting dates for LearnDash Groups rather than for all enrolled users.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'http://www.uncannyowl.com/knowledge-base/drip-lessons-by-ldgroup/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'autocomplete-lessons-topics-when-quiz-is-graded',
				'title'       => esc_html__( 'Autocomplete Lessons & Topics When Quiz is Graded', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Automatically mark LearnDash lessons and topics as completed when the associated quiz is manually graded.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics-when-quiz-is-graded/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'certificate-preview',
				'title'       => esc_html__( 'Certificate Preview', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Get a preview of your quiz or course certificate without leaving the editor.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/certificate-preview/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'enhanced-learnDash-csv-reports',
				'title'       => esc_html__( 'Enhanced LearnDash CSV Reports', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'This module will add additional columns to the LearnDash csv reports (course and quiz).', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/enhanced-learndash-csv-reports/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'group-forums-with-bbpress',
				'title'       => esc_html__( 'Group Forums with bbPress', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Create group-specific discussion forums with bbPress. Only group members will have access to group-specific forums. Includes a handy widget.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/learndash-group-forums-with-bbpress/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'group-login-redirect',
				'title'       => esc_html__( 'Group Login Redirect', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Adds a group-specific login redirect setting to LearnDash groups. Automatically redirect group members to a specific page on login.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/learndash-group-login-redirect/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'lazy-loading-course-navigation',
				'title'       => esc_html__( 'Lazy Loading Course Navigation', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Shortcode that loads a course navigation tree via AJAX. Useful on sites with many lessons and topics where the native LearnDash Course Navigation widget is extending page load time.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/lazy-loading-course-navigation/',
				'category'    => 'learndash',
			],
			[
				'id'          => 'restrict-page-access',
				'title'       => esc_html__( 'Restrict Page Access', 'uncanny-learndash-toolkit' ),
				'description' => esc_html__( 'Restrict access to any page by logged in/out status, course enrollment, group membership, or role. Display a message or automatically redirect users that are denied access.', 'uncanny-learndash-toolkit' ),
				'kb_link'     => 'https://www.uncannyowl.com/knowledge-base/restrict-page-access/',
				'category'    => [ 'learndash', 'wordpress' ],
			],
		];

		foreach ( $pro_modules as &$module ) {

			$module['cant_use_notice'] = str_replace( '{{', '<a href="' . Config::utm_parameters( 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/ ', 'modules', 'pro-notice' ) . '" target="_blank">', str_replace( '}}', '</a>', __( 'This module requires Uncanny Toolkit Pro. {{Buy it here}}', 'uncanny-learndash-toolkit' ) ) );

			$module['can_use']    = false;
			$module['is_active']  = false;
			$module['version']    = 'pro';
			$module['is_pro']     = true;
			$module['pseudo-pro'] = true;
			$module['utm_id']     = $module['id'];

			self::$modules[] = $module;
		}
	}

	/**
	 * Maybe apply notification filters to Toolkit pages.
	 * 
	 * @return void
	 */
	public static function maybe_filter_non_toolkit_admin_notices() {
		
		$toolkit_pages = array(
			'uncanny-toolkit',
			'uncanny-toolkit-kb',
			'uncanny-toolkit-plugins',
			'uncanny-toolkit-license',
			'learndash-toolkit-import-user', // Module : Bulk Import Users
			'uo-my-courses', // Module : Improved Group Leader Interface
		);

		// Bail if we're not on a Toolkit screen.
		if ( empty( $_REQUEST['page'] ) || ! in_array( strtolower( $_REQUEST['page'] ), $toolkit_pages, true ) ) {
			return;
		}

		// Run filter on all admin notices.
		self::filter_non_toolkit_admin_notices( 'user_admin_notices' );
		self::filter_non_toolkit_admin_notices( 'admin_notices' );
		self::filter_non_toolkit_admin_notices( 'all_admin_notices' );
	}

	/**
	 * Filter out non-Toolkit admin notices.
	 * 
	 * @param string $notice_type The type of notice to filter.
	 * 
	 * @return void
	 */
	public static function filter_non_toolkit_admin_notices( $notice_type ) {

		global $wp_filter;

		if ( empty( $wp_filter[ $notice_type ] ) ) {
			return;
		}

		if ( ! is_array( $wp_filter[ $notice_type ]->callbacks ) ) {
			return;
		}

		// All Toolkit lowercased namespaces.
		$allowed_sources = array(
			'uncanny_learndash_toolkit',
			'uncanny_pro_toolkit',
			'uncanny_custom_toolkit',
			'uncanny_owl'
		);

		foreach ( $wp_filter[ $notice_type ]->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof \Closure ) {
					unset( $wp_filter[ $notice_type ]->callbacks[ $priority ][ $name ] );
					continue;
				}

				// Determine the source of the notice
                $source = '';
				if ( isset( $arr['function'] ) && is_array( $arr['function'] ) && ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ) {
                    $source = strtolower( get_class( $arr['function'][0] ) );
                } elseif ( ! empty( $name ) ) {
                    $source = strtolower( $name );
                }

				// Remove the notice if its source is not in the list of allowed sources
                $allowed = false;
                foreach ( $allowed_sources as $allowed_source ) {
                    if ( strpos( $source, $allowed_source ) !== false) {
                        $allowed = true;
                        break;
                    }
                }

                if ( ! $allowed ) {
                    unset( $wp_filter[ $notice_type ]->callbacks[ $priority ][ $name ] );
                }
			}
		}

	}

}