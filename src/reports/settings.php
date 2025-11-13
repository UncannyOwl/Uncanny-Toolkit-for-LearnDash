<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ToolkitReportSetting
 * 
 * A class that will output the reporting setting in the modules page and handle it's saving
 */
class ToolkitReportSetting {

	private static $option_name = 'uncanny_toolkit_usage_report';
	
	/**
	 * is_enabled
	 * 
	 * This static method is used to check if Toolkit reporting is enabled or not.
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		// If the reporting constant is defined, return it.
		if ( defined( 'UNCANNY_TOOLKIT_REPORTING' ) ) {
			return UNCANNY_TOOLKIT_REPORTING;
		}

		// If Toolkit pro plugin is present, return true.
		if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
			return true;
		}

		return self::get_reporting_setting();
	}

	/**
	 * get_reporting_setting
	 *
	 * @return mixed
	 */
	public static function get_reporting_setting() {

		$option_value = get_option( self::$option_name, false );

		return '1' === $option_value;
	}

	public function __construct() {

		if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
			return;
		}

		add_action( 'ult_before_directory_actions', array( $this, 'output_reporting_setting' ) );
		add_action( 'wp_ajax_ult_save_reporting_setting', array( $this, 'save_reporting_setting' ) );
	}

	public function output_reporting_setting() {
		?>
		<?php $this->output_css(); ?>

		<div class="ult-reporting-setting">
			<div class="ult-form-element">
				<div class="uncannyowl-toggle">
					<input type="checkbox"
							class="ult-directory-module__status-toggle"
							value="1"
							<?php echo self::get_reporting_setting() ? 'checked="checked"' : ''; ?>
							aria-describedby="ult-reporting-help-text"
					/>
					<div class="uncannyowl-toggle-slider"></div>
				</div>
				<div class="ult-reporting-text">
					<span><?php echo esc_html__('Help us improve the Uncanny Toolkit by sharing usage statistics', 'uncanny-learndash-toolkit' ); ?></span>
					<small id="ult-reporting-help-text" class="ult-reporting-description"><?php echo esc_html__('Anonymous usage data helps us understand how the plugin is used and improve future versions.', 'uncanny-learndash-toolkit' ); ?></small>
				</div>
			</div>
		</div>
		<?php $this->output_js(); ?>
		<?php

	}
	
	/**
	 * output_css
	 *
	 * @return void
	 */
	public function output_css() {
		// Styles moved to main CSS file
	}
	
	/**
	 * output_js
	 *
	 * @return void
	 */
	public function output_js() {
		?>
		<script>
			jQuery(document).ready(function($) {
				$('.ult-reporting-setting .ult-directory-module__status-toggle').on('change', function() {
					var value = $(this).is(':checked');
					//Send an ajax request to save the setting
					jQuery.ajax({
						
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'POST',
						data: { 
							nonce: '<?php echo wp_create_nonce( 'ult-reporting-setting' ); ?>',
							action: 'ult_save_reporting_setting',
							value: value 
						},
					});
				});
			});
		</script>
		<?php
	}
	
	/**
	 * save_reporting_setting
	 *
	 * @return void
	 */
	public function save_reporting_setting() {

		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['value'] ) ) {
			wp_send_json_error( 'Invalid request' );
		}

		$nonce = filter_var( $_POST['nonce'] );
		$value = filter_var( $_POST['value'], FILTER_VALIDATE_BOOLEAN );

		if ( ! wp_verify_nonce( $nonce, 'ult-reporting-setting' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		update_option( self::$option_name, $value );

		die();
	}
}