<?php

namespace uncanny_learndash_toolkit;

use Uncanny_Owl\Usage_Reports\Report;
use Uncanny_Owl\Usage_Reports\Reporting_Schedule;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once UNCANNY_TOOLKIT_DIR . '/src/reports/settings.php';
require_once UNCANNY_TOOLKIT_DIR . '/src/reports/toolkit-report.php';
require_once UNCANNY_TOOLKIT_DIR . '/src/reports/module/reporting-schedule.php';

// Initialize the reporting scheduler after all plugins are fully loaded
add_action( 'plugins_loaded', function() {

	new ToolkitReportSetting();

	new Reporting_Schedule(
		'Uncanny Toolkit',
		ToolkitReportSetting::is_enabled(),
		new ToolkitReport()
	);
} );