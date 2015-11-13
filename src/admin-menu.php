<?php

namespace uncanny_learndash_public;

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

		//	spl_autoload_register( array('Boot', 'auto_loader'));
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

		$position = 80; // 80 - Below Settings Menu
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

	}

	/*
	 * Whitelisted Options that are save on the page
	 */
	public static function register_options_menu_page_settings() {
		register_setting( 'uncanny_learndash_public-group', 'uncanny_public_active_classes' );
	}

	public static function get_available_classes(){
		// loop file in cleeses folded and call get_details
		// check fun exsit first
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

			// TODO: test for function
			$class = new \ReflectionClass( $class_name );
			if ( $class->implementsInterface( 'get_details' ) )
			{
				$class_title = __( 'LearnDash Cert Widget', Config::get_text_domain() );
				$class_description = __( 'Display a list of all LearnDash Groups to which a user belongs on the user\'s profile page', Config::get_text_domain() );
				$details[ $class_name ] = array( 'title' => $class_title, 'description' => $class_description );
				continue;
			}
			$details[ $class_name ] = $class_name::get_details();
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

	}

	/**
	 * Create Theme Options page
	 */
	public static function options_menu_page_output() {

		$classes_available = self::get_available_classes();

		// Get an array of options from the database
		$active_classes = Config::get_available_classes();

		?>
		<div class="">
			<img src="<?php echo Config::get_admin_media('Uncanny-Owl-logo.png'); ?>" />
			<hr class="uo-underline">
			<h2>READY TO BE POPULATED</h2>
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
		foreach ( $classes_available as $key => $class ) {
			$is_activated = 'uo_feature_deactivated';
			$class_name = $key;
			if( isset( $active_classes[ $class_name ] ) ){
				$is_activated = 'uo_feature_activated';
			}
		?>
			<div class="uo_feature">
				<div class="uo_feature_title"><?php echo $class['title']; ?></div>
				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_feature_button <?php echo $is_activated; ?>">
					<div class="uo_feature_button_toggle"></div>
					<label class="uo_feature_label" for="<?php echo $class_name; ?>">Activate <?php echo $class['title']; ?></label>
					<input class="uo_feature_checkbox" type="checkbox" id="<?php echo $class_name; ?>" name="uncanny_public_active_classes[<?php echo  $class_name; ?>]" value="<?php echo  $class_name; ?>" <?php
					if (  array_key_exists( $class_name,$active_classes ) ) {
						checked( $active_classes[ $class_name ], $class_name, true );
					}
					?>/>
				</div>
			</div>
		<?php
		}
	}
}