<?php

/*
 * Plugin Name: Shortcode Scrubber
 * Plugin URI:
 * Description: A powerful tool for cleaning up shortcodes on your site and confidently managing plugins and themes that use shortcodes.
 * Version: 0.1.0
 * Author: Micah Wood
 * Author URI:  https://wpscholar.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'SHORTCODE_SCRUBBER_FILE', __FILE__ );
define( 'SHORTCODE_SCRUBBER_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

// Check plugin requirements
global $pagenow;
if ( 'plugins.php' === $pagenow ) {
	require( dirname( __FILE__ ) . '/includes/plugin-check.php' );
	$plugin_check = new Shortcode_Scrubber_Plugin_Check( __FILE__ );
	$plugin_check->min_php_version = '5.6';
	$plugin_check->min_wp_version = '3.2';
	$plugin_check->check_plugin_requirements();
}

require dirname( __FILE__ ) . '/includes/bootstrap.php';