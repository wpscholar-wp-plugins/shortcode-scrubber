<?php
/**
 * Plugin functions
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

/**
 * Find shortcode tags in a block of content
 *
 * @param string $content Shortcode content
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
 * @param array $tags Tag names
 *
 * @return array
 */
function filter_unregistered_shortcode_tags( array $tags ) {
	return array_filter( $tags, 'shortcode_exists' );
}

/**
 * Filter a list of shortcode tags to remove those that ARE registered. (Returns only unregistered shortcode tags)
 *
 * @param array $tags Tag names
 *
 * @return array
 */
function filter_registered_shortcode_tags( array $tags ) {
	return array_diff( $tags, filter_unregistered_shortcode_tags( $tags ) );
}

/**
 * Custom shortcode callback function for returning the shortcode content.
 *
 * @param array  $atts Shortcode attributes
 * @param string $content Shortcode content
 *
 * @return string
 */
function return_shortcode_content( $atts, $content = '' ) {
	return $content;
}

/**
 * Disable a shortcode by name, can optionally hide shortcode content
 *
 * @param string $shortcode Name of the shortcode to disable
 * @param bool   $show_content Whether or not to show the content
 */
function disable_shortcode( $shortcode, $show_content = true ) {
	add_shortcode(
		$shortcode,
		function ( $atts, $content = '' ) use ( $show_content ) {
			return $show_content ? $content : '';
		}
	);
}

/**
 * Remove all specified shortcode tags from the given content. Removes registered shortcodes by default.
 *
 * A duplicate of the strip_shortcodes() function in WordPress, but allows you to strip unregistered shortcodes as well.
 * Additionally, this function allows you to optionally strip the shortcode content or keep it.
 *
 * @param string $content Shortcode content
 * @param array  $tags Shortcode tags
 * @param bool   $strip_content Whether or not to strip content
 *
 * @return string
 */
function strip_shortcodes( $content, array $tags = [], $strip_content = true ) {

	global $shortcode_tags;

	if ( empty( $tags ) ) {
		$tags = array_keys( $shortcode_tags );
	}

	$tags_to_remove = array_intersect( find_shortcode_tags( $content ), $tags );

	if ( $tags_to_remove ) {

		$pattern = get_shortcode_regex( $tags_to_remove );

		if ( $strip_content ) {
			$content = (string) preg_replace_callback( "/{$pattern}/", 'strip_shortcode_tag', $content );
		} else {
			$content = (string) preg_replace( "/{$pattern}/", '$5', $content );
		}

		// Always restore square braces so we don't break things like <!--[if IE ]>
		$content = unescape_invalid_shortcodes( $content );

	}

	return $content;
}

/**
 * Strip shortcodes recursively
 *
 * @param string $content Content
 * @param array  $tags Tag names
 *
 * @return string
 */
function strip_shortcodes_recursively( $content, array $tags = [] ) {
	$content     = strip_shortcodes( $content, $tags, false );
	$nested_tags = array_intersect( find_shortcode_tags( $content ), $tags );
	if ( $nested_tags ) {
		$content = strip_shortcodes_recursively( $content, $tags );
	}

	return $content;
}

/**
 * Hide broken shortcodes
 *
 * @param string $content Content
 *
 * @return string
 */
function hide_broken_shortcodes( $content ) {
	$shortcodes              = find_shortcode_tags( $content );
	$unregistered_shortcodes = filter_registered_shortcode_tags( $shortcodes );
	$content                 = strip_shortcodes_recursively( $content, $unregistered_shortcodes );

	return $content;
}

/**
 * Executes the specified shortcodes in the content.
 *
 * @param string $content Content to be processed for shortcodes
 * @param array  $shortcodes The shortcodes to be processed (all others will be ignored)
 *
 * @return string
 */
function do_shortcodes( $content, array $shortcodes ) {
	global $shortcode_tags;
	$original_shortcode_tags = $shortcode_tags;
	$shortcode_tags          = array_intersect_key( $shortcode_tags, array_flip( $shortcodes ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	$content                 = do_shortcode( $content );
	$shortcode_tags          = $original_shortcode_tags; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	return $content;
}

/**
 * Find widgets containing shortcodes, or a specific shortcode if provided
 *
 * @param array $shortcodes Tag names
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
					$widget_key  = $matches[2];
					$widgets     = get_option( "widget_{$widget_slug}" );
					if ( isset( $widgets[ $widget_key ] ) ) {
						$widget_data_string   = wp_json_encode( $widgets[ $widget_key ] );
						$shortcode_tags_found = find_shortcode_tags( $widget_data_string );
						$active_shortcodes    = $shortcodes ? array_intersect( $shortcode_tags_found, $shortcodes ) : $shortcode_tags_found;
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
 * @param string $id Shortcode filter name
 * @param array  $args Shortcode filter arguments
 */
function add_shortcode_filter( $id, array $args = [] ) {
	$defaults = [
		'id'          => $id,
		'label'       => '',
		'description' => '',
		'callback'    => null,
	];

	$filter = (object) array_merge( $defaults, $args );

	add_filter(
		__NAMESPACE__ . ':shortcode-filters',
		function ( $filters ) use ( $filter ) {
			$filters[ $filter->id ] = $filter;

			return $filters;
		}
	);
}

/**
 * Get shortcode filters
 *
 * @return array
 */
function get_shortcode_filters() {
	return (array) apply_filters( __NAMESPACE__ . ':shortcode-filters', [] ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
}

/**
 * Get a shortcode filter
 *
 * @param string $id Shortcode filter name
 *
 * @return object|null
 */
function get_shortcode_filter( $id ) {
	$filters = get_shortcode_filters();

	return isset( $filters[ $id ] ) ? $filters[ $id ] : null;
}

/**
 * Get a list of actionable shortcode filters.
 *
 * @param string $shortcode_tag Tag name
 *
 * @return array
 */
function get_actionable_shortcode_filters( $shortcode_tag ) {
	$filters = get_shortcode_filters();

	foreach ( $filters as $id => $filter ) {
		if ( isset( $filter->shortcode ) && $shortcode_tag !== $filter->shortcode ) {
			unset( $filters[ $id ] );
		}
	}

	return $filters;
}

/**
 * Apply shortcode filters
 */
function apply_shortcode_filters() {
	$filters         = get_shortcode_filters();
	$applied_filters = (array) Options::get( 'applied_filters', [] );
	foreach ( $applied_filters as $shortcode => $id ) {
		if (
			isset( $filters[ $id ] ) &&
			is_object( $filters[ $id ] ) &&
			property_exists( $filters[ $id ], 'callback' ) &&
			is_callable( $filters[ $id ]->callback )
		) {
			$callable = $filters[ $id ]->callback;
			$callable( $shortcode );
		}
	}
}

/**
 * Apply the shortcode filter for a specific shortcode
 *
 * @param string $shortcode Tag name
 */
function apply_filter_for_shortcode( $shortcode ) {
	$applied_filters = (array) Options::get( 'applied_filters', [] );
	if ( isset( $applied_filters[ $shortcode ] ) ) {
		$id     = $applied_filters[ $shortcode ];
		$filter = get_shortcode_filter( $id );
		if ( $filter && isset( $filter->callback ) && is_callable( $filter->callback ) ) {
			$callable = $filter->callback;
			$callable( $shortcode );
		}
	}
}

/**
 * Activate a shortcode filter
 *
 * @param string $shortcode Tag name
 * @param string $filter_id Filter ID
 */
function activate_shortcode_filter( $shortcode, $filter_id ) {
	$filters = get_actionable_shortcode_filters( $shortcode );
	if ( array_key_exists( $filter_id, $filters ) ) {
		$applied_filters = (array) Options::get( 'applied_filters', [] );
		if ( ! array_key_exists( $shortcode, $applied_filters ) || $applied_filters[ $shortcode ] !== $filter_id ) {
			$applied_filters[ $shortcode ] = $filter_id;
			Options::set( 'applied_filters', $applied_filters );
		}
	}

}

/**
 * Deactivate a shortcode filter
 *
 * @param string $shortcode Tag name
 */
function deactivate_shortcode_filter( $shortcode ) {
	$applied_filters = (array) Options::get( 'applied_filters', [] );
	unset( $applied_filters[ $shortcode ] );
	Options::set( 'applied_filters', $applied_filters );
}

/**
 * Freeze the specified shortcodes for a specific post.
 *
 * @param \WP_Post $wp_post Post object
 * @param array    $shortcodes Tag names
 */
function freeze_shortcodes_for_post( \WP_Post $wp_post, array $shortcodes = [] ) {
	global $post;
	$original_post = $post;
	$post          = $wp_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	setup_postdata( $wp_post );
	$content         = get_the_content();
	$updated_content = do_shortcodes( $content, $shortcodes );
	if ( $content !== $updated_content ) {
		wp_update_post(
			[
				'ID'           => $wp_post->ID,
				'post_content' => $updated_content,
			]
		);
	}
	wp_reset_postdata();
	$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
}

/**
 * Freeze a shortcode
 *
 * @param string $shortcode Tag name
 */
function freeze_shortcode( $shortcode ) {

	apply_filter_for_shortcode( $shortcode );

	$post_types = wp_filter_object_list(
		array_map( 'get_post_type_object', get_post_types_by_support( 'editor' ) ),
		[ 'public' => true ]
	);

	$query = new \WP_Query(
		[
			'nopaging'  => true,
			'post_type' => wp_list_pluck( $post_types, 'name' ),
			's'         => '[' . $shortcode,
		]
	);

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			freeze_shortcodes_for_post( $post, [ $shortcode ] );
		}
	}

	$widgets = find_widgets_containing_shortcodes( [ $shortcode ] );
	foreach ( $widgets as $widget ) {
		$option_name = "widget_{$widget->widget_base_id}";
		$data        = get_option( $option_name );
		if ( $data ) {
			$json         = wp_json_encode( $data );
			$updated_json = do_shortcodes( $json, [ $shortcode ] );
			$updated_data = json_decode( $updated_json, true );
			if ( $updated_data && $data !== $updated_data ) {
				update_option( $option_name, $updated_data );
			}
		}
	}

}
