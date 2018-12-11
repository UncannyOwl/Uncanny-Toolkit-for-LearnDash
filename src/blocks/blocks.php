<?php

/**
 * Change this into a class
 * put a global variable called prefix or gutenberg prefix
 */

// global $gt_block_prefix = 'ultp';

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if Gutenberg exists
if ( function_exists( 'register_block_type' ) ){
	// Register Blocks
	add_action( 'init', function(){
		require_once( dirname( __FILE__ ) . '/src/toolkit-breadcrumbs/block.php' );
	});

	// Enqueue Gutenberg block assets for both frontend + backend.

	add_action( 'enqueue_block_assets', function(){
		wp_enqueue_style(
			'ultp-gutenberg-blocks',
			plugins_url( 'blocks/dist/blocks.style.build.css', dirname( __FILE__ ) ),
			[ 'wp-blocks' ],
			UNCANNY_REPORTING_VERSION
		);
	});

	// Enqueue Gutenberg block assets for backend editor.

	add_action( 'enqueue_block_editor_assets', function(){
		wp_enqueue_script(
			'ultp-gutenberg-editor',
			plugins_url( 'blocks/dist/blocks.build.js', dirname( __FILE__ ) ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ],
			UNCANNY_REPORTING_VERSION,
			true
		);

		wp_enqueue_style(
			'ultp-gutenberg-editor',
			plugins_url( 'blocks/dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			[ 'wp-edit-blocks' ],
			UNCANNY_REPORTING_VERSION
		);
	});

	// Create custom block category
	add_filter( 'block_categories', function( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'uncanny-learndash-toolkit',
					'title' => __( 'Uncanny LearnDash Toolkit', 'uncanny-learndash-toolkit' ),
				),
			)
		);
	}, 10, 2 );
}