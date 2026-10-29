<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside Boot::uo_support_page(); variables are local to that method.

// Allowlist for markup fetched from uncannyowl.com (KB articles, plugin showcase). Returned to the includer.
// The 'post' allowlist has no inline SVG; <script>, <style>, <iframe>, <form> and <input> stay stripped.
$svg_paint = array(
	'id'                => true,
	'class'             => true,
	'style'             => true,
	'fill'              => true,
	'fill-rule'         => true,
	'clip-rule'         => true,
	'opacity'           => true,
	'transform'         => true,
	'stroke'            => true,
	'stroke-width'      => true,
	'stroke-linecap'    => true,
	'stroke-linejoin'   => true,
	'stroke-miterlimit' => true,
	'stroke-dasharray'  => true,
	'stroke-opacity'    => true,
);

$post_html = wp_kses_allowed_html( 'post' );

$post_html['img'] = array_merge(
	isset( $post_html['img'] ) ? $post_html['img'] : array(),
	array(
		'srcset'  => true,
		'sizes'   => true,
		'loading' => true,
	)
);

return apply_filters(
	'uncanny_toolkit_remote_markup_allowed_html',
	array_merge(
		$post_html,
		array(
			'svg'      => array_merge(
				$svg_paint,
				array(
					'xmlns'       => true,
					'viewbox'     => true,
					'aria-hidden' => true,
					'focusable'   => true,
					'role'        => true,
					'width'       => true,
					'height'      => true,
				)
			),
			'g'        => $svg_paint,
			'path'     => array_merge( $svg_paint, array( 'd' => true ) ),
			'circle'   => array_merge(
				$svg_paint,
				array(
					'cx' => true,
					'cy' => true,
					'r'  => true,
				)
			),
			'rect'     => array_merge(
				$svg_paint,
				array(
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
					'rx'     => true,
					'ry'     => true,
				)
			),
			'line'     => array_merge(
				$svg_paint,
				array(
					'x1' => true,
					'y1' => true,
					'x2' => true,
					'y2' => true,
				)
			),
			'polyline' => array_merge( $svg_paint, array( 'points' => true ) ),
			'polygon'  => array_merge( $svg_paint, array( 'points' => true ) ),
			'defs'     => array( 'id' => true ),
			'use'      => array_merge(
				$svg_paint,
				array(
					'href'       => true,
					'xlink:href' => true,
					'x'          => true,
					'y'          => true,
					'width'      => true,
					'height'     => true,
				)
			),
			'title'    => array( 'id' => true ),
		)
	)
);
