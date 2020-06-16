<?php
namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Sample
 *
 * Sample Uncanny Toolkit for LearnDash class that can be used to build a module.
 * Module: A module on the wp-admin/admin.php?page=uncanny-toolkit page that is part
 * of the Uncanny Toolkit for LearnDash available for free in the WP plugin repo.
 *
 * @see src/boot.php
 *
 * A module is comprised of a title, description, link to the knowledge base article, settings pop-up modal,
 * icon, and on/off switch.
 *
 * Changes that need to be made:
 * @see get_details()
 * 1. Update class name and file name. File name must match class name for auto loader to find and include the file dynamically.
 * @see src/boot.php -> spl_autoload_register( array( __CLASS__, 'auto_loader' ) );
 *    The class name 'should' match the post_slug of it's associated knowledge base article but this is not always possible.
 *  - ex. SampleClass(class name) => sample-class.php(file name) => /knowledge-base/sample-class/(post_slug)
 *  - class name is camel cased
 *  - file name is lower cased with - between
 * 2. Update class's detail knowledge base article link.
 *  - @param $kb_link
 *  - @link https://www.uncannyowl.com/knowledge-base/sample-class
 * 3. Update class's detail title
 *  - @param $class_title
 * 4. Update class's module short description
 *  - @param $class_description
 * 5. Update class's settings
 *  - @param $class_settings
 * 6. Update class's dependencies
 *  - @param $class_dependents_available
 * 7. Update class's icon
 *  - @param $class_icon
 *
 * @package     uncanny_learndash_toolkit
 * @subpackage  uncanny_learndash_toolkit\Sample
 * @since       1.0.0
 * @since       1.3.3 Added more comments and documentation standards
 *
 */
class Sample extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 *
	 * The construct is only executed when the class is defined as active. If class is in the array retrieved by
	 * get_option( 'uncanny_toolkit_active_classes' )  will create a new class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		/*
		 * Add filters, actions, and/or functions here that need to be executed before plugins are loader.
		 * This is very uncommon. Use run_hooks action in most cases.
		 */

		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
	}

	/*
	 * Initialize actions, filters, and/or custom functions
	 *
	 * Most, if not all, functions should be run after plugins have been loaded. This will give access to modify and/or
	 * override functions for any external plugin or theme. We can also check if a plugin or theme exists before
	 * executing any action, filters, and/or extending classes from it.
	 *
	 * @since 1.0.0
	 */
	public static function plugins_loaded() {

		// Check if the needed plugins and/or theme are available to hook into
		if ( true === self::dependants_exist() ) {

			// Add filters, actions, and/or functions

		}
	}

	/**
	 * Detailed description of module
	 *
	 * This information is only loaded in the admin settings page to create a module which includes an on/off switch
	 * and settings modal pop up that populates module options in the WP DB. The details are retrieve by creating a
	 * reflection class(http://php.net/manual/en/class.reflectionclass.php). The class does not need to be initialized to get the details
	 * @see uncanny_learndash_toolkit/AdminMenu::get_class_details()
	 *
	 * @since 1.0.0
	 *
	 * @return array $class_details
	 */
	public static function get_details() {

		/*
		 * Link to class's associated knowledge base article
		 *
		 * @type Null Will NOT show a link on the plugins settings page
		 * @type String Will show a link icon on the user facing module
		 */
		$kb_link = null;
		$kb_link = 'http://www.uncannyowl.com';

		// Title should match the post_title from Knowledge base article
		$class_title = esc_html__( 'Sample Title', 'uncanny-learndash-toolkit' );

		/*
		 * Description should be adapted from the post_content in the knowledge base article. Exclude any technical
		 * documentation including: shortcodes, attributes, use cases, images, and/or steps. (approx. 25-200 characters)
		 */
		$class_description = esc_html__( 'Sample Description B', 'uncanny-learndash-toolkit' );

		/*
		 * Settings define the inputs that are added to the settings modal pop. If no seetings are needed, false can be
		 * passed and a modal and settings icon like will not be added to the module
		 *
		 * @type Array will create an html output of input within a modal pop up
		 * @type Bool false Will exclude a settings link and modal pop-up
		 */
		$class_settings = self::get_class_settings( $class_title );
		$class_settings = false;

		/*
		 * Check if the class is dependant on another plugin and/or theme
		 * @see dependants_exist();
		 *
		 * @type bool true The class does not depend on any external plugins and/or themes
		 * @type function Checks for dependencies
		 */
		$class_dependents_available = true;
		$class_dependents_available = self::dependants_exist();

		/*
		 * An is mandaotry for each module. There are four different types of icons that are supported:
		 * - Any text string with a maximum of four characters
		 * - Native WP dashicon
		 * - Font Awesome icon (font awesome is enqueued by this plugin)
		 * - Image file loaded from assest/media. Image can be of varying complexity so custom styles should be used
		 *   to get proper size and placement.
		 */
		$class_icon  = '<span class="uo_icon_text">[ /]</span>';
		$class_icon  = '<span class="uo_icon_dashicon dashicons dashicons-admin-users"></span>';
		$class_icon  = '<i class="uo_icon_fa fa fa-calendar"></i>';
		$icon_styles = 'width: 40px;  padding-top: 5px; padding-left: 9px;';
		$class_icon  = '<img style="' . $icon_styles . '" src="' . self::get_admin_media( 'gravity-forms-icon.png' ) . '" />';
		$type        = 'free';
		$tags        = 'learndash'; //learndash | general | user-handling

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'tags'             => $tags,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => $class_dependents_available,
			'settings'         => $class_settings,
			'icon'             => $class_icon,
		);

		return $class_details;

	}

	/*
	 * Checks if the class is dependant on another variable, function, plugin and/or theme
	 *
	 * If the dependency does not exists then the on/off switch on the module is replace with a message.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean || string
	 */
	public static function dependants_exist() {

		/*
		 * When checking for dependencies you should first check if the plugin or theme has a 'loaded hook' like gravity
		 * form below. If they do not offer an action to hook. Look for the first obvious global function or variable.
		 */

		// ex. Checks if LearnDash Plugin exists
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		//ex. Checks if gravity forms plugin exists
		if ( ! has_action( 'gform_loaded' ) ) {
			return 'Plugin: Gravity Forms';
		}

		// Return true dependency is available
		return true;
	}


	/**
	 * Create an array that defines the html output of module modal settings inputs
	 *
	 * @since 1.0.0
	 *
	 * @param $class_title
	 *
	 * @return string HTML output
	 */
	public static function get_class_settings( $class_title ) {

		// ex. Get pages to populate a drop down steps 1-5
		// 1. setup arguments - @link https://codex.wordpress.org/Function_Reference/get_pages#Parameters
		$args = array(
			'sort_order'  => 'asc',
			'sort_column' => 'post_title',
			'post_type'   => 'page',
			'post_status' => 'publish',
		);
		// 2. Get pages
		$pages = get_pages( $args );
		// 3. Create empty array to store select element values
		$drop_down = array();
		// 4. The first options elements should have a 0 value if no setting is need
		array_push( $drop_down, array( 'value' => 0, 'text' => '- Select Page -' ) );
		//  5. Push page ids and page titles into array of values
		foreach ( $pages as $page ) {
			array_push( $drop_down, array( 'value' => $page->ID, 'text' => $page->post_title ) );
		}

		/*
		 * Settings are simplified as an array of data that is sent to a html generator script. The settings array
		 * accepts one text element and five input types.
		 */
		$settings = array(

			// Outputs a string contained in a div element
			array(
				'type'       => 'html',
				'class'      => 'uo-additional-information',
				'inner_html' => __( '<div>Some Extra Information for the user</div>', 'uncanny-learndash-toolkit' ),
			),

			// Check Box input type
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Settings A', 'uncanny-learndash-toolkit' ),
				'option_name' => 'a',
			),

			// Text type
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Settings B', 'uncanny-learndash-toolkit' ),
				'option_name' => 'b',
			),

			// Radio type
			array(
				'type'       => 'radio',
				'label'      => esc_html__( 'Settings Gender', 'uncanny-learndash-toolkit' ),
				'radio_name' => 'uo_gender',
				'radios'     => array(
					array( 'value' => 'male', 'text' => 'Male' ),
					array( 'value' => 'female', 'text' => 'Female' ),
					array( 'value' => 'other', 'text' => 'Other' ),
				),
			),

			// Select input type
			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Settings Car', 'uncanny-learndash-toolkit' ),
				'select_name' => 'car',
				'options'     => array(
					array( 'value' => 'volvo', 'text' => 'Volvo' ),
					array( 'value' => 'saab', 'text' => 'Saab' ),
					array( 'value' => 'ford', 'text' => 'Ford' ),
				),
			),

			// Select input type with drop down generated by pages example above
			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Login Page', 'uncanny-learndash-toolkit' ),
				'select_name' => 'login_page',
				'options'     => $drop_down,
			),

			// Specialized text input type that loads the native wp color picker
			array(
				'type'        => 'text',
				'label'       => 'Accent Text Color',
				'class'       => 'uo-color-picker',
				'option_name' => 'accent_text_color',
			),

		);

		/*
		 * Build html output string
		 *
		 * @see Config:settings_output()
		 */
		$html = self::settings_output( array(
			'class'   => __CLASS__,
			'title'   => $class_title,
			'options' => $settings,
		) );

		return $html;
	}
}
