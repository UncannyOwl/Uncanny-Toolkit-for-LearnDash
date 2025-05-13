<?php

namespace Uncanny_Owl\Usage_Reports;

/**
 * Class Usage_Reports.
 * 
 * A class that will schedule the report and send it to the server once a week.
 * The class will also unschedule the report if the reporting is disabled.
 *
 * @package Uncanny_Automator
 */
class Reporting_Schedule {

	/**
	 * @var string
	 */
	private static $plugin_slug;

	/**
	 * @var bool
	 */
	private static $reporting_enabled;

	/**
	 * @var string
	 */
	private static $shedule_name;

	/**
	 * @var
	 */
	public $system_report;

	/**
	 * @var
	 */
	public $recipes_data;

	/**
	 * @var
	 */
	public $report;

    /**
     * @var Report
     */
    public static $report_object;

	/**
	 * @var bool
	 */
	public static $test_mode;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct( $plugin_slug, $reporting_enabled, $report_object, $test_mode = false ) {

		self::$test_mode = $test_mode;
		self::$reporting_enabled = $reporting_enabled;
        self::$report_object = $report_object;
        self::$plugin_slug = $this->generate_slug( $plugin_slug );
        self::$shedule_name = self::$plugin_slug . '_reporting';
		$this->register_hooks();
	}
    
    /**
     * register_hooks
     *
     * @return void
     */
    public function register_hooks() {

		add_action( 'init', array( $this, 'maybe_schedule_report' ) );
        add_action( self::$shedule_name, array( $this, 'maybe_send_report' ) );
    }
    
    /**
     * generate_slug
     *
     * @param  string $slug
     * @return string
     */
    private function generate_slug( $slug ) {

		// Convert the slug to lowercase.
        $slug = strtolower( $slug );

		// Replace spaces, dashes, slashes, backslashes, and dots with underscores.
        $replace = array( ' ', '-', '/', '\\', '.' );
        $slug = str_replace( $replace, '_', $slug );

        return $slug;
    }

	/**
	 * maybe_schedule_report
	 *
	 * @return void
	 */
	public function maybe_schedule_report() {

		// If reporting is disabled, unschedule the report.
		if ( ! self::$reporting_enabled ) {
	
			$this->unschedule_report();

			return;
		}

		// Otherwise, schedule the report.
		$this->schedule_report();
	}

	/**
	 * schedule_report
	 *
	 * @return void
	 */
	public function schedule_report() {

        // If already scheduled, bail.
		if ( wp_next_scheduled( self::$shedule_name ) ) {
            return;
        }

		// Otherwise, schedule the report.
		$first_report_timestamp = $this->get_random_timestamp();
		$reporting_interval = 'weekly';

		if ( self::$test_mode ) {
			$first_report_timestamp = time() + 60;
			$reporting_interval = 'hourly';
		}
        
		wp_schedule_event( $first_report_timestamp, $reporting_interval, self::$shedule_name );
	}

	/**
	 * unschedule_report
	 *
	 * @return void
	 */
	public function unschedule_report() {

		$timestamp = wp_next_scheduled( self::$shedule_name );

		if ( false === $timestamp ) {
			return;
		}

		wp_unschedule_event( $timestamp, self::$shedule_name );
	}

	/**
	 * get_random_timestamp
	 *
	 * Will generate a random timestamp within the current week.
	 *
	 * @return int timestamp
	 */
	public function get_random_timestamp() {

		$last_monday = strtotime( 'last Monday' );
		$next_monday = strtotime( 'next Monday' );

		return wp_rand( $last_monday, $next_monday );
	}

	/**
	 * Method maybe_send_report
	 *
	 * @return bool
	 */
	public function maybe_send_report() {

		if ( ! self::$reporting_enabled ) {
			return false;
		}

		return $this->send_report();
	}

	/**
	 * get_unique_site_hash
	 *
	 * Generate a unique site hash. We can't send the site URL without owner's consent due to GDPR.
	 *
	 * @return void
	 */
	public function get_unique_site_hash() {
		$site_url                  = get_site_url();
		$site_hash                 = md5( $site_url );
		$this->report['site_hash'] = $site_hash;
	}

	/**
	 * send_report
	 *
	 * @return void
	 */
	public function send_report() {
        self::$report_object->send();
	}
}
