<?php

/*
 * Plugin Name: Shortcode Scrubber
 * Plugin URI:
 * Description:
 * Version: 0.1.0
 * Author: Micah Wood
 * Author URI:  https://wpscholar.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'SHORTCODE_SCRUBBER_FILE', __FILE__ );
define( 'SHORTCODE_SCRUBBER_DIR', untrailingslashit( plugin_dir_path( SHORTCODE_SCRUBBER_FILE ) ) );

require __DIR__ . '/includes/bootstrap.php';