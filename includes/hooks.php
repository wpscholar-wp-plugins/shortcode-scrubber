<?php

use ShortcodeScrubber\Options;

// Magically enable nested shortcodes for shortcodes that don't support it
add_filter( 'do_shortcode_tag', 'do_shortcode' );

// Automatically hide broken shortcodes
add_filter( 'the_content', 'ShortcodeScrubber\hide_broken_shortcodes' );
add_filter( 'the_excerpt', 'ShortcodeScrubber\hide_broken_shortcodes' );
add_filter( 'widget_text', 'ShortcodeScrubber\hide_broken_shortcodes' );

// Setup admin pages
add_action( 'admin_menu', function () {

	// Add main shortcodes page
	$shortcodes = add_menu_page(
		esc_html__( 'Shortcodes', 'shortcode-scrubber' ),
		esc_html__( 'Shortcodes', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber',
		function () {
			require plugin_dir_path( SHORTCODE_SCRUBBER_FILE ) . 'templates/shortcodes.php';
		},
		'dashicons-editor-code'
	);

	add_action( "load-{$shortcodes}", function () {

		add_screen_option( 'per_page', array(
			'label'   => __( 'Number of items per page', 'shortcode-scrubber' ),
			'default' => 10,
			'option'  => 'shortcode_scrubber_items_per_page'
		) );

	} );

	// Add shortcode post usages page
	$post_usages = add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Find Post Usages', 'shortcode-scrubber' ),
		esc_html__( 'Find Post Usages', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-post-usages',
		function () {
			require SHORTCODE_SCRUBBER_DIR . '/templates/shortcode-post-usages.php';
		}
	);

	add_action( "load-{$post_usages}", function () {

		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Number of items per page', 'shortcode-scrubber' ),
			'default' => 10,
			'option'  => 'shortcode_scrubber_posts_per_page'
		) );

	} );

	// Add shortcode widget usages page
	$widget_usages = add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Find Widget Usages', 'shortcode-scrubber' ),
		esc_html__( 'Find Widget Usages', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-widget-usages',
		function () {
			require SHORTCODE_SCRUBBER_DIR . '/templates/shortcode-widget-usages.php';
		}
	);

	add_action( "load-{$widget_usages}", function () {

		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Number of items per page', 'shortcode-scrubber' ),
			'default' => 10,
			'option'  => 'shortcode_scrubber_widgets_per_page'
		) );

	} );

	// Add shortcode filter management page
	$shortcode_filters = add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Shortcode Filters', 'shortcode-scrubber' ),
		esc_html__( 'Shortcode Filters', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-filters',
		function () {
			require SHORTCODE_SCRUBBER_DIR . '/templates/shortcode-filters.php';
		}
	);

	add_action( "load-{$shortcode_filters}", function () {

		if ( isset( $_GET['action'], $_GET['shortcode'] ) && 'delete' === $_GET['action'] ) {
			\ShortcodeScrubber\deactivate_shortcode_filter( filter_input( INPUT_GET, 'shortcode', FILTER_SANITIZE_STRING ) );
			wp_safe_redirect( remove_query_arg( [ 'action', 'shortcode' ] ) );
			exit;
		}

		add_screen_option( 'per_page', array(
			'label'   => __( 'Number of items per page', 'shortcode-scrubber' ),
			'default' => 10,
			'option'  => 'shortcode_scrubber_filters_per_page'
		) );

	} );

	// Add shortcode management page
	add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Manage Filters', 'shortcode-scrubber' ),
		esc_html__( 'Manage Filters', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-manage',
		function () {
			require SHORTCODE_SCRUBBER_DIR . '/templates/shortcode-manage-filters.php';
		}
	);

	// Add settings page
	/*add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Settings', 'shortcode-scrubber' ),
		esc_html__( 'Settings', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-settings',
		function () {
			require plugin_dir_path( SHORTCODE_SCRUBBER_FILE ) . 'templates/settings.php';
		}
	);*/

} );

// Handle saving of screen options
add_filter( 'set-screen-option', function ( $status, $option, $value ) {
	switch ( $option ) {
		case 'shortcode_scrubber_items_per_page':
		case 'shortcode_scrubber_posts_per_page':
		case 'shortcode_scrubber_filters_per_page':
		case 'shortcode_scrubber_widgets_per_page':
			return absint( $value );
		default:
			return $status;
	}
}, 10, 3 );

// Apply custom shortcode actions
add_action( 'template_redirect', '\ShortcodeScrubber\apply_shortcode_filters', 999 );

// Make it easy to check for shortcodes on active plugins
add_filter( 'plugin_action_links', function ( $actions, $plugin_file, $plugin_data ) {

	if ( is_plugin_active( $plugin_file ) ) {
		$actions['shortcode_check'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( '/admin.php?page=shortcode-scrubber&filter_provider=' . $plugin_data['Name'] ) ),
			esc_html__( 'Check for Shortcodes', 'shortcode-scrubber' )
		);
	}

	return $actions;
}, 10, 3 );