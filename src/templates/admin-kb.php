<?php

namespace uncanny_learndash_toolkit;

// Load Pro KB articles first if Pro is active
if ( Config::is_pro_active() ) {
	$kb_category = 'uncanny-toolkit-pro';
	$json        = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/kb/' . $kb_category );

	if ( ! is_wp_error( $json ) ) {
		if ( 200 === wp_remote_retrieve_response_code( $json ) ) {
			$data = json_decode( $json['body'], true );
			if ( $data ) {
				echo $data;
			}
		}
	}
}

// Load Free KB articles
$kb_category = 'uncanny-learndash-toolkit';
$json        = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/kb/' . $kb_category );

if ( ! is_wp_error( $json ) ) {
	if ( 200 === wp_remote_retrieve_response_code( $json ) ) {
		$data = json_decode( $json['body'], true );
		if ( $data ) {
			echo $data;
		}
	}
}

?>
