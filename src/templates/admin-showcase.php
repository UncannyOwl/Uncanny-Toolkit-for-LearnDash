<?php
/**
 * Template for showcasing LearnDash plugins available.
 *
 * @package uncanny-learndash-toolkit
 */

namespace uncanny_learndash_toolkit;

$product_id = defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ? 1377 : 1;
$json = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/download/' . $product_id );

if ( ! is_wp_error( $json ) ) {
	if ( 200 === wp_remote_retrieve_response_code( $json ) ) {
		$data = json_decode( $json['body'], true );
		if ( $data ) {
			echo $data;
		}
	}
}
