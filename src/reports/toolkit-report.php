<?php

namespace uncanny_learndash_toolkit;

use UncannyOwl\Toolkit\UsageReports\Report;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ToolkitReport
 * 
 * A class that will prepare the report and send it to the server.
 */
class ToolkitReport extends Report {

	const PLUGIN_SLUG = 'uncanny-toolkit';
	const PLUGIN_NAME = 'Uncanny Toolkit';
	
	/**
	 * get_license_details
	 *
	 * @return void
	 */
	private function populate_license_details() {

		$this->report['pro_installed'] = defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ? true : false;
		$this->report['pro_installed_binary'] = defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ? 1 : 0;
		$this->report['pro_license_key'] = get_option( 'uo_license_key', '' );
		$this->report['pro_license_status'] = get_option( 'uo_license_status', 'not_connected' );

		if ( ! $this->report['pro_installed'] ) {
			$this->report['license_type'] = 'free';
		} else {
			$this->report['license_type'] = 'pro_' . $this->report['pro_license_status'];
		}

	}
	
	/**
	 * get_base_plugins_constants
	 *
	 * @return void
	 */
	private function populate_base_plugins_constants() {
		$this->report['base_plugins_constants'] = array(
			'toolkit_free_version' => UNCANNY_TOOLKIT_VERSION,
			'toolkit_pro_version' => defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ? UNCANNY_TOOLKIT_PRO_VERSION : 'Not installed',
			'ld_version' => defined( 'LEARNDASH_VERSION' ) ? LEARNDASH_VERSION : 'Not installed',
			'ld_db_version' => defined( 'LEARNDASH_SETTINGS_DB_VERSION' ) ? LEARNDASH_SETTINGS_DB_VERSION : 'Not installed',
			'ld_script_debug' => defined( 'LEARNDASH_SCRIPT_DEBUG' ) ? LEARNDASH_SCRIPT_DEBUG : 'Not set',
		);
	}
	
	/**
	 * get_modules
	 *
	 * @return void
	 */
	private function populate_modules() {

		$toolkit_modules = AdminMenu::get_modules();

		foreach ( $toolkit_modules as $module ) {
			$this->process_module( $module );
		}
	}
	
	/**
	 * process_module
	 *
	 * @param  mixed $module
	 * @return void
	 */
	private function process_module( $module ) {

		$module_data = $this->filter_module_data( $module );

		$this->report['toolkit_modules'][] = $module_data;

		if ( 'pro' === $module_data['version'] ) {
			$this->report['toolkit_pro_modules'][] = $module_data;
		} else {
			$this->report['toolkit_free_modules'][] = $module_data;
		}
	}
	
	/**
	 * filter_module_data
	 *
	 * @param  mixed $module
	 * @return array
	 */
	private function filter_module_data( $module ) {

		$module_data = array(
			'id' => $module['id'],
			'title' => $module['title'],
			'version' => $module['version'],
			'is_active' => $module['is_active'],
			'is_active_binary' => true === $module['is_active'] ? 1 : 0,
			'can_use' => $module['can_use'],
			'can_use_binary' => true === $module['can_use'] ? 1 : 0
		);

		return $module_data;
	}

	/**
	 * make_request
	 * 
	 * Send the report data to the server
	 * 
	 * @return mixed
	 */
	public function make_request() {

		$this->populate_license_details();
		$this->populate_base_plugins_constants();
		$this->populate_modules();
		
		$json_report = json_encode( $this->report );
		$timestamp = time(); // Current Unix timestamp
		
		// Create signature using timestamp and nonce
		$signature = hash_hmac( 'sha256', $timestamp, UNCANNY_API_KEY );
		
		return wp_safe_remote_post( UNCANNY_API_URL . 'reports', array(
			'body' => array(
				'action' => 'save',
				'signature' => $signature,
				'timestamp' => $timestamp,
				'report' => $json_report
			)
		));
	}
}