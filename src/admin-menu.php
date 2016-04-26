<?php

namespace uncanny_learndash_toolkit;

use ReflectionClass;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class AdminMenu
 * @package uncanny_custom_toolkit
 */
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
		$menu_slug = 'uncanny-learnDash-toolkit';
		$function = array( __CLASS__, 'options_menu_page_output' );

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
	 * Populates an array of classes in internal and external file in the classes folder
	 *
*@param mixed(Array || false) $external_classes
	 *
	* @return array
	*/
	public static function get_available_classes( $external_classes = false ) {

		$class_details = Array();

		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';

		$files = scandir( $path );

		$internal_details = self::get_class_details( $path, $files, __NAMESPACE__ );

		$class_details = array_merge( $class_details, $internal_details );

		if ( false !== $external_classes ){

			foreach( $external_classes as $external_class ){

				// Make sure that the external path being loaded exists
				if( false !== $external_class['path'] ){
					$external_files = scandir( $external_class['path'] );
					$external_details = self::get_class_details( $external_class['path'], $external_files, $external_class['namespace'] );
					$class_details = array_merge( $class_details, $external_details );
				}

			}

		}

		return $class_details;
	}

	/*
	 * get_class_details
	 * @param string $path
	 * @param array $files
	 * @param string $namespace
	 *
	 * @return array $details
	 */
	private static function get_class_details( $path, $files, $name_space ){

		$details = array();

		foreach ( $files as $file ) {
			if ( is_dir( $path . $file ) || '..' === $file || '.' === $file ) {
				continue;
			}

			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = $name_space  . '\\' . str_replace( ' ', '', $class_name );

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

	/*
	 * Load Scripts
	 * @paras string $hook Admin page being loaded
	 */
	public static function scripts( $hook ) {
		if ( 'toplevel_page_uncanny-learnDash-toolkit' != $hook ) {
			return;
		}

		// Admin CSS
		wp_enqueue_style( 'uo-menu-slug-css', Config::get_admin_css( 'admin-style.css' ) );
		// Admin JS
		wp_enqueue_script( 'uo-menu-slug-js', Config::get_admin_js( 'script.js' ), array( 'jquery' ), false, true );
		// Admin CSS
		wp_enqueue_style( 'uo-menu-slug-css-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );

	}

	/**
	 * Create Theme Options page
	 */
	public static function options_menu_page_output() {

		// check if custom ou plugin is available
		$uo_custom_classes['path'] = self::check_for_other_uo_plugin_classes('custom');
		$uo_custom_classes['namespace'] = 'uncanny_custom_toolkit';

		$uo_pro_classes['path'] = self::check_for_other_uo_plugin_classes('pro');
		$uo_pro_classes['namespace'] = 'uncanny_pro_toolkit';

		// Get Available Classes from UO-Public
		$classes_available = self::get_available_classes( array( $uo_custom_classes, $uo_pro_classes ) );

		// Get an array of options from the database
		$active_classes = Config::get_active_classes();

		?>
		<div class="uo-admin-header">

			<a href="http://www.uncannyowl.com" target="_blank">
				<img src="<?php echo esc_url( Config::get_admin_media( 'Uncanny-Owl-logo.png' ) ); ?>" />
			</a>

			<hr class="uo-underline">

			<h2><?php esc_html_e( 'Thanks for using the Uncanny LearnDash Toolkit!', 'uncanny-learndash-toolkit' ); ?></h2>

			<p><?php
			printf(
				__( 'This plugin makes it easier to incorporate some of the features we see most requested on LearnDash sites. We\'re hard
				at work on a new Knowledge Base for this plugin, but for now, instructions are available at  
				<a href="%s" target="_blank">https://www.uncannyowl.com/uncanny-learndash-toolkit/</a>.', 'uncanny-learndash-toolkit' ) ,
			esc_url( 'https://www.uncannyowl.com/uncanny-learndash-toolkit/' ) );
			?></p>

			<p><?php
			printf(
				__( 'This is the first Uncanny Owl LearnDash plugin that we\'ve made available in the WordPress repository. We\'ll be
				releasing more over the next few months as well as adding new features to this plugin. Visit us at 
				<a href="%s" target="_blank">Uncanny Owl</a> so you don\'t miss any updates!', 'uncanny-learndash-toolkit' ) ,
			esc_url( 'https://www.uncannyowl.com/' ) );
			?></p>

			<p><?php
			printf(
				__( 'Developers are welcome to create pull requests on <a href="%s" target="_blank"><i class="fa fa-bitbucket" aria-hidden="true"> Bitbucket</i></a>.', 'uncanny-learndash-toolkit' ) ,
			esc_url( 'https://bitbucket.org/uncannyowl/uncanny-learndash-toolkit/' ) );
			?></p>

		</div>
		<form method="post" action="options.php">
		<?php
		settings_fields( 'uncanny_learndash_toolkit-group' );
		do_settings_sections( 'uncanny_learndash_toolkit-group' );
		?>
		<div class="uo_feature_container">
			<?php self::create_features( $classes_available, $active_classes ); ?>
		</div>
		<?php
		submit_button();
		echo '</form>';

	}

	/*
	 * Add add-ons to options page
	 *
	 * @param Array() $classes_available
	 * @param Array() $active_classes
	 *
	 */
	public static function create_features( $classes_available, $active_classes ) {

		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		if ( function_exists( 'get_magic_quotes_gpc' ) ) {
			if ( get_magic_quotes_gpc() ) {
				//strip slashes from all keys in array
				$active_classes = Config::stripslashes_deep( $active_classes );
			}
		}



		// Sort add ons alphabetically by title
		$add_on_titles = array();
		foreach ($classes_available as $key => $row) {
		    $add_on_titles[$key] = $row['title'];
		}
		array_multisort($add_on_titles, SORT_ASC, $classes_available);

		foreach ( $classes_available as $key => $class ) {

			// skip sample classes
			if( 'uncanny_learndash_toolkit\Sample' === $key ||
				'uncanny_custom_toolkit\Sample' === $key ||
				'uncanny_pro_toolkit\Sample' === $key ){
				continue;
			}

			if ( false === $class ) {
				?>
					<div class="uo_feature">
						<div class="uo_feature_title"><?php echo esc_html( $key ) ?></div>
						<div class="uo_feature_description"><?php
							esc_html_e( 'This class is not configured properly. Contact Support for assistance.', 'uncanny-learndash-toolkit' );
						?></div>
					</div>
				<?php
				continue;
			}

			$dependants_exist = $class['dependants_exist'];

			$is_activated = 'uo_feature_deactivated';
			$class_name = $key;
			if ( isset( $active_classes[ $class_name ] ) ) {
				$is_activated = 'uo_feature_activated';
			}
			if ( true !== $dependants_exist ) {
				$is_activated = 'uo_feature_needs_dependants';
			}

			$icon = '<div class="uo_icon"></div>';
			if ( $class['icon'] ) {
				$icon = $class['icon'];
			}

			if ( false === $class['settings'] || ! isset( $class['settings'] ) ) {
				$class['settings']['modal'] = '';
				$class['settings']['link'] = '';
			}

		?>

			<?php // Setting Modal Popup
			echo $class['settings']['modal']; ?>

			<div class="uo_feature">

				<?php // Settings Modal Popup trigger
				echo $class['settings']['link']; ?>

				<div class="uo_feature_title">

					<?php echo $class['title']; ?>

					<?php
					 // Link to KB for Feature
					if ( null !== $class['kb_link'] ) {
					?>
						<a class="uo_feature_more_info" href="<?php echo $class['kb_link']; ?>" target="_blank">
							<i class="fa fa-question-circle"></i>
						</a>
					<?php } ?>

				</div>

				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_icon_container"><?php echo $icon; ?></div>
				<div class="uo_feature_button <?php echo $is_activated; ?>">

					<?php
					if ( true !== $dependants_exist ) {
						echo '<div><strong>'. esc_html( $dependants_exist ) .'</strong>' . esc_html__( ' is needed for this add-on', 'uncanny-learndash-toolkit' ) . '</div>';
					} else {
						?>
						<div class="uo_feature_button_toggle"></div>
						<label class="uo_feature_label" for="<?php echo esc_attr( $class_name ) ?>">
							<?php echo ( esc_html__( 'Activate ', 'uncanny-learndash-toolkit' ) . $class['title'] ); ?>
						</label>
						<input class="uo_feature_checkbox" type="checkbox" id="<?php echo esc_attr( $class_name ); ?>" name="uncanny_toolkit_active_classes[<?php echo esc_attr( $class_name ) ?>]" value="<?php echo esc_attr( $class_name ) ?>" <?php
						if (  array_key_exists( $class_name, $active_classes ) ) {
						// Some wp installs remove slashes during db calls, being extra safe when comparing DB vs php values with stripslashes
							checked( stripslashes(  $active_classes[ $class_name ] ), stripslashes( $class_name ), true );
						}
						?>
						/>
					<?php } ?>
				</div>
			</div>
		<?php
		}
	}

	/*
	 * Check for Adds that are located in other UO plugins
	 *@param string $uo_plugin
	 *
	 *return mixed(false || String)
	*/
	private static function check_for_other_uo_plugin_classes( $uo_plugin ){

		// plugins dir
		$directory_contents = scandir( WP_PLUGIN_DIR );

		// loop through all contents
		foreach($directory_contents as $content){

			// exclude parent directories
			if( $content !=='.' or $content !== '..' ){

				// create absolute path
				$plugin_dir = WP_PLUGIN_DIR. '/' . $content;

				if( is_dir( $plugin_dir ) ){

					if( 'pro' === $uo_plugin ){
						if( 'uo-plugin-pro' === $content){
							// Check if plugin is active
								if ( is_plugin_active( $content.'/uncanny-toolkit-pro.php' ) ) {
									return $plugin_dir.'/src/classes/';
								}
						}
					}

					if( 'custom' === $uo_plugin ){

						$explode_directory = explode( '-', $content );
						if( 3 === count( $explode_directory ) ){
							// custom plugin directory is may be prefixed with client name
							// check suffix uo-custom-plugin
							if( in_array( 'uo', $explode_directory ) && in_array( 'custom', $explode_directory ) && in_array( 'plugin', $explode_directory ) ){

								// Check if plugin is active
								if ( is_plugin_active( $content.'/uncanny-toolkit-custom.php' ) ) {
									return $plugin_dir.'/src/classes/';
								}

							}
						}

					}

				}

			}
		}

		return false;

	}
}
