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
		}

		//parent::__construct();
	}

	/**
	 * Create Plugin options menu
	 */
	public static function register_options_menu_page() {

		$page_title = __( 'Uncanny LearnDash Toolkit', Config::get_text_domain() );
		$menu_title = __( 'Uncanny Toolkit', Config::get_text_domain() );
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
		register_setting( 'uncanny_learndash_public-group', 'uncanny_public_active_classes' );
	}

	/**
	 * @return array
	*/
	public static function get_available_classes() {
		// loop file in classes folded and call get_details
		// check function exist first
		$path = dirname( __FILE__ ) . '/classes/';
		$files = scandir( $path );
		$details = array();
		foreach ( $files as $file ) {
			if ( is_dir( $path . $file ) ) {
				continue;
			}
			//get class name
			$class_name = str_replace( '.php', '', $file );
			$class_name = str_replace( '-', ' ', $class_name );
			$class_name = ucwords( $class_name );
			$class_name = __NAMESPACE__  . '\\' . str_replace( ' ', '', $class_name );

			// test for required functions
			$class = new ReflectionClass( $class_name );
			if ( $class->implementsInterface( 'uncanny_learndash_public\RequiredFunctions' ) ) {
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

		$classes_available = self::get_available_classes();

		// Get an array of options from the database
		$active_classes = Config::get_available_classes();

		?>
		<div class="uo-admin-header">

			<a href="http://www.uncannyowl.com" target="_blank">
				<img src="<?php echo esc_url( Config::get_admin_media( 'Uncanny-Owl-logo.png' ) ); ?>" />
			</a>

			<hr class="uo-underline">

			<h2><?php _e( 'Thanks for using the Uncanny LearnDash Toolkit!', Config::get_text_domain() ); ?></h2>

			<p><?php
			printf(
				__( 'This plugin makes it easier to incorporate some of the features we see most requested on LearnDash sites. We\'re hard
				at work on a new Knowledge Base for this plugin, but for now, instructions are available at  
				<a href="http://www.uncannyowl.com/uncanny-learndash-toolkit/" target="_blank">http://www.uncannyowl.com/uncanny-learndash-toolkit/</a>.', Config::get_text_domain() ) ,
			esc_url( 'http://www.uncannyowl.com' ) );
			?></p>

			<p><?php
			printf(
				__( 'This is the first Uncanny Owl LearnDash plugin that we\'ve made available in the WordPress repository. We\'ll be
				releasing more over the next few months as well as adding new features to this plugin. Sign up for our mailing list 
				at <a href="http://www.uncannyowl.com/" target="_blank">Uncanny Owl</a> so you don\'t miss any updates!', Config::get_text_domain() ) ,
			esc_url( 'http://www.uncannyowl.com' ) );
			?></p>

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
	public static function create_features( $classes_available, $active_classes ) {

		/* If Magic Quotes are enable we need to stripslashes from ouw $active classes */
		if ( function_exists( 'get_magic_quotes_gpc' ) ) {
			if ( get_magic_quotes_gpc() ) {
				//strip slashes from all keys in array
				$active_classes = Config::stripslashes_deep( $active_classes );
			}
		}

		foreach ( $classes_available as $key => $class ) {

			// skip sample class
			if( 'uncanny_learndash_public\Sample' === $key){
				continue;
			}

			if ( false === $class ) {
				?>
					<div class="uo_feature">
						<div class="uo_feature_title"><?php echo esc_html( $key ) ?></div>
						<div class="uo_feature_description"><?php
							esc_html_e( 'This class is not configured properly. Contact Support for assistance.',  Config::get_text_domain() );
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
						echo '<div><strong>'. esc_html( $dependants_exist ) .'</strong>' . __( ' is needed for this add-on', Config::get_text_domain() ) . '</div>';
					} else {
						?>
						<div class="uo_feature_button_toggle"></div>
						<label class="uo_feature_label" for="<?php echo esc_attr( $class_name ) ?>">
							<?php echo ( __( 'Activate ', Config::get_text_domain() ) . $class['title'] ); ?>
						</label>
						<input class="uo_feature_checkbox" type="checkbox" id="<?php echo esc_attr( $class_name ); ?>" name="uncanny_public_active_classes[<?php echo esc_attr( $class_name ) ?>]" value="<?php echo esc_attr( $class_name ) ?>" <?php
						if (  array_key_exists( $class_name, $active_classes ) ) {
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
