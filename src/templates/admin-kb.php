<?php

namespace uncanny_learndash_toolkit;

if ( Config::is_pro_active() ) {
	$kb_category = 'uncanny-toolkit-pro';
	$json        = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/kb/' . $kb_category . '?wpnonce=' . wp_create_nonce( time() ) );
	if ( ! is_wp_error( $json ) ) {
		if ( 200 === $json['response']['code'] ) {
			$data = json_decode( $json['body'], true );
			if ( $data ) {
				echo $data;
			}
		}
	}
}

$kb_category = 'uncanny-learndash-toolkit';
$json        = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/kb/' . $kb_category . '?wpnonce=' . wp_create_nonce( time() ) );
if ( ! is_wp_error( $json ) ) {
	if ( 200 === $json['response']['code'] ) {
		$data = json_decode( $json['body'], true );
		if ( $data ) {
			echo $data;
		}
	}
}
