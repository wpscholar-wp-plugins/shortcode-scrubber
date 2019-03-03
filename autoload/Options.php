<?php
/**
 * Class for handling plugin options.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

/**
 * Class Options
 *
 * @package ShortcodeScrubber
 */
class Options {

	/**
	 * Option name
	 *
	 * @var string
	 */
	const NAME = __NAMESPACE__ . ':options';

	/**
	 * Options
	 *
	 * @var array
	 */
	protected static $options;

	/**
	 * Check if option exists
	 *
	 * @param string $name Option name
	 *
	 * @return bool
	 */
	public static function has( $name ) {

		if ( ! isset( self::$options ) ) {
			self::fetch();
		}

		return isset( self::$options[ $name ] );
	}

	/**
	 * Get option value
	 *
	 * @param string $name Option name
	 * @param mixed  $default Default value
	 *
	 * @return mixed
	 */
	public static function get( $name, $default = null ) {

		$value = $default;

		if ( ! isset( self::$options ) ) {
			self::fetch();
		}

		if ( self::has( $name ) ) {
			$value = self::$options[ $name ];
		}

		return $value;
	}

	/**
	 * Set option value
	 *
	 * @param string $name Option name
	 * @param mixed  $value Value
	 */
	public static function set( $name, $value ) {
		if ( ! isset( self::$options ) ) {
			self::fetch();
		}

		self::$options[ $name ] = $value;

		add_action( 'shutdown', [ __CLASS__, 'save' ] );
	}

	/**
	 * Delete option
	 *
	 * @param string $name Option name
	 */
	public static function delete( $name ) {
		if ( ! isset( self::$options ) ) {
			self::fetch();
		}

		unset( self::$options[ $name ] );

		add_action( 'shutdown', [ __CLASS__, 'save' ] );
	}

	/**
	 * Fetch options from database
	 */
	public static function fetch() {
		self::$options = array_filter( (array) get_option( self::NAME, [] ) );
	}

	/**
	 * Save options to database
	 */
	public static function save() {
		update_option( self::NAME, self::$options );
	}

}
