<?php

namespace uncanny_learndash_toolkit;

/**
 *
 */
class Plugin_API_Override {
	/**
	 * @var string
	 */
	public static $plugin_slug = 'uncanny-automator';

	/**
	 *
	 */
	public function __construct() {
		if ( defined( 'AUTOMATOR_PLUGIN_VERSION' ) ) {
			return;
		}

		add_filter( 'plugins_api_result', array( __CLASS__, 'plugins_api_result' ), PHP_INT_MAX, 3 );
	}

	/**
	 * @param $result
	 * @param $action
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function plugins_api_result( $result, $action, $args ) {
		// If user is searching, bail
		if ( isset( $args->search ) ) {
			return $result;
		}
		// Removing filter to avoid infinity loop
		remove_filter( 'plugins_api_result', array( __CLASS__, 'plugins_api_result' ), PHP_INT_MAX );

		// If plugin slug exists in the list, return
		if ( ! empty( $result->plugins ) && is_object( $result->plugins ) ) {
			foreach ( $result->plugins as $plugin ) {
				if ( is_object( $plugin ) && ! empty( $plugin->slug ) && $plugin->slug === self::$plugin_slug ) {
					return $result;
				}
			} // foreach
		}

		// Get information about Uncanny Automator
		$plugin_info = plugins_api(
			'plugin_information',
			array(
				'slug'   => self::$plugin_slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners'           => true,
					'reviews'           => true,
					'downloaded'        => true,
					'active_installs'   => true,
					'icons'             => true,
					'short_description' => true,
				),
			)
		);

		if ( is_wp_error( $plugin_info ) ) {
			return $result;
		}

		if ( ! is_object( $plugin_info ) ) {
			return $result;
		}

		if ( isset( $result->plugins ) && is_array( $result->plugins ) ) {
			array_unshift( $result->plugins, $plugin_info );
		}

		return $result;
	}
}

new Plugin_API_Override();
