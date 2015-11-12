<?php

namespace uncanny_learndash_public;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class AdminMenu {

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

		$page_title = 'UO Page Title';
		$menu_title = 'UO Menu Title';
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

	/*
	 * Load Scripts
	 * @paras string $hook Admin page being loaded
	 */
	public function scripts( $hook ) {
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
		// Define Features
		$classes_available['LearndashGroupUserProfile']['class_name'] = 'LearndashGroupUserProfile';
		$classes_available['LearndashGroupUserProfile']['title'] = __('LearnDash Groups in User Profiles', Config::get_text_domain() );
		$classes_available['LearndashGroupUserProfile']['description'] = __('Display a list of all LearnDash Groups to which a user belongs on the user\'s profile page', Config::get_text_domain() );

		$classes_available['WidgetCert']['class_name'] = 'WidgetCert';
		$classes_available['WidgetCert']['title'] = __('Certificates Widget', Config::get_text_domain() );
		$classes_available['WidgetCert']['description'] = __('Widget to list the certificates from LMS for the current user', Config::get_text_domain() );


		/* DUMMY DATE FOR RESPONSIVE TESTING*/
		$classes_available['LearndashGroupUserProfilexz']['class_name'] = 'LearndashGroupUserProfilex';
		$classes_available['LearndashGroupUserProfilexz']['title'] = __('LearnDash Groups Something', Config::get_text_domain() );
		$classes_available['LearndashGroupUserProfilexz']['description'] = __('Display a list of all LearnDash Somethings', Config::get_text_domain() );

		$classes_available['LearndashGroupUserProfilexy']['class_name'] = 'LearndashGroupUserProfiley';
		$classes_available['LearndashGroupUserProfilexy']['title'] = __('LearnDash Groups Something', Config::get_text_domain() );
		$classes_available['LearndashGroupUserProfilexy']['description'] = __('Display a list of all LearnDash Somethings', Config::get_text_domain() );

		$classes_available['LearndashGroupUserProfilexw']['class_name'] = 'LearndashGroupUserProfilew';
		$classes_available['LearndashGroupUserProfilexw']['title'] = __('LearnDash Groups Something', Config::get_text_domain() );
		$classes_available['LearndashGroupUserProfilexw']['description'] = __('Display a list of all LearnDash Somethings', Config::get_text_domain() );

		$classes_available['LearndashGroupUserProfilexq']['class_name'] = 'LearndashGroupUserProfileq';
		$classes_available['LearndashGroupUserProfilexq']['title'] = __('LearnDash Groups Something', Config::get_text_domain() );
		$classes_available['LearndashGroupUserProfilexq']['description'] = __('Display a list of all LearnDash Somethings', Config::get_text_domain() );


		// Get an array of options from the database
		//update_option( 'uncanny_public_active_classes', array());

		$active_classes = get_option( 'uncanny_public_active_classes' );

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
		foreach($classes_available as $class ){
		$is_activated = 'uo_feature_deactivated';
		$class_option = $class['class_name'];
		if( isset( $active_classes[ $class_option ] ) ){
			$is_activated = 'uo_feature_activated';
		}
		?>
			<div class="uo_feature">
				<div class="uo_feature_title"><?php echo $class['title']; ?></div>
				<div class="uo_feature_description"><?php echo $class['description']; ?></div>
				<div class="uo_feature_button <?php echo $is_activated; ?>">
					<div class="uo_feature_button_toggle"></div>
					<label class="uo_feature_label" for="<?php echo $class_option; ?>">Activate <?php echo $class['title']; ?></label>
					<input class="uo_feature_checkbox" type="checkbox" id="<?php echo $class_option; ?>" name="uncanny_public_active_classes[<?php echo  $class_option; ?>]" value="1" <?php checked( $active_classes[$class_option], '1', true ); ?>/>
				</div>
			</div>
		<?php
		}
	}
}