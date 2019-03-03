<?php
/**
 * Register generic shortcode filters.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

add_shortcode_filter(
	'strip',
	[
		'label'       => __( 'Strip', 'shortcode-scrubber' ),
		'description' => __( 'Removes all instances of this shortcode and discards the shortcode content.', 'shortcode-scrubber' ),
		'callback'    => function ( $shortcode ) {
			add_shortcode( $shortcode, '__return_empty_string' );
		},
	]
);

add_shortcode_filter(
	'disable',
	[
		'label'       => __( 'Disable', 'shortcode-scrubber' ),
		'description' => __( 'Removes all instances of this shortcode, but keeps the shortcode content.', 'shortcode-scrubber' ),
		'callback'    => function ( $shortcode ) {
			add_shortcode( $shortcode, __NAMESPACE__ . '\\return_shortcode_content' );
		},
	]
);
