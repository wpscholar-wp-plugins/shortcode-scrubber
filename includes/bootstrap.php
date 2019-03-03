<?php
/**
 * Load the plugin files.
 *
 * @package ShortcodeScrubber
 */

require __DIR__ . '/functions.php';
require __DIR__ . '/hooks.php';
require __DIR__ . '/shortcode-filters.php';

// Register our custom autoloader
spl_autoload_register(
	function ( $class ) {
		$prefix = 'ShortcodeScrubber\\';
		$len    = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) === 0 ) {
			$file = dirname( __DIR__ ) . '/autoload/' . str_replace( '\\', '/', substr( $class, $len ) ) . '.php';
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	}
);
