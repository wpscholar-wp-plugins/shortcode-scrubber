<?php
/**
 * Shortcode class.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

// phpcs:disable WordPress.NamingConventions.ValidFunctionName

/**
 * Class Shortcode
 *
 * @package ShortcodeScrubber
 *
 * TODO: Use https://github.com/phpDocumentor/ReflectionDocBlock to provide documentation?
 */
class Shortcode {

	/**
	 * Shortcode context (e.g. core, plugin, theme, parent-theme)
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * Name of the shortcode provider (e.g. WordPress)
	 *
	 * @var string
	 */
	protected $provider;

	/**
	 * Instance of the reflection class
	 *
	 * @var \ReflectionFunction|\ReflectionMethod|null
	 */
	protected $reflection;

	/**
	 * Shortcode tag name
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * Shortcode constructor.
	 *
	 * @param string $tag Tag name
	 */
	public function __construct( $tag ) {
		$this->tag = $tag;
	}

	/**
	 * Fetch the tag name
	 *
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}

	/**
	 * Check if shortcode is active
	 *
	 * @return bool
	 */
	public function isActive() {
		global $shortcode_tags;

		return isset( $shortcode_tags[ $this->tag ] );
	}

	/**
	 * Fetch the shortcode callback
	 *
	 * @return callable|null
	 */
	public function getCallback() {
		global $shortcode_tags;

		return $this->isActive() ? $shortcode_tags[ $this->tag ] : '__return_null';
	}

	/**
	 * Get plugin file
	 *
	 * @return string
	 */
	public function getPluginFile() {
		$plugin_file = '';
		$plugins     = get_plugins();
		$file        = plugin_basename( $this->reflection->getFileName() );
		if ( array_key_exists( $file, $plugins ) ) {
			$plugin_file = $file;
		} else {
			$paths    = explode( '/', $file );
			$root_dir = array_shift( $paths );
			foreach ( $plugins as $path => $data ) {
				if ( 0 === strpos( $path, $root_dir ) ) {
					$plugin_file = $path;
					break;
				}
			}
		}

		return $plugin_file;
	}

	/**
	 * Get an instance of the shortcode callback reflection class
	 *
	 * @return \ReflectionFunction|\ReflectionMethod|null
	 */
	public function getReflection() {

		if ( null === $this->reflection ) {

			global $shortcode_tags;

			try {

				if ( $this->isActive() ) {

					$callback = $shortcode_tags[ $this->tag ];

					if ( is_string( $callback ) ) {
						$this->reflection = new \ReflectionFunction( $callback );
					} elseif ( is_array( $callback ) ) {
						$this->reflection = new \ReflectionMethod( $callback[0], $callback[1] );
					} elseif ( is_object( $callback ) && ( $callback instanceof \Closure ) ) {
						$this->reflection = new \ReflectionFunction( $callback );
					}
				}
			} catch ( \Exception $e ) {
				trigger_error( $e->getMessage() ); // phpcs:ignore WordPress
			}
		}

		return $this->reflection;
	}

	/**
	 * Get the context in which a shortcode is defined (e.g. core, plugin, theme, parent-theme)
	 *
	 * @return string|null
	 */
	public function getContext() {

		if ( null === $this->context ) {

			$reflection = $this->getReflection();

			if ( $reflection ) {

				$file_name = $reflection->getFileName();

				// WordPress Core
				if ( 'embed' === $this->tag || 0 === strpos( $file_name, ABSPATH . 'wp-includes/media.php' ) ) {
					$this->context = 'core';
				}

				// WordPress Plugin
				if ( 0 === strpos( $file_name, WP_PLUGIN_DIR . '/' ) ) {
					$this->context = 'plugin';
				}

				// WordPress Theme
				$theme_dir        = get_stylesheet_directory();
				$parent_theme_dir = get_template_directory();

				if ( 0 === strpos( $file_name, $theme_dir ) ) {
					$this->context = 'theme';
					if ( $theme_dir !== $parent_theme_dir ) {
						$this->context = 'theme-child';
					}
				}

				if ( $theme_dir !== $parent_theme_dir && 0 === strpos( $file_name, $parent_theme_dir ) ) {
					$this->context = 'theme-parent';
				}
			}

			if ( empty( $this->context ) ) {
				$this->context = null;
			}
		}

		return $this->context;
	}

	/**
	 * Get the shortcode provider name (e.g. WordPress)
	 *
	 * @return string
	 */
	public function getProvider() {

		if ( null === $this->provider ) {

			switch ( $this->getContext() ) {

				case 'core':
					$this->provider = __( 'WordPress', 'shortcode-scrubber' );
					break;

				case 'plugin':
					$plugins     = get_plugins();
					$plugin_file = $this->getPluginFile();
					if ( array_key_exists( $plugin_file, $plugins ) ) {
						$this->provider = $plugins[ $plugin_file ]['Name'];
					} else {
						$this->provider = __( 'Unknown Plugin', 'shortcode-scrubber' );
					}
					break;

				case 'theme':
				case 'theme-child':
					$theme          = wp_get_theme( basename( get_stylesheet_directory() ) );
					$this->provider = $theme->name;
					break;

				case 'theme-parent':
					$theme          = wp_get_theme( basename( get_template_directory() ) );
					$this->provider = $theme->name;
					break;

				default:
					$this->provider = __( 'Unknown', 'shortcode-scrubber' );
			}
		}

		return $this->provider;
	}

}
