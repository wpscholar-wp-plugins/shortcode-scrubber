<?php
/**
 * List table for displaying widgets.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ShortcodeWidgetListTable
 *
 * @package ShortcodeScrubber
 */
class ShortcodeWidgetListTable extends \WP_List_Table {

	/**
	 * Collection of items.
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * ShortcodePostListTable constructor.
	 *
	 * @param array $args Arguments
	 */
	public function __construct( $args = [] ) {
		parent::__construct(
			[
				'singular' => 'widget',
				'plural'   => 'widgets',
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$filters  = $this->get_filters();
		$per_page = $this->get_items_per_page( 'shortcode_scrubber_widgets_per_page', 10 );

		$shortcode = array_filter( (array) $filters['shortcode'] );
		$widgets   = find_widgets_containing_shortcodes( $shortcode );

		foreach ( $widgets as $widget ) {

			if ( ! empty( $filters['widget_area'] ) && $widget->sidebar_id !== $filters['widget_area'] ) {
				continue;
			}

			if ( ! empty( $filters['widget_type'] ) && $widget->widget_base_id !== $filters['widget_type'] ) {
				continue;
			}

			$this->items[] = (array) $widget;

		}

		$this->items = $this->sort( $this->items );

		$this->set_pagination_args(
			array(
				'per_page'    => $per_page,
				'total_items' => count( $widgets ),
			)
		);

	}

	/**
	 * Sort items
	 *
	 * @param array $items Collection of items to sort
	 *
	 * @return array
	 */
	public function sort( $items ) {

		$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'asc' ] ] );
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'title' ] ] );

		if ( ! array_key_exists( $orderby, $this->get_columns() ) ) {
			$orderby = $this->get_default_primary_column_name();
		}

		usort(
			$items,
			function ( $a, $b ) use ( $order, $orderby ) {

				if ( 'title' === $orderby ) {
					if ( 'asc' === $order ) {
						return strcasecmp( "{$a['widget_label']}: {$a['title']}", "{$b['widget_label']}: {$b['title']}" );
					}

					return strcasecmp( "{$b['widget_label']}: {$b['title']}", "{$a['widget_label']}: {$a['title']}" );
				}

				if ( 'asc' === $order ) {
					return strcasecmp( $a[ $orderby ], $b[ $orderby ] );
				}

				return strcasecmp( $b[ $orderby ], $a[ $orderby ] );
			}
		);

		return $items;
	}

	/**
	 * Get a list of columns
	 *
	 * @return array
	 */
	public function get_columns() {

		return array(
			'title'         => esc_html__( 'Title', 'shortcode-scrubber' ),
			'sidebar_label' => esc_html__( 'Widget Area', 'shortcode-scrubber' ),
			'widget_label'  => esc_html__( 'Widget Type', 'shortcode-scrubber' ),
			'shortcodes'    => esc_html__( 'Shortcodes In Use', 'shortcode-scrubber' ),
		);

	}

	/**
	 * Get a list of sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'title' => array( 'title', true ),
		);

	}

	/**
	 * Default callback for column display
	 *
	 * @param array  $item Item
	 * @param string $column_name Column name
	 *
	 * @return null|string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : null;
	}

	/**
	 * Callback for displaying the title column
	 *
	 * @param array $item Item
	 *
	 * @return string
	 */
	protected function column_title( $item ) {

		$actions = [];

		$edit_link = sprintf(
			'%s<span class="in-widget-title">%s</span>',
			esc_html( $item['widget_label'] ),
			empty( $item['title'] ) ? '' : esc_html( ': ' . $item['title'] )
		);

		if ( current_user_can( 'edit_theme_options' ) ) {

			$edit_link = sprintf(
				'<a class="row-title" href="%s">%s</a>',
				esc_url( $item['edit_link'] ),
				$edit_link
			);

			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $item['edit_link'] ),
				esc_html__( 'Edit', 'shortcode-scrubber' )
			);
		}

		return $edit_link . $this->row_actions( $actions );
	}

	/**
	 * Callback for displaying the shortcodes column
	 *
	 * @param array $item Item
	 *
	 * @return string
	 */
	protected function column_shortcodes( $item ) {

		$shortcodes        = [];
		$current_shortcode = filter_input( INPUT_GET, 'filter_shortcode' );

		if ( $current_shortcode ) {

			$shortcodes[] = sprintf(
				'<a href="%s" style="%s">[%s]</a>',
				esc_url( add_query_arg( 'filter_shortcode', $current_shortcode ) ),
				shortcode_exists( $current_shortcode ) ? 'color: inherit;' : 'color: red;',
				esc_html( $current_shortcode )
			);

		} elseif ( isset( $item['shortcodes'] ) && is_array( $item['shortcodes'] ) ) {

			asort( $item['shortcodes'] );

			foreach ( array_unique( $item['shortcodes'] ) as $shortcode ) {

				$shortcodes[] = sprintf(
					'<a href="%s" style="%s">[%s]</a>',
					esc_url( add_query_arg( 'filter_shortcode', $shortcode ) ),
					shortcode_exists( $shortcode ) ? 'color: inherit;' : 'color: red;',
					esc_html( $shortcode )
				);

			}
		}

		return count( $shortcodes ) ? implode( '<br />', $shortcodes ) : '-';

	}

	/**
	 * Generate the table nav above or below the table
	 *
	 * @param string $which Name of table nav
	 */
	protected function display_tablenav( $which ) {

		echo '<div class="tablenav ' . esc_attr( $which ) . '">';

		$this->extra_tablenav( $which );
		$this->pagination( $which );

		echo '<br class="clear" /></div>';

	}

	/**
	 * Display filter dropdowns in the table nav
	 *
	 * @param string $which Name of table nav
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' === $which ) {

			$filters = $this->get_filters();

			?>
			<div class="alignleft actions">

				<input type="hidden" name="page"
						value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>" />

				<label for="filter_widget_area" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Widget Area', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_widget_area" name="filter_widget_area">
					<option value=""><?php esc_html_e( 'Filter By Widget Area', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( get_widget_areas() as $slug => $label ) : ?>
						<option value="<?php echo esc_html( $slug ); ?>"<?php selected( filter_input( INPUT_GET, 'filter_widget_area' ), $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label for="filter_widget_type" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Widget Type', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_widget_type" name="filter_widget_type">
					<option value=""><?php esc_html_e( 'Filter By Widget Type', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( get_widget_types() as $slug => $label ) : ?>
						<option value="<?php echo esc_html( $slug ); ?>"<?php selected( filter_input( INPUT_GET, 'filter_widget_type' ), $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label for="filter_shortcode" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Shortcode', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_shortcode" name="filter_shortcode">
					<option value=""><?php esc_html_e( 'Filter By Shortcode', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( array_keys( get_shortcodes() ) as $shortcode ) : ?>
						<option value="<?php echo esc_html( $shortcode ); ?>" <?php selected( $filters['shortcode'], $shortcode ); ?> >
							[<?php echo esc_html( $shortcode ); ?>]
						</option>
					<?php endforeach; ?>
				</select>

				<input type="submit" id="post-query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'shortcode-scrubber' ); ?>" />

			</div>
			<?php
		} else {
			printf( '<span>*%s</span>', esc_html__( 'Shortcodes displayed in red are not registered in WordPress.', 'shortcode-scrubber' ) );
		}

	}

	/**
	 * Displayed when there are no items found
	 */
	public function no_items() {
		esc_html_e( 'No widgets found.', 'shortcode-scrubber' );
	}

	/**
	 * Display the table.
	 */
	public function display() {
		$filters = $this->get_filters();
		?>
		<form method="get">
			<p class="search-box">
				<label>
					<span class="screen-reader-text"><?php esc_html_e( 'Search by Shortcode', 'shortcode-scrubber' ); ?></span>
					<input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" />
				</label>
				<button class="button"><?php esc_html_e( 'Search by Shortcode', 'shortcode-scrubber' ); ?></button>
			</p>
			<?php parent::display(); ?>
		</form>
		<?php
	}

	/**
	 * Get current filters
	 *
	 * @return array
	 */
	public function get_filters() {

		$search    = trim( filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING ), '[]' );
		$shortcode = filter_input( INPUT_GET, 'filter_shortcode', FILTER_SANITIZE_STRING );

		return array(
			'search'      => $search,
			'shortcode'   => empty( $search ) ? $shortcode : $search,
			'widget_area' => filter_input( INPUT_GET, 'filter_widget_area', FILTER_SANITIZE_STRING ),
			'widget_type' => filter_input( INPUT_GET, 'filter_widget_type', FILTER_SANITIZE_STRING ),
		);
	}

}
