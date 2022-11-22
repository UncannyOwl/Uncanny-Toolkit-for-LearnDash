<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MenuItemVisibility
 * @package uncanny_custom_toolkit
 */
class MenuItemVisibility extends Config implements RequiredFunctions {


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {
			// Include custom walker class
			add_action( 'admin_init', array( __CLASS__, 'include_custom_walker' ) );

			// Replace the admin walker menu
			add_filter( 'wp_edit_nav_menu_walker', array( __CLASS__, 'edit_nav_menu_walker' ), 11 );

			// Add new fields to the custom admin walker menu
			//add_action( 'wp_nav_menu_item_uo_fields', array( __CLASS__, 'custom_fields' ), 10, 4 );

			// add new fields via hook
			add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'custom_fields' ), 10, 4 );

			// Save new custom admin walker menu item meta fields
			add_action( 'wp_update_nav_menu_item', array( __CLASS__, 'nav_update' ), 10, 2 );

			// Add meta to menu items
			add_filter( 'wp_setup_nav_menu_item', array( __CLASS__, 'setup_nav_item' ) );

			// Exclude items form menu
			add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'exclude_menu_items' ) );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'menu-item-visibility';
		$class_title       = esc_html__( 'Menu Item Visibility', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/menu-item-visibility/';
		$class_description = esc_html__( 'Toggles the visibility of menu items based on whether or not users are signed in to the site.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-eye-slash"></i>';
		$category          = 'wordpress';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link,
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => false,
			'icon'             => $class_icon,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {

		if ( function_exists( 'Nav_Menu_Roles' ) ) {
			return 'This module cannot be active while Nav Menu Roles is in use. Please use Nav Menu Roles instead.';
		}

		// Return true if no dependency or dependency is available
		return true;
	}

	/**
	 * Include the custom admin walker
	 */
	public static function include_custom_walker() {
		include_once( self::get_include( 'custom-walker-nav-menu.php' ) );
	}

	/**
	 * Override the Admin Menu Walker
	 */
	public static function edit_nav_menu_walker( $walker ) {
		return 'CustomWalkerNavMenu';
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $item_id
	 * @param $item
	 * @param $depth
	 * @param $args
	 */
	public static function custom_fields( $item_id, $item, $depth, $args ) {

		/* Get the roles saved for the post. */
		$roles = get_post_meta( $item->ID, '_nav_menu_role', true );

		// by default nothing is checked (will match "everyone" radio)
		$logged_in_out = '';

		// specific roles are saved as an array, so "in" or an array equals "in" is checked
		if ( 'in' == $roles ) {
			$logged_in_out = 'in';
		} elseif ( 'out' == $roles ) {
			$logged_in_out = 'out';
		}

		?>

        <input type="hidden" name="nav-menu-role-nonce"
               value="<?php echo wp_create_nonce( 'nav-menu-nonce-name' ); ?>"/>

        <div class="field-nav_menu_role nav_menu_logged_in_out_field description-wide" style="margin: 5px 0;">
            <span class="description"><?php echo esc_html__( 'Display Mode', 'uncanny-learndash-toolkit' ); ?></span>
            <br/>

            <input type="hidden" class="nav-menu-id" value="<?php echo $item->ID; ?>"/>

            <div class="logged-input-holder" style="float: left; width: 35%;">
                <input type="radio" class="nav-menu-logged-in-out"
                       name="nav-menu-logged-in-out[<?php echo $item->ID; ?>]"
                       id="nav_menu_logged_in-for-<?php echo $item->ID; ?>" <?php checked( 'in', $logged_in_out ); ?>
                       value="in"/>
                <label for="nav_menu_logged_in-for-<?php echo $item->ID; ?>">
					<?php echo esc_html__( 'Logged In Users', 'uncanny-learndash-toolkit' ); ?>
                </label>
            </div>

            <div class="logged-input-holder" style="float: left; width: 35%;">
                <input type="radio" class="nav-menu-logged-in-out"
                       name="nav-menu-logged-in-out[<?php echo $item->ID; ?>]"
                       id="nav_menu_logged_out-for-<?php echo $item->ID; ?>" <?php checked( 'out', $logged_in_out ); ?>
                       value="out"/>
                <label for="nav_menu_logged_out-for-<?php echo $item->ID; ?>">
					<?php echo esc_html__( 'Logged Out Users', 'uncanny-learndash-toolkit' ); ?>
                </label>
            </div>

            <div class="logged-input-holder" style="float: left; width: 30%;">
                <input type="radio" class="nav-menu-logged-in-out"
                       name="nav-menu-logged-in-out[<?php echo $item->ID; ?>]"
                       id="nav_menu_by_role-for-<?php echo $item->ID; ?>" <?php checked( '', $logged_in_out ); ?>
                       value=""/>
                <label for="nav_menu_by_role-for-<?php echo $item->ID; ?>">
					<?php echo esc_html__( 'Everyone', 'uncanny-learndash-toolkit' ); ?>
                </label>
            </div>

        </div>

		<?php
	}


	/**
	 * Save the roles as menu item meta
	 *
	 * @param $menu_id
	 * @param $menu_item_db_id
	 *
	 * @return string
	 * @since 1.0
	 */
	public static function nav_update( $menu_id, $menu_item_db_id ) {

		$saved_data = false;

		if ( isset( $_POST['nav-menu-logged-in-out'][ $menu_item_db_id ] ) && in_array( $_POST['nav-menu-logged-in-out'][ $menu_item_db_id ], array(
				'in',
				'out'
			) )
		) {
			$saved_data = $_POST['nav-menu-logged-in-out'][ $menu_item_db_id ];
		}

		if ( $saved_data ) {
			update_post_meta( $menu_item_db_id, '_nav_menu_role', $saved_data );
		} else {
			delete_post_meta( $menu_item_db_id, '_nav_menu_role' );
		}

	}

	/**
	 * Adds value of new field to $item object
	 * is be passed to Walker_Nav_Menu_Edit_Custom
	 * @since 1.0
	 *
	 * @param $menu_item
	 *
	 * @return null
	 */
	public static function setup_nav_item( $menu_item ) {
		
		if( ! isset( $menu_item->ID ) ){
			return $menu_item;
		}

		$roles = get_post_meta( $menu_item->ID, '_nav_menu_role', true );

		if ( ! empty( $roles ) ) {
			$menu_item->roles = $roles;
		}

		return $menu_item;
	}

	/**
	 * Exclude menu items via wp_get_nav_menu_items filter
	 *
	 * @param $items
	 *
	 * @return
	 */
	public static function exclude_menu_items( $items ) {

		if ( is_admin() ) {
			return $items;
		}

		$hide_children_of = array();

		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {

			$visible = true;

			// hide any item that is the child of a hidden item
			if ( in_array( $item->menu_item_parent, $hide_children_of ) ) {
				$visible            = false;
				$hide_children_of[] = $item->ID; // for nested menus
			}

			// check any item that has NMR roles set
			if ( $visible && isset( $item->roles ) ) {

				// check all logged in, all logged out, or role
				switch ( $item->roles ) {
					case 'in' :
						$visible = is_user_logged_in() ? true : false;
						break;
					case 'out' :
						$visible = ! is_user_logged_in() ? true : false;
						break;
					default:
						$visible = false;
						if ( is_array( $item->roles ) && ! empty( $item->roles ) ) {
							foreach ( $item->roles as $role ) {
								if ( current_user_can( $role ) ) {
									$visible = true;
								}
							}
						}

						break;
				}
			}

			// add filter to work with plugins that don't use traditional roles
			$visible = apply_filters( 'nav_menu_roles_item_visibility', $visible, $item );

			// unset non-visible item
			if ( ! $visible ) {
				$hide_children_of[] = $item->ID; // store ID of item
				unset( $items[ $key ] );
			}
		}

		return $items;
	}
}
