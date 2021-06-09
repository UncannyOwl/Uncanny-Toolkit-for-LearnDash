<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class DisableEmails extends Config implements RequiredFunctions {

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( ! defined( 'DISABLE_EMAILS_PLUGIN_ROOT' ) ) {

			add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_emails_disabled' ), 999 );
			if ( ! defined( 'DISABLE_EMAILS_OPTIONS' ) ) {
				define( 'DISABLE_EMAILS_OPTIONS', 'disable_emails' );
			}
			include_once UNCANNY_TOOLKIT_DIR . '/src/includes/class.DisableEmailsPlugin.php';

			DisableEmailsPlugin::getInstance();

			// replace standard WordPress wp_mail() if nobody else has already done it
			if ( ! function_exists( 'wp_mail' ) ) {

				require_once UNCANNY_TOOLKIT_DIR . '/src/includes/wp-mail-func.php';
				DisableEmailsPlugin::setActive();

			}
		}
	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id         = 'disable-emails';
		$class_title       = esc_html__( 'Disable Emails', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/disable-emails/';
		$class_description = esc_html__( 'While this module is active, WordPress and any plugins using native WordPress email functions will be prevented from sending email.', 'uncanny-learndash-toolkit' );
		$category          = 'wordpress';
		$type              = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => false,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {
		return true;
	}

	/**
	 * Add toolbar node for suspending transients
	 *
	 * @access  public
	 * @return  void
	 * @since   1.6
	 */
	public static function admin_bar_emails_disabled( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$args = array(
			'id'     => 'emails-disable',
			'title'  => '<span style="font-weight: bold;font-style: italic;">' . __( 'Emails are Disabled', ' uncanny-learndash-toolkit' ) . '</span>',
			'parent' => 'top-secondary',
		);

		$wp_admin_bar->add_node( $args );

	}
}
