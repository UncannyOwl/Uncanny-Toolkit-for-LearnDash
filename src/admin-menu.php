<?php

namespace uncanny_learndash_toolkit;

use ReflectionClass;


class AdminMenu extends Boot {
	/**
	 * class constructor
	 */
	public function __construct() {
		// Setup Theme Options Page Menu in Admin
		if ( is_admin() ) {
			add_action( 'admin_menu', array( __CLASS__, 'register_options_menu_page' ) );
			add_action( 'admin_init', array( __CLASS__, 'register_options_menu_page_settings' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'scripts' ) );
		}

	}

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = esc_html__( 'Uncanny LearnDash Toolkit', 'uncanny-learndash-toolkit' );
		$menu_title = esc_html__( 'Uncanny Toolkit', 'uncanny-learndash-toolkit' );
		$capability = 'manage_options';
		$menu_slug  = 'uncanny-learnDash-toolkit';
		$function   = array( __CLASS__, 'options_menu_page_output' );

		// Menu Icon blends into sidebar when the default admin color scheme is used
		$admin_color_scheme = get_user_meta( get_current_user_id(), 'admin_color', true );
		if ( 'fresh' === $admin_color_scheme ) {
			$icon_url = Config::get_admin_media( 'menu-icon-light.png' );
		} else {
			$icon_url = Config::get_admin_media( 'menu-icon.png' );
		}

		$position = 81; // 81 - Above Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

	}

	/*
	* Whitelisted Options that are saved on the page
	*/
	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_toolkit-group', 'uncanny_toolkit_active_classes' );
	}

	/**
	 * @param $hook
	 */
	public static function scripts( $hook ) {

		if ( 'toplevel_page_uncanny-learnDash-toolkit' === $hook || 'uncanny-toolkit_page_uncanny-pro-license-activation' === $hook ) {
			// Admin CSS
			wp_enqueue_style( 'uo-menu-slug-css', Config::get_admin_css( 'admin-style.css' ), array(), UNCANNY_TOOLKIT_VERSION );
			// Admin JS
			wp_enqueue_script( 'uo-menu-slug-js', Config::get_admin_js( 'script.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
			wp_enqueue_script( 'uo-quicksand-js', Config::get_admin_js( 'jquery.quicksand.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
			wp_enqueue_script( 'quicksand-js', Config::get_admin_js( 'quicksand.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
			// Admin CSS
			wp_enqueue_style( 'uo-menu-slug-css-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
			// Load Native WP Color Picker
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

		$pro_ad = '';

		if ( ! $uo_pro_classes['path'] ) {
			$pro_ad .= '<a href="http://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/?utm_medium=freetoolkit&utm_campaign=settings" class="page-title-action" target="_blank">Upgrade Now</a>';
			$pro_ad .= '<p><strong>'.__( 'Ready to take your LearnDash site even further?', 'uncanny-learndash-toolkit' ).'</strong></p>';
			$pro_ad .= '<h2>'.__( 'Check out the Pro modules for the Uncanny LearnDash Toolkit!', 'uncanny-learndash-toolkit' ).'</h2>';
			$pro_ad .= '<div class="list">';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/redirect-user-if-not-enrolled/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Not Enrolled Redirect', 'uncanny-learndash-toolkit' ).'</a><span class="new-module">NEW!</span></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/enhanced-lessontopic-grid/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Enhanced Lesson/Topic Grid', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/send-course-certificates-email/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Send Course Certificates by Email', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/import-learndash-users/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Import LearnDash Users', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Autocomplete Lessons &amp; Topics', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/enhanced-course-grid/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Enhanced Course Grid', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/learndash-course-dashboard/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'LearnDash Course Dashboard', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/simple-course-timer/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Simple Course Timer', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/send-certificates-by-email/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Send Quiz Certificates by Email', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/group-sign-up/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'LearnDash Group Registration', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/knowledge-base/learner-transcript/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Learner Transcript', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '<li><a href="https://www.uncannyowl.com/article-categories/uncanny-toolkit-pro/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( '...and much more!', 'uncanny-learndash-toolkit' ).'</a></li>';
			$pro_ad .= '</div>';
			$pro_ad .= '<div style="clear:both;"></div>';

			$show_pro_toolkit_heading = 'style="display:none;"';
		} else {
			$show_pro_toolkit_heading = '';
		}

		$show_pro_ad = 'style="display:none;"';
		if ( '' !== $pro_ad ) {
			$show_pro_ad = '';
		}

		$plugins_ad    = '';
		$other_plugins = '';

		$other_plugins .= '<li><a href="https://automatorplugin.com/integration/learndash/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Uncanny Automator', 'uncanny-learndash-toolkit' ).'</a>'.__( ' - Create personalized learning paths and connect LearnDash with many other popular plugins.', 'uncanny-learndash-toolkit' ).'</li>';

		if ( ! defined( 'UNCANNY_REPORTING_VERSION' ) ) {
			$other_plugins .= '<li><a href="https://www.uncannyowl.com/downloads/tin-canny-reporting/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Tin Canny Learndash Reporting', 'uncanny-learndash-toolkit' ).'</a>'.__( ' - Add powerful reporting tools and support for Tin Can and SCORM modules.', 'uncanny-learndash-toolkit' ).'</li>';
		}

		if ( ! defined( 'CEU_VERSION' ) ) {
			$other_plugins .= '<li><a href="https://www.uncannyowl.com/downloads/uncanny-continuing-education-credits/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Uncanny Continuing Education Credits', 'uncanny-learndash-toolkit' ).'</a>'.__( ' - Track, report on and award certificates based on course credits.', 'uncanny-learndash-toolkit' ).'</li>';
		}

		if ( ! defined( 'UNCANNY_REPORTING_VERSION' ) ) {
			$other_plugins .= '<li><a href="https://www.uncannyowl.com/downloads/uncanny-learndash-codes/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Uncanny LearnDash Codes', 'uncanny-learndash-toolkit' ).'</a>'.__( ' - Generate codes to let users self-enroll into LearnDash groups and courses.', 'uncanny-learndash-toolkit' ).'</li>';
		}

		if ( ! defined( 'ULGM_VERSION' ) ) {
			$other_plugins .= '<li><a href="https://www.uncannyowl.com/downloads/uncanny-learndash-groups/?utm_medium=freetoolkit&utm_campaign=settings" target="_blank">'.__( 'Uncanny LearnDash Groups', 'uncanny-learndash-toolkit' ).'</a>'.__( ' - A must-have plugin if you use LearnDash Groups.', 'uncanny-learndash-toolkit' ).'</li>';
		}

		$show_plugins_ad = 'style="display:none;"';
		if ( '' !== $other_plugins ) {
			$show_plugins_ad = '';
			$plugins_ad      .= '<h2>'.__( 'Looking for even more LearnDash features?', 'uncanny-learndash-toolkit' ).'</h2>';
			$plugins_ad      .= '<div class="">';
			$plugins_ad      .= $other_plugins;
			$plugins_ad      .= '</div>';
			$plugins_ad      .= '<div style="clear:both;"></div>';
		}


		// Get Available Classes from UO-Public
		$classes_available = self::get_available_classes( array( $uo_custom_classes, $uo_pro_classes ) );

		// Get an array of options from the database
		$active_classes = Config::get_active_classes();

		?>
		<div class="uo-admin-header uo-wrap">
			<a href="http://www.uncannyowl.com" target="_blank">
				<img src="<?php echo esc_url( Config::get_admin_media( 'Uncanny-Owl-logo.png' ) ); ?>"/>
			</a>
			<hr class="uo-underline">
			<h2><?php esc_html_e( 'Thanks for using the Uncanny LearnDash Toolkit!', 'uncanny-learndash-toolkit' ); ?></h2>
			<p><?php
				printf(
					__( 'This plugin adds a number of simple but essential functions to LearnDash sites. To learn more about how to use these modules properly, please visit our introduction at <a href="%s" target="_blank" >https://www.uncannyowl.com/uncanny-learndash-toolkit/</a> and Knowledge Base at <a href="%s" target="_blank" >https://www.uncannyowl.com/article-categories/uncanny-learndash-toolkit/</a>.', 'uncanny-learndash-toolkit' ), esc_url( 'https://www.uncannyowl.com/uncanny-learndash-toolkit/' ), esc_url( 'https://www.uncannyowl.com/article-categories/uncanny-learndash-toolkit/' ) );
				?></p>
			<p <?php echo $show_pro_toolkit_heading; ?>>
				<?php
				printf(
					__( 'Instructions for the Pro suite of modules are in the Knowledge Base at <a href="%s" target="_blank" >https://www.uncannyowl.com/article-categories/uncanny-toolkit-pro/</a>.', 'uncanny-learndash-toolkit' ),
					esc_url( 'https://www.uncannyowl.com/article-categories/uncanny-toolkit-pro/' ) );
				?>
			</p>
			<p>
				<?php
				printf(
					__( 'Developers are invited to review and suggest changes to the Toolkit on  <a href="%s" target="_blank"><i class="fa fa-bitbucket" aria-hidden="true"> Bitbucket</i></a>.', 'uncanny-learndash-toolkit' ),
					esc_url( 'https://bitbucket.org/uncannyowl/uncanny-learndash-toolkit/' ) );
				?>
			</p>
		</div>

		<div class="ad-pro-toolkit uo-wrap" <?php echo $show_pro_ad; ?>>
			<?php echo $pro_ad; ?>
		</div>

		<div class="ad-pro-toolkit uo-wrap" <?php echo $show_plugins_ad; ?>>
			<?php echo $plugins_ad; ?>
		</div>


		<div class="module_activated" id="module_activated">
			<i class="fa fa-check-circle" aria-hidden="true"></i><span></span></div>
		<div class="module_deactivated" id="module_deactivated">
			<i class="fa fa-times-circle" aria-hidden="true"></i><span></span></div>
		<form id="filter">
			<h3>Filter Modules:</h3>
			<ul class="uo-filters">
				<li <?php if ( ! $uo_pro_classes['path'] ) { ?>style="display: none;"<?php } ?>>
					<fieldset>
						<label for="uo-type"><?php _e( 'Free/Pro Modules', 'uncanny-learndash-toolkit' ); ?></label>
						<select id="uo-type" name="type">
							<option value="0" selected="selected"><?php _e( '--- Free & Pro ---', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="free"><?php _e( 'Free', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="pro"><?php _e( 'Pro', 'uncanny-learndash-toolkit' ); ?></option>
							<?php if ( $uo_custom_classes['path'] ) { ?>
								<option value="custom"><?php _e( 'Custom Modules', 'uncanny-learndash-toolkit' ); ?></option>
							<?php } ?>
						</select>
					</fieldset>
				</li>
				<li>
					<fieldset>
						<label for="uo-tags"><?php _e( 'Module Category', 'uncanny-learndash-toolkit' ); ?></label>
						<select id="uo-tags" name="tags">
							<option value="0"><?php _e( '--- All Modules ---', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="learndash"><?php _e( 'LearnDash', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="general"><?php _e( 'General WordPress', 'uncanny-learndash-toolkit' ); ?></option>
						</select>
					</fieldset>
				</li>
				<li>
					<fieldset>
						<label id="sort" for="uo-sort"><?php _e( 'Module Status', 'uncanny-learndash-toolkit' ); ?></label>
						<select name="sort">
							<option value="0"><?php _e( '--- Active & Inactive ---', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="1"><?php _e( 'Active', 'uncanny-learndash-toolkit' ); ?></option>
							<option value="2"><?php _e( 'Inactive', 'uncanny-learndash-toolkit' ); ?></option>
						</select>
					</fieldset>
				</li>
			</ul>
		</form>
		<div class="switcher_content">
			<div class="switch-view">
				<div class="switch-btn grid-view selected" data-view="grid">
					<i class="fa fa-table" aria-hidden="true"></i>
					<?php _e('Grid view', 'uncanny-learndash-toolkit'); ?>
				</div>
				<div class="switch-btn list-view" data-view="list">
					<i class="fa fa-list-ul" aria-hidden="true"></i>
					<?php _e('List view', 'uncanny-learndash-toolkit'); ?>
				</div>
			</div>
		</div>
		<hr class="uo-underline">
		<!--<form method="post" action="options.php">
		--><?php
		/*		settings_fields( 'uncanny_learndash_toolkit-group' );
				do_settings_sections( 'uncanny_learndash_toolkit-group' );
				*/ ?>
		<div class="uo_feature_container">
			<?php self::create_features( $classes_available, $active_classes ); ?>
		</div>
		<?php
		/*		submit_button();
				echo '</form>';*/


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
	 * Populates an array of classes in internal and external file in the classes folder
	 *
	 * @param bool $external_classes
	 *
	 * @return array
	 */
	public static function get_available_classes( $external_classes = false ) {

		$class_details = array();

		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';

		$files = scandir( $path );

		$internal_details = self::get_class_details( $path, $files, __NAMESPACE__ );

		$class_details = array_merge( $class_details, $internal_details );

		if ( false !== $external_classes ) {
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
		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		if ( function_exists( 'get_magic_quotes_gpc' ) ) {
			if ( get_magic_quotes_gpc() ) {
				//strip slashes from all keys in array
				$active_classes = Config::stripslashes_deep( $active_classes );
			}
		}
		$modal_html = '';
		foreach ( $classes_available as $key => $class ) {
			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				$class['settings']['modal'] = '';
				$class['settings']['link']  = '';
			}
			// Setting Modal Popup
			$modal_html .= $class['settings']['modal'];
		}
		$toolkit_html = '';
		$toolkit_html .= '<ul id="features">';

		$add_on_titles = array();
		foreach ( $classes_available as $key => $row ) {
			$add_on_titles[ $key ] = $row['title'];
		}
		array_multisort( $add_on_titles, SORT_ASC, $classes_available );

		foreach ( $classes_available as $key => $class ) {
			// skip sample classes
			//if ( strpos( $key, 'Sample' ) ) {
			//	continue;
			//}
			if ( 'uncanny_learndash_toolkit\Sample' === $key || 'uncanny_custom_toolkit\Sample' === $key || 'uncanny_pro_toolkit\Sample' === $key ) {
				continue;
			}
			$class_name = $key;

			if ( false === $class ) {
				$toolkit_html .= '<li class="uo_feature" data-id="' . str_replace( array(
						'uncanny_learndash_toolkit',
						'\\',
						'uncanny_pro_toolkit',
					), '', $class_name ) . '" data-tags="' . $class['tags'] . '" data-active="0" data-type="' . $class['type'] . '">';
				$toolkit_html .= '<div class="uo_feature_title"> ' . esc_html( $key ) . '</div>';
				$toolkit_html .= '<div class="uo_feature_description">' . esc_html_e( 'This class is not configured properly. Contact Support for assistance.', 'uncanny-learndash-toolkit' ) . '</div>';
				$toolkit_html .= '</li>';
				continue;
			}
			$dependants_exist = $class['dependants_exist'];

			$is_activated = 'uo_feature_deactivated';
			$is_active    = 2;
			if ( isset( $active_classes[ $class_name ] ) || isset( $active_classes[ stripslashes( $class_name ) ] ) ) {
				$is_activated = 'uo_feature_activated';
				$is_active    = 1;
			}
			if ( true !== $dependants_exist ) {
				$is_activated = 'uo_feature_needs_dependants';
			}

			$icon = '<div class="uo_icon"></div>';
			if ( $class['icon'] ) {
				$icon = $class['icon'];
			}

			if ( ! isset( $class['settings'] ) || false === $class['settings'] ) {
				$class['settings']['modal'] = '';
				$class['settings']['link']  = '';
			}
			// Setting Modal Popup
			//$toolkit_html .= $class['settings']['modal'];
			if ( key_exists( 'tags', $class ) && ! empty( $class['tags'] ) ) {
				$tags = $class['tags'];
			} else {
				$tags = 'general';
			}
			if ( key_exists( 'type', $class ) && ! empty( $class['type'] ) ) {
				$type = $class['type'];
			} else {
				$type = 'custom';
			}

			$toolkit_html .= '<li class="uo_feature" data-id="' . str_replace( array(
					'uncanny_learndash_toolkit',
					'uncanny_pro_toolkit',
				), '', stripslashes( $class_name ) ) . '" data-tags="' . $tags . '" data-active="' . $is_active . '" data-type="' . $type . '">';

			// Settings Modal Popup trigger
			$toolkit_html .= $class['settings']['link'];
			$toolkit_html .= '<div class="uo_feature_title">';
			$toolkit_html .= $class['title'];
			// Link to KB for Feature
			if ( null !== $class['kb_link'] ) {
				$toolkit_html .= '<a class="uo_feature_more_info" href="' . $class['kb_link'] . '" target="_blank"><i class="fa fa-info-circle"></i></a>';
			}
			$toolkit_html .= '</div>';
			$toolkit_html .= '<div class="uo_feature_description">' . $class['description'] . '</div>';
			$toolkit_html .= '<div class="uo_icon_container">' . $icon . '</div>';
			$toolkit_html .= '<div class="uo_feature_button ' . $is_activated . '">';
			?>

			<?php
			if ( true !== $dependants_exist ) {

				if ( strpos( $dependants_exist, '@uo_custom_message' ) !== false ) {
					$dependants_exist = str_replace( '@uo_custom_message', '', $dependants_exist );
					$toolkit_html     .= '<div><strong>' . esc_html( $dependants_exist ) . '</strong></div>';
				} else {
					$toolkit_html .= '<div><strong>' . esc_html( $dependants_exist ) . '</strong>' . esc_html__( ' is needed for this add-on', 'uncanny-learndash-toolkit' ) . '</div>';
				}
			} else {
				$toolkit_html .= '<div class="uo_feature_button_toggle"></div>';
				$toolkit_html .= '<label class="uo_feature_label" for="' . esc_attr( $class_name ) . '">' . esc_html__( 'Activate ', 'uncanny-learndash-toolkit' ) . $class['title'] . '</label>';
				$toolkit_html .= '<input class="uo_feature_checkbox" data-class="' . $class['title'] . '" type="checkbox" id="' . esc_attr( $class_name ) . '" name="uncanny_toolkit_active_classes[' . esc_attr( $class_name ) . ']" value="' . esc_attr( $class_name ) . '"';
				if ( array_key_exists( $class_name, $active_classes ) ) {
					// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values with stripslashes
					$toolkit_html .= checked( stripslashes( $active_classes[ $class_name ] ), stripslashes( $class_name ), false );
				}
				$toolkit_html .= ' />';
			}
			$toolkit_html .= '</div>';
			$toolkit_html .= '</li>';
		}
		$toolkit_html .= '</ul>';
		echo $modal_html . $toolkit_html;
	}
}