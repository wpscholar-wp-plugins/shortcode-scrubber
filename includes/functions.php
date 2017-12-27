<?php

namespace ShortcodeScrubber;

/**
 * Disable a shortcode by name, can optionally hide shortcode content
 *
 * @param string $shortcode Name of the shortcode to disable
 * @param bool $show_content Whether or not to show the content
 */
function disable_shortcode( $shortcode, $show_content = true ) {
	add_shortcode( $shortcode, function ( $atts, $content = '' ) use ( $show_content ) {
		return $show_content ? $content : '';
	} );
}

/**
 * Executes the specified shortcodes in the content.
 *
 * @param string $content Content to be processed for shortcodes
 * @param array $shortcodes The shortcodes to be processed (all others will be ignored)
 *
 * @return string
 */
function do_shortcodes( $content, array $shortcodes ) {
	global $shortcode_tags;
	$original_shortcode_tags = $shortcode_tags;
	$shortcode_tags = array_intersect_key( $shortcode_tags, array_fill_keys( $shortcodes, true ) );
	$content = do_shortcode( $content );
	$shortcode_tags = $original_shortcode_tags;

	return $content;
}

/**
 * Find the active shortcodes in a block of content.
 *
 * @param string $content
 * @param array $shortcodes
 *
 * @return array
 */
function find_active_shortcodes_in_content( $content, array $shortcodes = [] ) {
	$shortcodes_found = array();
	if ( empty( $shortcodes ) ) {
		$shortcodes = null;
	}
	preg_match_all( '/' . get_shortcode_regex( $shortcodes ) . '/', $content, $located );
	if ( ! empty( $located[2] ) && is_array( $located[2] ) ) {
		foreach ( $located[2] as $shortcode_tag ) {
			$shortcodes_found[] = $shortcode_tag;
		}
	}

	return $shortcodes_found;
}

/**
 * Find widgets containing shortcodes, or a specific shortcode if provided
 *
 * @param array $shortcodes
 *
 * @return array
 */
function find_widgets_containing_shortcodes( array $shortcodes = [] ) {

	global $wp_widget_factory, $wp_registered_sidebars;

	$widgets_containing_shortcodes = [];

	$widget_classes = array_flip( wp_list_pluck( $wp_widget_factory->widgets, 'id_base' ) );

	foreach ( wp_get_sidebars_widgets() as $sidebar_id => $widget_ids ) {
		foreach ( $widget_ids as $widget_id ) {
			preg_match( '#(.*)-(\d)*$#', $widget_id, $matches );
			if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
				$widget_slug = $matches[1];
				$widget_key = $matches[2];
				$widgets = get_option( "widget_{$widget_slug}" );
				if ( isset( $widgets[ $widget_key ] ) ) {
					$widget_data_string = wp_json_encode( $widgets[ $widget_key ] );
					$active_shortcodes = find_active_shortcodes_in_content( $widget_data_string, $shortcodes );
					if ( count( $active_shortcodes ) ) {

						$sidebar_label = __( 'Inactive Widgets', 'shortcode-scrubber' );
						if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
							$sidebar_label = $wp_registered_sidebars[ $sidebar_id ]['name'];
						}

						$widget_label = $wp_widget_factory->widgets[ $widget_classes[ $widget_slug ] ]->name;

						if ( 'wp_inactive_widgets' === $sidebar_id ) {
							$edit_link = admin_url( 'widgets.php#wp_inactive_widgets' );
						} else {
							$edit_link = admin_url( '/customize.php?autofocus[panel]=widgets&autofocus[section]=sidebar-widgets-' . $sidebar_id );
						}

						$title = ! empty( $widgets[ $widget_key ]['title'] ) ? $widgets[ $widget_key ]['title'] : '';

						$widgets_containing_shortcodes[] = (object) [
							'title'          => $title,
							'edit_link'      => $edit_link,
							'sidebar_label'  => $sidebar_label,
							'sidebar_id'     => $sidebar_id,
							'widget_label'   => $widget_label,
							'widget_base_id' => $widget_slug,
							'widget_class'   => $widget_classes[ $widget_slug ],
							'widget_id'      => $widget_id,
							'shortcodes'     => $active_shortcodes,
						];
					}
				}
			}
		}
	}

	return $widgets_containing_shortcodes;
}

/**
 * Get all registered shortcodes
 *
 * TODO: Create a trac ticket to add function to WordPress core that allows fetching shortcodes without directly accessing the global variable.
 *
 * @return array
 */
function get_shortcodes() {
	global $shortcode_tags;
	$shortcodes = $shortcode_tags;
	ksort( $shortcodes );

	return $shortcodes;
}

/**
 * Get all registered widget types
 *
 * @return array
 */
function get_widget_types() {
	global $wp_widget_factory;
	$widget_types = [];
	foreach ( $wp_widget_factory->widgets as $widget ) {
		$widget_types[ $widget->id_base ] = $widget->name;
	}
	asort( $widget_types );

	return $widget_types;
}

/**
 * Get all registered widget areas
 *
 * @return array
 */
function get_widget_areas() {
	global $wp_registered_sidebars;
	$widget_areas = [
		'wp_inactive_widgets' => __( 'Inactive Widgets', 'shortcode-scrubber' ),
	];
	foreach ( $wp_registered_sidebars as $sidebar ) {
		$widget_areas[ $sidebar['id'] ] = $sidebar['name'];
	}
	asort( $widget_areas );

	return $widget_areas;
}

/**
 * Register a new shortcode filter
 *
 * @param string $name
 * @param string $label
 * @param callable $callback
 * @param array $args
 */
function add_shortcode_filter( $name, $label, callable $callback, array $args = [] ) {
	$filter = (object) array_merge( $args, compact( 'name', 'label', 'callback' ) );
	add_filter( __NAMESPACE__ . ':shortcode-filters', function ( $filters ) use ( $filter ) {
		$filters[ $filter->name ] = $filter;

		return $filters;
	} );
}

/**
 * Get shortcode filters
 *
 * @return array
 */
function get_shortcode_filters() {
	return (array) apply_filters( __NAMESPACE__ . ':shortcode-filters', [] );
}