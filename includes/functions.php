<?php

namespace ShortcodeScrubber;

/**
 * Find shortcode tags in a block of content
 *
 * @param string $content
 *
 * @return array
 */
function find_shortcode_tags( $content ) {
	$shortcodes = [];
	preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
	if ( ! empty( $matches[1] ) && is_array( $matches[1] ) ) {
		$shortcodes = array_filter( array_unique( $matches[1] ) );
	}

	return $shortcodes;
}

/**
 * Filter a list of shortcode tags to remove those that are NOT registered. (Returns only registered shortcode tags)
 *
 * @param array $tags
 *
 * @return array
 */
function filter_unregistered_shortcode_tags( array $tags ) {
	return array_filter( $tags, 'shortcode_exists' );
}

/**
 * Filter a list of shortcode tags to remove those that ARE registered. (Returns only unregistered shortcode tags)
 *
 * @param array $tags
 *
 * @return array
 */
function filter_registered_shortcode_tags( array $tags ) {
	return array_diff( $tags, filter_unregistered_shortcode_tags( $tags ) );
}

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
		if ( is_array( $widget_ids ) ) {
			foreach ( $widget_ids as $widget_id ) {
				preg_match( '#(.*)-(\d)*$#', $widget_id, $matches );
				if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
					$widget_slug = $matches[1];
					$widget_key = $matches[2];
					$widgets = get_option( "widget_{$widget_slug}" );
					if ( isset( $widgets[ $widget_key ] ) ) {
						$widget_data_string = wp_json_encode( $widgets[ $widget_key ] );
						$shortcode_tags_found = find_shortcode_tags( $widget_data_string );
						$active_shortcodes = $shortcodes ? array_intersect( $shortcode_tags_found, $shortcodes ) : $shortcode_tags_found;
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