<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class LearndashGroupUserProfile
 * @package uncanny_custom_toolkit
 */
class LearndashGroupUserProfile extends Config implements RequiredFunctions {

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

			// Add learndash groups field when the user is view there own profile
			add_action( 'show_user_profile', array( __CLASS__, 'show_users_groups_profile_fields' ) );
			// Add learndash groups field when the user is view another users profile
			add_action( 'edit_user_profile', array( __CLASS__, 'show_users_groups_profile_fields' ) );
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'groups-in-user-profiles';
		$class_title       = esc_html__( 'Groups in User Profiles', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/learndash-groups-user-profiles/';
		$class_description = esc_html__( 'Displays a user\'s LearnDash Group memberships in the user profile.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-users"></i>';
		$category          = 'learndash';
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
	 * return boolean || string Return either true or name of function or plugin
	 */
	public static function dependants_exist() {
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		return true;
	}

	/**
	 * @param object $user The WP_User object of the user being edited/viewed
	 *
	 * @return string
	 */
	public static function show_users_groups_profile_fields( $user ) {

		global $wpdb;

		// title of section
		$section_title = esc_html__( 'LearnDash Groups', 'uncanny-learndash-toolkit' );
		// User ID of user being viewed/edited
		$user_ID = $user->ID;

		$groups = learndash_get_users_group_ids( $user_ID );

		// If the user is part of at least one group... lets create the LearnDash Group Section
		if ( ! empty( $groups ) ) {
			// Happy Filtering!
			$section_title = apply_filters( 'learndash_users_groups_profile_title', $section_title );

			// Loop through all the user's group ids and collect the title and link
			$list_groups = '';
			foreach ( $groups as $group_ID ) {
				if ( ! empty( $group_ID ) && is_numeric( $group_ID ) ) {
					$group_permalink = add_query_arg(
						array(
							'post'   => (int) $group_ID,
							'action' => 'edit',
						), admin_url( 'post.php' )
					);
					$group_title     = get_the_title( (int) $group_ID ); // Get the group title
					$list_groups .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $group_permalink ), esc_html( $group_title ) );// list of all the groups
				}
			}

			?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <h3><?php echo esc_html( $section_title ); ?></h3>
                    </th>
                    <td>
                        <ol>
							<?php echo $list_groups; ?>
                        </ol>
                    </td>
                </tr>
                </tbody>
            </table>
			<?php
		}
	}
}
