<?php

namespace uncanny_learndash_public;

use ReflectionClass;

if ( ! defined( 'WPINC' ) ) {
	die;
}

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

			// Add a simple settings link to our page from the plugins list
			$prefix = is_network_admin() ? 'network_admin_' : ''; // TODO Multi-Site
			add_filter( 'plugin_action_links_' . ULP_PLUGIN_BASENAME, array( __CLASS__, 'link_to_plugins_page' ), 10, 1);
		}

	}

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = __( 'UO Page Title', Config::get_text_domain() );
		$menu_title =  __( 'UO Addons', Config::get_text_domain() );
		$capability = 'manage_options';
		$menu_slug = 'uo-menu-slug';
		$function = array( __CLASS__, 'options_menu_page_output' );

		// Menu Icon blends into sidebar when the default admin color scheme is used
		$admin_color_scheme =  get_user_meta( get_current_user_id(), 'admin_color', true );
		if( $admin_color_scheme === 'fresh' ){
			$icon_url = Config::get_admin_media('menu-icon-light.png');
		}else{
			$icon_url = Config::get_admin_media('menu-icon.png');
		}

		$position = 81; // 81 - Above Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

	}

	/*
	 * Whitelisted Options that are saved on the page
	 */
	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_public-group', 'uncanny_public_active_classes' );
	}

	/**
 * @return array
*/public static function get_available_classes(){
		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';
		$files = scandir( $path );
		$details = array();
		foreach ( $files as $file ) {
			if ( is_dir ( $path . $file ) ) {
				continue;
			}
			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = __NAMESPACE__  . '\\' . str_replace( ' ', '', $class_name );

			// test for required functions
			$class = new ReflectionClass( $class_name );
			if ( $class->implementsInterface( 'uncanny_learndash_public\RequiredFunctions' ) )
			{
				$details[ $class_name ] = $class_name::get_details();
			}else{
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
		if ( 'toplevel_page_uo-menu-slug' != $hook ) {
		   return;
		}

		// Admin CSS
		wp_enqueue_style( 'uo-menu-slug-css', Config::get_admin_css('admin-style.css') );
		// Admin JS
		wp_enqueue_script( 'uo-menu-slug-js', Config::get_admin_js('script.js'), array( 'jquery' ), false, true );
		// Admin CSS
		wp_enqueue_style( 'uo-menu-slug-css-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );

	}

	/**
	 * @param Array $actions		Plugin action links.
	 *
	 * @return Array
	 */
	public static function link_to_plugins_page( $actions ) {
		array_unshift($actions, '<a href="'.menu_page_url('uo-menu-slug', false).'">'.__( 'Settings', self::get_text_domain() ).'</a>');
		return $actions;
	}

	/**
	 * Create Theme Options page
	 */
	public static function options_menu_page_output() {

		$classes_available = self::get_available_classes();

		// Get an array of options from the database
		$active_classes = Config::get_available_classes();

		?>
		<div class="uo-admin-header">

			<a href="http://www.uncannyowl.com" target="_blank">
				<img src="<?php echo Config::get_admin_media('Uncanny-Owl-logo.png'); ?>" />
			</a>

			<hr class="uo-underline">

			<h2>Thanks for using the Uncanny LearnDash Toolkit!</h2>

			<p>This plugin makes it easier to incorporate some of the most common things we see requested on LearnDash
			sites. Instructions for each feature are provided below, and we also have a helpful screencasts available at
			<strong>!!![link]!!!</strong>. Consider turning off any features you don’t need to maximize performance.</p>

			<p>This is Uncanny Owl’s first LearnDash plugin that we've published in the WordPress repository. We’ll be
			releasing more plugins over the next few months as well as adding new features to this plugin. Sign up for
			our mailing list at <a href="www.uncannyowl.com" target="_blank">Uncanny Owl</a> so you don’t miss any updates!</p>

		</div>
		<form method="post" action="options.php">
		<?php
		settings_fields( 'uncanny_learndash_public-group' );
		do_settings_sections( 'uncanny_learndash_public-group' );
		?>
		<div class="uo_feature_container">
			<?php self::create_features( $classes_available, $active_classes ); ?>
		</div>

		<?php

		submit_button();
		echo '</form>';

	}

	/*
	 * @param Array() $classes_available
	 * @param Array() $active_classes
	 *
	 * return echoed String
	 */
	public static function create_features( $classes_available, $active_classes ){

		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		if( function_exists( 'get_magic_quotes_gpc' )){
			if( get_magic_quotes_gpc() ){
				//strip slashes from all keys in array
				$active_classes = Config::stripslashes_deep($active_classes);
			}
		}

		foreach ( $classes_available as $key => $class ) {
			if( false === $class){
				?>
					<div class="uo_feature">
						<div class="uo_feature_title"><?php echo $key; ?></div>
						<div class="uo_feature_description">This class is not configured properly. Contact Support for assistance.</div>
					</div>
				<?php
				continue;
			}

			$dependants_exist = $class['dependants_exist'];

			$is_activated = 'uo_feature_deactivated';
			$class_name = $key;
			if( isset( $active_classes[ $class_name ] ) ){
				$is_activated = 'uo_feature_activated';
			}
			if( TRUE !== $dependants_exist ){
				$is_activated = 'uo_feature_needs_dependants';
			}

			$icon = '<div class="uo_icon"></div>';
			if( $class['icon'] ){
				$icon = $class['icon'];
			}

			if( false === $class['settings'] || !isset( $class['settings'] ) ){
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

					<?php // Link to KB for Feature
						if( null !== $class['kb_link'] ){ ?>
							<a class="uo_feature_more_info" href="<?php echo $class['kb_link']; ?>" target="_blank">
								<i class="fa fa-info-circle"></i>
							</a>
					<?php } ?>

				</div>

				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_icon_container"><?php echo $icon; ?></div>
				<div class="uo_feature_button <?php echo $is_activated; ?>">

					<?php
					if( TRUE !== $dependants_exist ){
					 echo '<div><strong>'. $dependants_exist .'</strong> is needed for this add-on</div>';
					}else{
						?>
						<div class="uo_feature_button_toggle"></div>
						<label class="uo_feature_label" for="<?php echo $class_name; ?>">Activate <?php echo $class['title']; ?></label>
						<input class="uo_feature_checkbox" type="checkbox" id="<?php echo $class_name; ?>" name="uncanny_public_active_classes[<?php echo  $class_name; ?>]" value="<?php echo  $class_name; ?>" <?php
						if (  array_key_exists( $class_name,$active_classes ) ) {
							checked( $active_classes[ $class_name ], $class_name, true );
						}
						?>
						/>
					<?php } ?>

				</div>
			</div>
		<?php
		}
	}
}