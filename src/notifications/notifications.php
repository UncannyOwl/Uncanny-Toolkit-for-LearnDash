<?php
/**
 * Uncanny_Owl\Notifications
 *
 * @version 1.0.0
 */

namespace Uncanny_Owl;

/**
 * Allow class overwrite.
 */
if ( ! class_exists( 'Uncanny_Owl\Notifications' ) ) {
	/**
	 * Notifications.
	 *
	 * @since 3.9.1.2
	 */
	class Notifications {

		/**
		 * Track if the notifications is loaded
		 *
		 * @var bool
		 */
		public static $loaded = false;

		/**
		 * Source of notifications content.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $source_url = 'https://notifs-cdn.uncannyowl.com/wp-content/notifications.json';

		/**
		 * Option value.
		 *
		 * @since 1.0.0
		 *
		 * @var bool|array
		 */
		public $option = false;

		/**
		 * The name of the option used to store the data.
		 *
		 * @var string
		 */
		public $option_name = 'uncanny_owl_notifications';

		public $license_types = array();

		/**
		 * Class construct.
		 *
		 * @return \Uncanny_Owl\Notifications Chainable.
		 */
		public function __construct() {

			if ( defined( 'UNCANNY_OWL_NOTIFICATIONS_SOURCE_URL' ) ) {

				$this->source_url = UNCANNY_OWL_NOTIFICATIONS_SOURCE_URL;

			}

			add_action( 'uncanny_owl_admin_notifications_update', array( $this, 'update' ) );

			return $this;

		}

		/**
		 * Initialize class.
		 *
		 * @since 1.0.0
		 */
		public function init() {

			do_action( 'uncanny_owl_notifications_before_init', $this );

			$this->hooks();

		}

		/**
		 * Register hooks.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {

			add_action( 'wp_ajax_uncanny_owl_notification_dismiss', array( $this, 'dismiss' ) );

			add_action( 'current_screen', array( $this, 'dispatch_admin_notice' ) );

			// Temporary stylesheet. Can be moved later as a sass file.
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		}

		/**
		 * Shows admin notice on `admin_notices` hook.
		 *
		 * @return void
		 */
		public function dispatch_admin_notice() {

			if ( $this->in_page_notice_showable() ) {

				add_action( 'admin_notices', array( $this, 'show_notifications' ), 999 );

			}

		}

		/**
		 * Retrieves the notification CSS URL path.
		 *
		 * @param $file_name
		 *
		 * @return string
		 */
		public function get_notifs_css( $file_name ) {

			return plugins_url( 'assets/css/' . $file_name, __FILE__ );

		}

		/**
		 * Retrieves the notification JS URL path.
		 *
		 * @param $file_name
		 *
		 * @return string
		 */
		public function get_notifs_js( $file_name ) {

			return plugins_url( 'assets/js/' . $file_name, __FILE__ );

		}

		/**
		 * Enqueues notification assets.
		 *
		 * @return void
		 */
		public function assets() {

			if ( $this->in_page_notice_showable() ) {

				wp_enqueue_style( 'notifications', $this->get_notifs_css( 'notifications.css' ), array(), '1.0.0', false );

				wp_enqueue_script( 'notifications', $this->get_notifs_js( 'notifications.js' ), array(), '1.0.0', false );

				$this->localize_script();

			}

		}

		/**
		 * Return true if notification must be shown on the particular page.
		 *
		 * @return boolean
		 */
		public function in_page_notice_showable() {

			$current_page_requested = filter_input( INPUT_GET, 'page' );

			return $this->is_current_page_matches_given_pattern( $current_page_requested ) // ReGex matching.
			       || $this->is_current_page_learndash_toplevel(); // Manually show to specified learndash pages.

		}

		/**
		 * Retrieves the patterns used to match the current page.
		 *
		 * @return array The patterns with license type.
		 */
		private function get_patterns() {

			return array(
				'uncanny-groups' => '/uncanny(\-learndash)?\-groups(.+)?/',
				'uncanny-ceu'    => '/uncanny\-(ceu(.+)?|(historical\-completions\-credits)|(deficiency\-report))/',
				'uncanny-codes'  => '/uncanny(\-learndash)?\-codes(.+)?/',
				'tin-canny'      => '/((uncanny\-(tincanny|learnDash\-|reporting)(.+)?)|manage\-content|snc\_options)/',
				'toolkit-pro'    => '/uncanny(\-learndash)?\-toolkit(.+)?/',
				'toolkit-free'   => '/uncanny(\-learndash)?\-toolkit(.+)?/',
			);

		}

		/**
		 * Retrieves the current page that matches the license type.
		 *
		 * @return array The matching license types with current pages.
		 */
		private function get_current_page_matching_license_types() {

			$current_page_requested = filter_input( INPUT_GET, 'page' );

			foreach ( $this->get_patterns() as $license_type => $pattern ) {

				preg_match( $pattern, $current_page_requested, $matches );

				if ( ! empty( $matches ) ) {

					$matching_license_types[ $license_type ] = true;

				}
			}

			return ! empty( $matching_license_types ) ? array_keys( $matching_license_types ) : array();

		}

		/**
		 * Determines whether the current page matches the given pattern.
		 *
		 * @param string $page The page to match.
		 *
		 * @return boolean True if current page matches the given pattern. Otherwise, false.
		 * @see $this->get_patterns.
		 *
		 */
		public function is_current_page_matches_given_pattern( $page = '' ) {

			$has_matching_pattern = false;

			foreach ( $this->get_patterns() as $pattern ) {
				if ( null === $page ) {
					continue;
				}
				preg_match( $pattern, $page, $matches );

				if ( ! empty( $matches ) ) {

					$has_matching_pattern = true;

				}
			}

			return $has_matching_pattern;

		}

		/**
		 * Determines whether the current page is toplevel learndash page.
		 *
		 * @return boolean True or false.
		 */
		public function is_current_page_learndash_toplevel() {

			$pattern = '/((learndash(\-|\_)?(lms)?(\-|\_)?(.+)?)|(ld\-exam)|(group_admin_page)|(sfwd\-)?(courses|lessons|topics|topic|quizzes|quiz|essays|groups|assignment|certificate|certificates)?(\-options)?)/';
			if ( filter_has_var( INPUT_GET, 'page' ) && filter_has_var( INPUT_GET, 'post_type' ) ) {
				preg_match( $pattern, filter_input( INPUT_GET, 'page' ), $pages );

				preg_match( $pattern, filter_input( INPUT_GET, 'post_type' ), $post_types );

				return ! empty( $pages[1] ) || ! empty( $post_types[1] );
			}

			return false;
		}

		/**
		 * Localises notifications scripts.
		 *
		 * @return void
		 */
		public function localize_script() {

			wp_localize_script(
				'jquery',
				'ToolkitNotifications',
				array(
					'itemsCount' => absint( count( $this->get_active_notifications() ) ),
					'lastIndex'  => absint( count( $this->get_active_notifications() ) ),
					'ajaxurl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'toolkit-notifications' ),
				)
			);

		}


		/**
		 * Check if user has access and is enabled.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function has_access() {

			$access = false;

			if ( current_user_can( 'manage_options' ) ) {

				$access = true;

			}

			return apply_filters( 'uncanny_owl_admin_notifications_has_access', $access );
		}

		/**
		 * Get option value.
		 *
		 * @param bool $cache Reference property cache if available.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_option( $cache = true ) {

			if ( $this->option && $cache ) {
				return $this->option;
			}

			$option = get_option( $this->option_name, array() );

			$this->option = array(
				'update'    => ! empty( $option['update'] ) ? $option['update'] : 0,
				'events'    => ! empty( $option['events'] ) ? $option['events'] : array(),
				'feed'      => ! empty( $option['feed'] ) ? $option['feed'] : array(),
				'dismissed' => ! empty( $option['dismissed'] ) ? $option['dismissed'] : array(),
			);

			return $this->option;
		}

		/**
		 * Fetch notifications from feed.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function fetch_feed() {

			$res = wp_remote_get( $this->source_url );

			if ( is_wp_error( $res ) ) {

				return array();
			}

			$body = wp_remote_retrieve_body( $res );

			if ( empty( $body ) ) {
				return array();
			}

			return $this->verify( json_decode( $body, true ) );

		}

		/**
		 * Verify notification data before it is saved.
		 *
		 * @param array $notifications Array of notifications items to verify.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function verify( $notifications ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

			$data = array();

			if ( ! is_array( $notifications ) || empty( $notifications ) ) {
				return $data;
			}

			$this->hydrate_license_types();

			$option = $this->get_option();

			foreach ( $notifications as $notification ) {

				// The message and license should never be empty, if they are, ignore.
				if ( empty( $notification['content'] ) || empty( $notification['type'] ) ) {
					continue;
				}

				if ( ! array_intersect( $this->license_types, $notification['type'] ) ) {
					continue;
				}

				// Ignore if notification is not ready to display(based on start time).
				if ( ! empty( $notification['start'] ) && time() < strtotime( $notification['start'] ) ) {
					continue;
				}

				// Ignore if expired.
				if ( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) ) {
					continue;
				}

				// Ignore if notification has already been dismissed.
				$notification_already_dismissed = false;

				if ( is_array( $option['dismissed'] ) && ! empty( $option['dismissed'] ) ) {

					foreach ( $option['dismissed'] as $dismiss_notification ) {

						if ( $notification['id'] === $dismiss_notification['id'] ) {

							$notification_already_dismissed = true;
							break;

						}
					}
				}

				if ( true === $notification_already_dismissed ) {
					continue;
				}

				$data[] = $notification;

			}

			// Filter by over_time.
			// Ignore if notification existed before installing uncanny toolkit.
			// Prevents bombarding the user with notifications after activation.
			foreach ( $data as $index => $loaded_notification ) {

				foreach ( $loaded_notification['type'] as $type ) {

					$over_time = get_option( 'uncanny_owl_over_time_' . $type, array() );

					if (
						! empty( $over_time['installed_date'] ) &&
						! empty( $loaded_notification['start'] ) &&
						$over_time['installed_date'] > strtotime( $loaded_notification['start'] )
					) {
						unset( $data[ $index ] );
					}
				}
			}

			return $data;

		}

		/**
		 * Verify saved notification data for active notifications.
		 *
		 * @param array $notifications Array of notifications items to verify.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function verify_active( $notifications ) {

			if ( ! is_array( $notifications ) || empty( $notifications ) ) {
				return array();
			}

			$this->hydrate_license_types();

			// Remove notifications that are not active, or if the license type not exists
			foreach ( $notifications as $key => $notification ) {

				if (
					( ! empty( $notification['start'] ) && time() < strtotime( $notification['start'] ) ) ||
					( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) ) ||
					( ! empty( $notification['type'] ) && ! array_intersect( $this->license_types, $notification['type'] ) )
				) {
					unset( $notifications[ $key ] );
				}
			}

			return $notifications;

		}

		public function get_license_types() {

			return $this->license_types;

		}

		/**
		 * Get notification data.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get() {

			if ( ! $this->has_access() ) {
				return array();
			}

			$option = $this->get_option();

			// Update notifications using async task.
			if ( empty( $option['update'] ) || time() > $option['update'] - DAY_IN_SECONDS ) {
				if ( false === wp_next_scheduled( 'uncanny_owl_admin_notifications_update' ) ) {
					wp_schedule_single_event( time(), 'uncanny_owl_admin_notifications_update' );
				}
			}

			$events = ! empty( $option['events'] ) ? $this->verify_active( $option['events'] ) : array();
			$feed   = ! empty( $option['feed'] ) ? $this->verify_active( $option['feed'] ) : array();

			$notifications              = array();
			$notifications['active']    = array_merge( $events, $feed );
			$notifications['active']    = $this->get_notifications_with_human_readeable_start_time( $notifications['active'] );
			$notifications['active']    = $this->get_notifications_with_formatted_content( $notifications['active'] );
			$notifications['dismissed'] = ! empty( $option['dismissed'] ) ? $option['dismissed'] : array();
			$notifications['dismissed'] = $this->get_notifications_with_human_readeable_start_time( $notifications['dismissed'] );
			$notifications['dismissed'] = $this->get_notifications_with_formatted_content( $notifications['dismissed'] );

			return $notifications;

		}

		/**
		 * Improve format of the content of notifications before display. By default just runs wpautop.
		 *
		 * @param array $notifications The notifications to be parsed.
		 *
		 * @return mixed
		 */
		public function get_notifications_with_formatted_content( $notifications ) {
			if ( ! is_array( $notifications ) || empty( $notifications ) ) {
				return $notifications;
			}

			foreach ( $notifications as $key => $notification ) {
				if ( ! empty( $notification['content'] ) ) {
					$notifications[ $key ]['content'] = wpautop( $notification['content'] );
					$notifications[ $key ]['content'] = apply_filters( 'uncanny_owl_notification_content_display', $notifications[ $key ]['content'] );
				}
			}

			return $notifications;
		}

		/**
		 * Get notifications start time with human time difference
		 *
		 * @return array $notifications
		 *
		 * @since 7.12.3
		 */
		public function get_notifications_with_human_readeable_start_time( $notifications ) {

			if ( ! is_array( $notifications ) || empty( $notifications ) ) {
				return;
			}

			foreach ( $notifications as $key => $notification ) {

				if ( ! isset( $notification['start'] ) || empty( $notification['start'] ) ) {
					continue;
				}

				// Translators: Readable time to display
				$modified_start_time = sprintf( __( '%1$s ago', 'google-analytics-for-wordpress' ), human_time_diff( strtotime( $notification['start'] ), current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

				$notifications[ $key ]['start'] = $modified_start_time;

			}

			return $notifications;
		}

		/**
		 * Get active notifications.
		 *
		 * @return array $notifications['active'] active notifications
		 *
		 * @since 7.12.3
		 */
		public function get_active_notifications() {

			$notifications = $this->get();

			if ( empty( $notifications ) || empty( $notifications['active'] ) ) {
				return array();
			}

			// Only display notifications on the page where it belongs.
			foreach ( $notifications['active'] as $notification ) {
				if ( ! array_intersect( $this->get_current_page_matching_license_types(), $notification['type'] ) ) {
					continue;
				}
				$filtered_notifications[] = $notification;
			}

			if ( $this->is_current_page_learndash_toplevel() ) {

				return isset( $notifications['active'] ) ? $notifications['active'] : array();

			}

			return isset( $filtered_notifications ) ? $filtered_notifications : array();

		}

		/**
		 * Get dismissed notifications.
		 *
		 * @return array $notifications['dismissed'] dismissed notifications
		 *
		 * @since 7.12.3
		 */
		public function get_dismissed_notifications() {

			$notifications = $this->get();

			return isset( $notifications['dismissed'] ) ? $notifications['dismissed'] : array();

		}

		/**
		 * Get notification count.
		 *
		 * @return int
		 * @since 1.0.0
		 */
		public function get_count() {

			return count( $this->get_active_notifications() );

		}

		/**
		 * Add a manual notification event.
		 *
		 * @param array $notification Notification data.
		 *
		 * @since 1.0.0
		 */
		public function add( $notification ) {

			if ( empty( $notification['id'] ) ) {
				return;
			}

			$option = $this->get_option();

			foreach ( $option['dismissed'] as $item ) {
				if ( $item['id'] === $notification['id'] ) {
					return;
				}
			}

			foreach ( $option['events'] as $item ) {
				if ( $item['id'] === $notification['id'] ) {
					return;
				}
			}

			$notification = $this->verify( array( $notification ) );

			update_option(
				$this->option_name,
				array(
					'update'    => $option['update'],
					'feed'      => $option['feed'],
					'events'    => array_merge( $notification, $option['events'] ),
					'dismissed' => $option['dismissed'],
				),
				false
			);

		}

		/**
		 * Update notification data from feed.
		 *
		 * @param array $option (Optional) Added @since 7.13.2
		 *
		 * @since 1.0.0
		 */
		public function update() {

			$feed = $this->fetch_feed();

			$option = $this->get_option();

			update_option(
				$this->option_name,
				array(
					'update'    => time(),
					'feed'      => $feed,
					'events'    => $option['events'],
					'dismissed' => array_slice( $option['dismissed'], 0, 30 ),
					// Limit dismissed notifications to last 30.
				),
				false
			);

			return true;

		}

		/**
		 * Dismiss notification via AJAX.
		 *
		 * @since 1.0.0
		 */
		public function dismiss() {

			// Run a security check.
			if ( ! wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'toolkit-notifications' ) ) {

				echo 'Invalid nonce.';

				die;

			}

			$notification_id = filter_input( INPUT_GET, 'id' );

			// Check for access and required param.
			if ( ! $this->has_access() || empty( $notification_id ) ) {

				wp_send_json_error();

			}

			$id = sanitize_text_field( wp_unslash( $notification_id ) );

			$option = $this->get_option();

			// Dismiss all notifications and add them to dissmiss array.
			if ( 'all' === $id ) {
				if ( is_array( $option['feed'] ) && ! empty( $option['feed'] ) ) {
					foreach ( $option['feed'] as $key => $notification ) {
						array_unshift( $option['dismissed'], $notification );
						unset( $option['feed'][ $key ] );
					}
				}
				if ( is_array( $option['events'] ) && ! empty( $option['events'] ) ) {
					foreach ( $option['events'] as $key => $notification ) {
						array_unshift( $option['dismissed'], $notification );
						unset( $option['events'][ $key ] );
					}
				}
			}

			$type = is_numeric( $id ) ? 'feed' : 'events';

			// Remove notification and add in dismissed array.
			if ( is_array( $option[ $type ] ) && ! empty( $option[ $type ] ) ) {
				foreach ( $option[ $type ] as $key => $notification ) {
					if ( $notification['id'] == $id ) { // phpcs:ignore WordPress.PHP.StrictComparisons
						// Add notification to dismissed array.
						array_unshift( $option['dismissed'], $notification );
						// Remove notification from feed or events.
						unset( $option[ $type ][ $key ] );
						break;
					}
				}
			}

			update_option( $this->option_name, $option, false );

			wp_send_json_success();

		}

		/**
		 * This generates the markup for the notifications indicator if needed.
		 *
		 * @return string
		 */
		public function get_menu_count() {

			if ( $this->get_count() > 0 ) {
				return '<span class="uncanny-toolkit-menu-notification-indicator update-plugins">' . $this->get_count() . '</span>';
			}

			return '';

		}

		/**
		 * Get the URL for the page where users can see/read notifications.
		 *
		 * @return string
		 */
		public function get_view_url( $scroll_to, $page, $tab = '' ) {

			$disabled = false;

			$url = add_query_arg(
				array(
					'page'                      => $page,
					'uncanny-toolkit-scroll'    => $scroll_to,
					'uncanny-toolkit-highlight' => $scroll_to,
				),
				admin_url( 'admin.php' )
			);

			if ( ! empty( $tab ) ) {
				$url .= '#/' . $tab;
			}

			if ( false !== $disabled ) {
				$url = is_multisite() ? network_admin_url( 'admin.php?page=uncanny_owl_network' ) : admin_url( 'admin.php?page=uncanny_owl_settings' );
			}

			return $url;

		}

		/**
		 * Get the notification sidebar URL for the page where users can see/read notifications.
		 *
		 * @return string
		 */
		public function get_sidebar_url() {

			$disabled = false;

			$url = add_query_arg(
				array(
					'page' => 'uncanny_owl_reports',
					'open' => 'uncanny_owl_notification_sidebar',
				),
				admin_url( 'admin.php' )
			);

			if ( false !== $disabled ) {

				$url = is_multisite() ? network_admin_url( 'admin.php?page=uncanny_owl_network' ) : admin_url( 'admin.php?page=uncanny_owl_settings' );

			}

			return $url;
		}

		/**
		 * Delete the notification options.
		 */
		public function delete_notifications_data() {

			delete_option( $this->option_name );

			// Delete old notices option.
			delete_option( 'uncanny_owl_notices' );

		}

		/**
		 * Shows the notifications.
		 *
		 * @return void
		 */
		public function show_notifications() {

			$notifications = $this->get_active_notifications();

			$dismissed = $this->get_dismissed_notifications();

			require_once trailingslashit( __DIR__ ) . 'views/banner.php';

		}

		/**
		 * Set the license type base on available plugins.
		 */
		public function hydrate_license_types() {

			$types = array();
			if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
				$status = get_option( 'uo_license_status' );
				if ( 'valid' === $status ) {
					$types[] = 'toolkit-pro';
				}
			}

			if ( defined( 'UNCANNY_TOOLKIT_VERSION' ) ) {
				$types[] = 'toolkit-free';
			}

			if ( defined( 'UNCANNY_GROUPS_VERSION' ) ) {
				$status = get_option( 'ulgm_license_status' );
				if ( 'valid' === $status ) {
					$types[] = 'uncanny-groups';
				}
			}

			if ( class_exists( '\uncanny_ceu\InitializePlugin' ) ) {
				$status = get_option( \uncanny_ceu\Utilities::get_prefix() . '_license_status' );
				if ( 'valid' === $status ) {
					$types[] = 'uncanny-ceu';
				}
			}

			if ( defined( 'UNCANNY_REPORTING_VERSION' ) ) {
				$status = get_option( 'uo_reporting_license_status' );
				if ( 'valid' === $status ) {
					$types[] = 'tin-canny';
				}
			}

			if ( defined( 'UNCANNY_LEARNDASH_CODES_VERSION' ) ) {
				$status = get_option( 'uo_codes_license_status' );
				if ( 'valid' === $status ) {
					$types[] = 'uncanny-codes';
				}
			}

			$this->license_types = $types;

		}

		/**
		 * Add UTM parameters to any links.
		 *
		 * @param mixed $url The url of the button.
		 * @param mixed $campaign The title of the button. Urlencoded with spaces replaced by dash.
		 * @param mixed $content The button tex. Urlencoded with spaces replaced by dash.
		 *
		 * @return string The link with utm specified parameters.
		 */
		public function url_add_utm( $url = '', $campaign = '', $content = '' ) {

			if ( empty( $url ) ) {
				return '';
			}

			return add_query_arg(
				array(
					'utm_medium'   => 'notification',
					'utm_campaign' => str_replace( ' ', '-', $campaign ),
					'utm_content'  => str_replace( ' ', '-', $content ),
					'utm_source'   => 'uncanny_owl',
				),
				$url
			);

		}

	}

}
