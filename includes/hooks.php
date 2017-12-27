<?php

// Magically enable nested shortcodes for shortcodes that don't support it
add_filter( 'do_shortcode_tag', 'do_shortcode' );

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
			'option'  => 'shortcode_scrubber_post_locator_items_per_page'
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
			'option'  => 'shortcode_scrubber_widget_locator_items_per_page'
		) );

	} );

	// Add shortcode actions page
	add_submenu_page(
		'shortcode-scrubber',
		esc_html__( 'Manage', 'shortcode-scrubber' ),
		esc_html__( 'Manage', 'shortcode-scrubber' ),
		'manage_options',
		'shortcode-scrubber-manage',
		function () {
			require SHORTCODE_SCRUBBER_DIR . '/templates/shortcode-manage.php';
		}
	);

} );

// Handle saving of screen options
add_filter( 'set-screen-option', function ( $status, $option, $value ) {
	switch ( $option ) {
		case 'shortcode_scrubber_items_per_page':
		case 'shortcode_scrubber_post_locator_items_per_page':
		case 'shortcode_scrubber_widget_locator_items_per_page':
			return absint( $value );
		default:
			return $status;
	}
}, 10, 3 );