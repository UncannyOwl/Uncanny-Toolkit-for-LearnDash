<?php

namespace uncanny_learndash_toolkit;

use UncannyOwl\Toolkit\UsageReports\Reporting_Schedule;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once UNCANNY_TOOLKIT_DIR . '/vendor/autoload.php';

// Initialize the reporting scheduler after all plugins are fully loaded
add_action( 'plugins_loaded', function() {

	// Load the plugin-specific report classes
	require_once UNCANNY_TOOLKIT_DIR . '/src/reports/settings.php';
	require_once UNCANNY_TOOLKIT_DIR . '/src/reports/toolkit-report.php';

	new ToolkitReportSetting();

	new Reporting_Schedule(
		'Uncanny Toolkit',
		ToolkitReportSetting::is_enabled(),
		new ToolkitReport()
	);
} );
