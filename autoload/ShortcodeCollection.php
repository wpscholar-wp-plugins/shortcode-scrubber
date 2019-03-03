<?php
/**
 * Shortcode collection class.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

/**
 * Class ShortcodeCollection
 *
 * @package ShortcodeScrubber
 */
class ShortcodeCollection implements \Iterator, \Countable {

	/**
	 * Internal array containing shortcode instances
	 *
	 * @var Shortcode[]
	 */
	protected $shortcodes = [];

	/**
	 * ShortcodeCollection constructor.
	 */
	public function __construct() {
		global $shortcode_tags;
		foreach ( array_keys( $shortcode_tags ) as $shortcode_tag ) {
			$this->shortcodes[] = new Shortcode( $shortcode_tag );
		}
	}

	/**
	 * Return current shortcode
	 *
	 * @return Shortcode
	 */
	public function current() {
		return current( $this->shortcodes );
	}

	/**
	 * Traverse to next shortcode
	 */
	public function next() {
		next( $this->shortcodes );
	}

	/**
	 * Key for current shortcode or null if invalid
	 *
	 * @return int|null
	 */
	public function key() {
		return key( $this->shortcodes );
	}

	/**
	 * Check if current key is valid
	 *
	 * @return bool
	 */
	public function valid() {
		return key( $this->shortcodes ) !== null;
	}

	/**
	 * Rewind shortcode array
	 */
	public function rewind() {
		reset( $this->shortcodes );
	}

	/**
	 * Get total number of shortcodes
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->shortcodes );
	}

}
