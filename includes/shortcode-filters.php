<?php

namespace ShortcodeScrubber;

add_shortcode_filter(
	'remove-keep-content',
	esc_html__( 'Remove - keep content', 'shortcode-scrubber' ),
	function ( $shortcode ) {

	},
	[
		'description' => esc_html__( 'Removes all instances of this shortcode, but keeps the shortcode content.', 'shortcode-scrubber' ),
	]
);

add_shortcode_filter(
	'remove',
	esc_html__( 'Remove - discard content', 'shortcode-scrubber' ),
	function ( $shortcode ) {

	},
	[
		'description' => esc_html__( 'Removes all instances of this shortcode and discards the shortcode content.', 'shortcode-scrubber' ),
	]
);

add_shortcode_filter(
	'replace-generated',
	esc_html__( 'Replace with generated output', 'shortcode-scrubber' ),
	function ( $shortcode ) {

	},
	[
		'description' => esc_html__( 'Removes all instances of this shortcode with the generated output.', 'shortcode-scrubber' ),
	]
);