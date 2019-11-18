<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class for managing the plugin
 */
class DisableEmailsPlugin {

	public $options;

	protected $wpmailReplaced = false;

	/**
	 * static method for getting the instance of this singleton object
	 * @return self
	 */
	public static function getInstance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * hook into WordPress
	 */
	private function __construct() {


		$defaults = array(
			'wp_mail'              => 1,
			'wp_mail_from'         => 1,
			'wp_mail_from_name'    => 1,
			'wp_mail_content_type' => 1,
			'wp_mail_charset'      => 1,
			'phpmailer_init'       => 1,
			'buddypress'           => 1,
			'events_manager'       => 1,
		);

		$this->options = get_option( DISABLE_EMAILS_OPTIONS, $defaults );

		add_action( 'admin_notices', array( $this, 'showWarningAlreadyDefined' ) );

		// maybe stop BuddyPress emails too
		if ( ! empty( $this->options['buddypress'] ) ) {
			add_filter( 'bp_email_use_wp_mail', '__return_true' );
		}

		// maybe stop Events Manager emails too
		if ( ! empty( $this->options['events_manager'] ) ) {
			add_filter( 'pre_option_dbem_rsvp_mail_send_method', array( $this, 'forceEventsManagerDisable' ) );
			add_action( 'load-event_page_events-manager-options', array( $this, 'cancelEventsManagerDisable' ) );
		}
	}

	/**
	 * warn that wp_mail() is defined so emails cannot be disabled!
	 */
	public function showWarningAlreadyDefined() {
		if ( ! $this->wpmailReplaced ) {
			include UNCANNY_TOOLKIT_DIR . '/src/templates/warn-already-defined.php';
		}
	}

	/**
	 * force Events Manager to use wp_mail(), so that we can disable it
	 *
	 * @param string|bool $return
	 *
	 * @return string
	 */
	public function forceEventsManagerDisable( $return ) {
		return 'wp_mail';
	}

	/**
	 * cancel Events Manager hook forcing wp_mail() because we're on its settings page
	 */
	public function cancelEventsManagerDisable() {
		remove_filter( 'pre_option_dbem_rsvp_mail_send_method', array( $this, 'forceEventsManagerDisable' ) );
	}

	/**
	 * wp_mail() was successfully replaced, so we can activate disabling emails
	 */
	public static function setActive() {
		include UNCANNY_TOOLKIT_DIR . '/src/includes/class.DisableEmailsPHPMailerMock.php';

		$plugin                 = self::getInstance();
		$plugin->wpmailReplaced = true;
	}

}
