<?php

namespace Uncanny_Owl\Usage_Reports;

/**
 * Report
 * 
 * A class that will populate the report and send it to the server.
 */
abstract class Report {

	/**
	 * send
	 * 
	 * This is the only public interface of a report object.
	 *
	 * @return mixed
	 */
	public final function send() {
		$this->prepare_report();
		return $this->make_request();
	}

	/**
	 * @var array
	 */
	protected $report = array();
    
	/**
	 * get_wp_info
	 *
	 * @return void
	 */
	protected function get_wp_info() {

		$wp['multisite']       	= is_multisite();
		$wp['sites']           	= $wp['multisite'] ? $this->sites_count() : 1;
		$wp['user_count']      	= $this->get_user_count();
		$wp['timezone_offset'] 	= $this->get_timezone_string();
		$wp['locale']          	= get_locale();
		$wp['wp_debug_mode']   	= defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp['wp_debug_log']    	= defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$wp['wp_debug_display'] = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		$wp['wp_version'] 			= get_bloginfo( 'version' );
		$wp['wp_memory_limit'] 	= ini_get( 'memory_limit' );
		$wp['wp_cron_enabled'] 	= defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

		return $wp;
	}

	/**
	 * get_user_count
	 *
	 * @return void
	 */
	protected function get_user_count() {
		$usercount = count_users();

		return isset( $usercount['total_users'] ) ? $usercount['total_users'] : esc_html__( 'Not set', 'uncanny-automator' );
	}

	/**
	 * get_theme_info
	 *
	 * @return void
	 */
	protected function get_theme_info() {
		$active_theme = wp_get_theme();

		$theme['name']    = $active_theme->get('Name');
		$theme['version'] = $active_theme->get('Version');

		return $theme;
	}

	/**
	 * sites_count
	 *
	 * @return void
	 */
	protected function sites_count() {

		$blog_count = 'Not set';

		if ( function_exists( 'get_blog_count' ) ) {

			$blog_count = get_blog_count();

		}

		return $blog_count;
	}

	/**
	 * get_timezone_string
	 *
	 * @return string
	 */
	protected function get_timezone_string() {

		if ( ! function_exists( 'wp_timezone_string' ) ) {
			return '';
		}

		return wp_timezone_string();
	}
	
	/**
	 * get_server_info
	 *
	 * @return array
	 */
	protected function get_server_info() {

		$info = array(
			'external_object_cache' => defined( 'WP_CACHE' ) && WP_CACHE,
			'php_version' => phpversion(),
			'php_post_max_size' => ini_get( 'post_max_size' ),
			'php_max_execution_time' => ini_get( 'max_execution_time' ),
			'php_max_input_vars' => ini_get( 'max_input_vars' ),
			'php_memory_limit' => ini_get( 'memory_limit' ),
			'curl_version' => curl_version(),
			'max_upload_size' => ini_get( 'upload_max_filesize' ),
			'mysql_version' => $this->get_mysql_version(),
			'mbstring_enabled' => extension_loaded( 'mbstring' )
		);

		return $info;
	}

	/**
	 * Get list of active plugins with their details.
	 *
	 * @since 3.6.0
	 * @return array Array of active plugins with their details.
	 */
	protected function get_active_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins' );
		$formatted_plugins = array();

		foreach ( $active_plugins as $plugin ) {

			if ( ! isset( $all_plugins[ $plugin ] ) ) {
				continue;
			}

			$formatted_plugins[] = $all_plugins[ $plugin ];
		}

		return $formatted_plugins;
	}

	/**
	 * get_unique_site_hash
	 *
	 * Generate a unique site hash. We can't send the site URL without owner's consent due to GDPR.
	 *
	 * @return void
	 */
	protected function get_unique_site_hash() {
		$site_url                  = get_site_url();
		$site_hash                 = md5( $site_url );
		return $site_hash;
	}
	
	/**
	 * get_mysql_version
	 *
	 * @return string
	 */
	protected function get_mysql_version() {
		global $wpdb;
		return $wpdb->db_version();
	}

	/**
	 * prepare_report
	 *
	 * @return void
	 */
	protected function prepare_report() {

		$this->report['site_hash'] = $this->get_unique_site_hash();
		$this->report['wp'] = $this->get_wp_info();
		$this->report['theme'] = $this->get_theme_info();
		$this->report['server'] = $this->get_server_info();
		$this->report['active_plugins'] = $this->get_active_plugins();
	}

	/**
	 * make_request
	 * 
	 * Override this method to define how and where the report will be sent
	 *
	 * @return void
	 */ 	
	abstract protected function make_request();
}

