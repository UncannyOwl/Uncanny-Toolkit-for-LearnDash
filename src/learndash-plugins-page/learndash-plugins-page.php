<?php
/**
 * Uncanny_Owl\LearnDash_Plugins_Page
 *
 * @version 1.0.0
 */

namespace Uncanny_Owl;

/**
 * Allow class overwrite.
 */
if ( ! class_exists( 'Uncanny_Owl\LearnDash_Plugins_Page' ) ) {
	/**
	 * LearnDash_Plugins_Page
	 */
	class LearnDash_Plugins_Page {
		/**
		 * Initialize class.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Returns the URL of an asset
		 *
		 * @param $file_name
		 *
		 * @return string
		 */
		public function get_asset( $file_name ) {
			return plugins_url( '/' . $file_name, __FILE__ );
		}

		/**
		 * Enqueues static assets.
		 *
		 * @return void
		 */
		public function enqueue_assets() {
			// If `page` is not set, return
			if ( ! filter_has_var( INPUT_GET, 'page' ) ) {
				return;
			}
			// Only load if `page` has `uncanny`
			if ( ! preg_match( '/(uncanny)/', filter_input( INPUT_GET, 'page' ) ) ) {
				return;
			}
			wp_enqueue_style(
				'uncannyowl-core',
				$this->get_asset( '/dist/index.css' ),
				array(),
				'1.0.0',
				false
			);

			wp_enqueue_script(
				'uncannyowl-core',
				$this->get_asset( '/dist/index.js' ),
				array(),
				'1.0.0',
				false
			);

		}
	}

}
$plugins = new LearnDash_Plugins_Page();
$plugins->init();
