<?php

namespace ShortcodeScrubber;

/**
 * Class ShortcodePluginFilterIterator
 *
 * @package ShortcodeScrubber
 */
class ShortcodePluginFilterIterator extends \FilterIterator {

	/**
	 * The plugin file to filter by
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * ShortcodePluginFilterIterator constructor.
	 *
	 * @param ShortcodeCollection $iterator
	 * @param string $plugin_file
	 */
	public function __construct( ShortcodeCollection $iterator, $plugin_file ) {
		parent::__construct( $iterator );
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Filter function used to accept or reject results
	 *
	 * @return bool
	 */
	public function accept() {

		/**
		 * @var $shortcode Shortcode
		 */
		$shortcode = $this->current();

		if ( $shortcode->getContext() === 'plugin' ) {
			return WP_PLUGIN_DIR . '/' . $this->plugin_file === $shortcode->getPluginFile();
		}

		return false;
	}

}