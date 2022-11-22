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

		$add_block_scripts = false;

		// Check if Gutenberg exists
		if ( function_exists( 'register_block_type' ) ) {

			if (
				isset( $active_classes['uncanny_learndash_toolkit\Breadcrumbs'] ) ||
				isset( $active_classes['uncanny_learndash_toolkit\LearnDashResume'] ) ||
				isset( $active_classes['uncanny_learndash_toolkit\FrontendLoginPlus'] )
			) {
				$add_block_scripts = true;
			}
			// Register Blocks
			add_action( 'init', function () {
				if ( isset( $this->active_classes['uncanny_learndash_toolkit\Breadcrumbs'] ) ) {
					require_once( dirname( __FILE__ ) . '/src/toolkit-breadcrumbs/block.php' );
				}

				if ( isset( $this->active_classes['uncanny_learndash_toolkit\LearnDashResume'] ) ) {
					require_once( dirname( __FILE__ ) . '/src/toolkit-resume-button/block.php' );
				}

				if ( isset( $this->active_classes['uncanny_learndash_toolkit\FrontendLoginPlus'] ) ) {
					require_once( dirname( __FILE__ ) . '/src/toolkit-frontend-login/block.php' );
				}
			} );

			if ( $add_block_scripts ) {

				// Enqueue Gutenberg block assets for both frontend + backend
				// add_action( 'enqueue_block_assets', function () {
				// 	wp_enqueue_style(
				// 		$this->prefix . '-gutenberg-blocks',
				// 		plugins_url( 'blocks/dist/style-index.css', dirname( __FILE__ ) ),
				// 		array(),
				// 		UNCANNY_TOOLKIT_VERSION
				// 	);
				// } );

				// Enqueue Gutenberg block assets for backend editor
				add_action( 'enqueue_block_editor_assets', function () {
					wp_enqueue_script(
						$this->prefix . '-gutenberg-blocks-editor',
						plugins_url( 'blocks/dist/index.js', dirname( __FILE__ ) ),
						array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
						UNCANNY_TOOLKIT_VERSION,
						true
					);

					// Get only the Free blocks
					$free_blocks = array_values( array_map( function ( $block ) {
						// Remove the prefix
						return str_replace( 'uncanny_learndash_toolkit\\', '', $block );
					}, array_filter( $this->active_classes, function ( $block ) {
						// Filter only Pro blocks
						return strpos( $block, 'uncanny_learndash_toolkit\\' ) !== false;
					} ) ) );

					wp_add_inline_script( $this->prefix . '-gutenberg-blocks-editor', 'var ultGutenbergModules = ' . json_encode( $free_blocks ), 'before' );

					// Add support for Uncanny Toolkit Pro for LearnDash > 3.4.3
					if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
						if ( version_compare( UNCANNY_TOOLKIT_PRO_VERSION, '3.4.3', '<' ) ) {
							// Add a variable with the old data
							wp_localize_script( $this->prefix . '-gutenberg-blocks-editor', 'ultpModules', array(
								'active' => $this->active_classes,
							) );
						}
					}

					wp_enqueue_style(
						$this->prefix . '-gutenberg-blocks-editor',
						plugins_url( 'blocks/dist/index.css', dirname( __FILE__ ) ),
						array( 'wp-edit-blocks' ),
						UNCANNY_TOOLKIT_VERSION
					);
				} );
			}
			if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
				// Legacy filter
				// Create custom block category
				add_filter( 'block_categories', array( $this, 'block_categories' ), 10, 2 );
			} else {
				// Create custom block category
				add_filter( 'block_categories_all', array( $this, 'block_categories' ), 10, 2 );
			}
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
