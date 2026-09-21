<?php

namespace uncanny_learndash_toolkit;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class learndashBreadcrumbs
 * @package uncanny_custom_toolkit
 */
class Blocks {

	/*
	 * Plugin prefix
	 * @var string
	 */
	public $prefix = '';

	/*
	 * Plugin version
	 * @var string
	 */
	public $version = '';

	/*
	 * Active Classes
	 * @var string
	 */
	public $active_classes = '';

	/**
	 * Blocks constructor.
	 *
	 * @param string $prefix
	 * @param string $version
	 * @param array $active_classes
	 */
	public function __construct( $prefix = '', $version = '', $active_classes = [] ) {

		$this->prefix         = $prefix;
		$this->version        = $version;
		$this->active_classes = $active_classes;

		// Gutenberg required.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$has_active_blocks = (
			isset( $active_classes['uncanny_learndash_toolkit\Breadcrumbs'] ) ||
			isset( $active_classes['uncanny_learndash_toolkit\LearnDashResume'] ) ||
			isset( $active_classes['uncanny_learndash_toolkit\FrontendLoginPlus'] )
		);

		if ( ! $has_active_blocks ) {
			// Still register the block category filter even if no blocks are active.
			$this->add_block_category_filter();
			return;
		}

		// Register blocks from their block.json metadata (apiVersion 3, dynamic render).
		// Each block is only registered when its feature class is active.
		add_action(
			'init',
			function () {
				if ( isset( $this->active_classes['uncanny_learndash_toolkit\Breadcrumbs'] ) ) {
					register_block_type( __DIR__ . '/dist/toolkit-breadcrumbs' );
				}

				if ( isset( $this->active_classes['uncanny_learndash_toolkit\LearnDashResume'] ) ) {
					register_block_type( __DIR__ . '/dist/toolkit-resume-button' );
				}

				if ( isset( $this->active_classes['uncanny_learndash_toolkit\FrontendLoginPlus'] ) ) {
					register_block_type( __DIR__ . '/dist/toolkit-frontend-login' );
				}
			}
		);

		// Output the ultGutenbergModules inline JS variable so the legacy
		// utilities.js moduleIsActive() helper (if still referenced) keeps working.
		// Attached to 'wp-blocks' which is always enqueued when blocks are present.
		add_action(
			'enqueue_block_editor_assets',
			function () {
				// Get only the Free toolkit blocks that are active.
				$free_blocks = array_values(
					array_map(
						function ( $block ) {
							return str_replace( 'uncanny_learndash_toolkit\\', '', $block );
						},
						array_filter(
							$this->active_classes,
							function ( $block ) {
								return strpos( $block, 'uncanny_learndash_toolkit\\' ) !== false;
							}
						)
					)
				);

				wp_add_inline_script(
					'wp-blocks',
					'var ultGutenbergModules = ' . wp_json_encode( $free_blocks ) . ';',
					'before'
				);

				// Add support for Uncanny Toolkit Pro for LearnDash > 3.4.3
				if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
					if ( version_compare( UNCANNY_TOOLKIT_PRO_VERSION, '3.4.3', '<' ) ) {
						wp_add_inline_script(
							'wp-blocks',
							'var ultpModules = ' . wp_json_encode( array( 'active' => $this->active_classes ) ) . ';',
							'before'
						);
					}
				}
			}
		);

		$this->add_block_category_filter();
	}

	/**
	 * Register the block category filter.
	 */
	private function add_block_category_filter() {
		if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
			add_filter( 'block_categories', array( $this, 'block_categories' ), 10, 2 );
		} else {
			add_filter( 'block_categories_all', array( $this, 'block_categories' ), 10, 2 );
		}
	}

	/**
	 * @param $categories
	 * @param $post
	 *
	 * @return array
	 */
	public function block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'uncanny-learndash-toolkit',
					'title' => __( 'Uncanny Toolkit for LearnDash', 'uncanny-learndash-toolkit' ),
				),
			)
		);
	}
}
