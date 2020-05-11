<?php
/**
 * Plugin Name: Shortcode Scrubber
 * Plugin URI: https://wpscholar.com/wordpress-plugins/shortcode-scrubber/
 * Description: A powerful tool for cleaning up shortcodes on your site and confidently managing plugins and themes that use shortcodes.
 * Version: 1.0.3
 * Author: Micah Wood
 * Author URI:  https://wpscholar.com
 * Requires at least: 3.2
 * Requires PHP: 5.6
 * Text Domain: shortcode-scrubber
 * Domain Path: languages
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright 2019-2020 by Micah Wood - All rights reserved.
 *
 * @package ShortcodeScrubber
 */

define( 'SHORTCODE_SCRUBBER_FILE', __FILE__ );
define( 'SHORTCODE_SCRUBBER_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

require __DIR__ . '/vendor/autoload.php';

// Check plugin requirements
global $pagenow;
if ( 'plugins.php' === $pagenow ) {
	$plugin_check = new WP_Forge_Plugin_Check( __FILE__ );

	$plugin_check->min_php_version = '5.6';
	$plugin_check->min_wp_version  = '3.2';
	$plugin_check->check_plugin_requirements();
}

require dirname( __FILE__ ) . '/includes/bootstrap.php';
