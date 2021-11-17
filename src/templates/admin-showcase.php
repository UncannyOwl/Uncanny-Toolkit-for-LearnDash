<?php
/**
 * Template for showcasing LearnDash plugins available.
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit;

$product_id = 1;

$url = add_query_arg(
	array(
		'wpnonce' => wp_create_nonce( time() ),
	),
	sprintf( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/download/%d', $product_id )
);

$response = wp_remote_get( $url );

// Check if there are any connection issues.
if ( ! is_wp_error( $response ) ) {
	if ( 200 === $response['response']['code'] ) {
		$data = json_decode( $response['body'], true );
		if ( $data ) {
			echo wp_kses_post( $data );
		}
	}
} else {
	// Show an error message if there is.
	echo '<h3>';
		// translators: Some error message.
		echo sprintf( esc_html__( 'An error occurred: %s', 'uncanny-learndash-toolkit' ), $response->get_error_message() );
	echo '</h3>';
}
