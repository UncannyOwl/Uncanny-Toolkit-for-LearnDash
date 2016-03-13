<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

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
	public static function run_frontend_hooks(){

		if ( true === self::dependants_exist() ) {
			/* Hide admin bar on frontend for the user role */
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
		$class_title = __( 'LearnDash Groups in User Profiles', self::get_text_domain() );
		$kb_link = null;
		$class_description = __( 'Displays a user\'s LearnDash Group memberships in the user profile.', self::get_text_domain() );
		$class_icon = '<i class="uo_icon_fa fa fa-users"></i>';

		return array(
				'title'            => $class_title,
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
		$section_title = __( 'LearnDash Groups', self::get_text_domain() );
		// User ID of user being viewed/edited
		$user_ID = $user->ID;
		// Meta key stored by learndash of user's groups
		$meta_key = '%learndash_group_users_%';

		// Query to get all the users' groups
		$group_query = $wpdb->prepare(
			" SELECT `meta_value` FROM $wpdb->usermeta WHERE `user_id` = %d AND meta_key LIKE %s",
			$user_ID,
			$meta_key
		);

		// Collect data from the database
		$user_groups = $wpdb->get_results( $group_query, ARRAY_N );

		// If the user is part of at least one group... lets create the LearnDash Group Section
		if ( ! empty( $user_groups ) ) {
			// Happy Filtering!
			$section_title = apply_filters( 'learndash_users_groups_profile_title', $section_title );

			// Loop through all the user's group ids and collect the title and link
			$list_groups = '';
			foreach ( $user_groups as $group_ID ) {
				$group_permalink = add_query_arg(
					array(
						'post' => (int) $group_ID[0],
						'action' => 'edit',
					), admin_url( 'post.php' )
				);
				$group_title     = get_the_title( (int) $group_ID[0] ); // Get the group title
				$list_groups .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $group_permalink ), esc_html( $group_title ) );// list of all the groups
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
