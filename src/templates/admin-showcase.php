<?php

namespace uncanny_learndash_toolkit;

$product_id = 1;
$json       = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/download/' . $product_id . '?wpnonce=' . wp_create_nonce( time() ) );
if ( 200 === $json['response']['code'] ) {
	$data = json_decode( $json['body'], true );
	if ( $data ) {
		echo $data;
	}

}