<?php

namespace ShortcodeScrubber;

/**
 * Class ShortcodeWrapper
 *
 * @package ShortcodeScrubber
 */
class ShortcodeWrapper {

	protected $shortcode;

	public function __construct( $shortcode ) {
		$this->shortcode = $shortcode;
		add_filter( 'pre_do_shortcode_tag', [ $this, 'shortcode' ], 10, 4 );
	}

	public function shortcode( $output, $shortcode, $attr, $matches ) {

		if ( $shortcode === $this->shortcode ) {

			global $shortcode_tags;

			$atts = array_filter( (array) $attr );

			$defaults = apply_filters( __CLASS__ . ':shortcode-defaults', [], $shortcode );
			$atts = apply_filters( __CLASS__ . ':shortcode-atts', array_merge( $defaults, $atts ), $shortcode, $atts );
			$content = apply_filters( __CLASS__ . ':shortcode-content', ( isset( $matches[5] ) ? $matches[5] : null ), $shortcode, $atts );

			$output = $matches[1] . call_user_func( $shortcode_tags[ $shortcode ], array_change_key_case( $atts, CASE_LOWER ), $content, $shortcode ) . $matches[6];

			if ( $content ) {
				$start = strpos( $output, $content );
				if ( false !== $start ) {
					$end = $start + strlen( $content );
					$before = apply_filters( __CLASS__ . ':shortcode-before', substr( $output, 0, $start ), $shortcode, $atts );
					$after = apply_filters( __CLASS__ . ':shortcode-after', substr( $output, $end, strlen( $output ) ), $shortcode, $atts );
					$output = $before . $content . $after;
				}
			}

			$output = apply_filters( 'do_shortcode_tag', $output, $shortcode, $atts, $matches );

		}

		return $output;
	}

}