<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}


class LearndashGroupUserProfile {

	static $instance;

	/**
	 * Call this method to get singleton
	 * @return LearndashGroupUserProfile $instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * class constructor
	 *
	 */
	private function __construct() {
		// Add learndash groups field when the user is view there own profile
		add_action( 'show_user_profile', array( __CLASS__, 'show_users_groups_profile_fields' ) );
		// Add learndash groups field when the user is view another users profile
		add_action( 'edit_user_profile', array( __CLASS__, 'show_users_groups_profile_fields' ) );
	}

	/**
	 * @param object $user The WP_User object of the user being edited/viewed
	 *
	 * @return string
	 */
	public static function show_users_groups_profile_fields( $user ) {

		global $wpdb;

		// title of section
		$section_title = __( 'LearnDash Groups', Config::get_text_domain() );
		// User ID of user being viewed/edited
		$user_ID = $user->ID;
		// Meta key stored by learndash of user's groups
		$meta_key = '%learndash_group_users_%';


		// Query to get all the users' groups
		$group_query = $wpdb->prepare(
			" SELECT `meta_value` FROM %s WHERE `user_id` = %d AND meta_key LIKE %s",
			$wpdb->usermeta,
			$user_ID,
			$meta_key
		);

		// Collect data from the database
		$user_groups = $wpdb->get_results( $group_query, ARRAY_N );

		// If the user is part of at least one group... lets create the LearnDash Group Section
		if( !empty( $user_groups ) ){
			// Happy Filtering!
			$section_title = apply_filters( 'learndash_users_groups_profile_title', $section_title );

			// Loop through all the user's group ids and collect the title and link
			$list_groups = '';
			foreach( $user_groups as $group_ID ){
				$group_permalink = site_url().'/wp-admin/post.php?post='.(int)$group_ID[0].'&action=edit';
				$group_title = get_the_title( (int)$group_ID[0] ); // Get the group title
				$list_groups .= sprintf( '<li><a href="%s">%s</a></li>', $group_permalink, $group_title);// list of all the groups
			}

			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<h3><?php echo $section_title; ?></h3>
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