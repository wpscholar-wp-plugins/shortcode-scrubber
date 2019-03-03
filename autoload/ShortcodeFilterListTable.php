<?php
/**
 * List table for displaying shortcode filters.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ShortcodeFilterListTable
 *
 * @package ShortcodeScrubber
 */
class ShortcodeFilterListTable extends \WP_List_Table {

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
				'singular' => 'filter',
				'plural'   => 'filters',
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$items = [];

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$shortcode_filters = get_shortcode_filters();
		$applied_filters   = (array) Options::get( 'applied_filters', [] );

		foreach ( $applied_filters as $shortcode => $id ) {
			if ( isset( $shortcode_filters[ $id ] ) ) {
				$items[] = [
					'shortcode'          => $shortcode,
					'filter'             => $shortcode_filters[ $id ],
					'filter_id'          => $id,
					'filter_label'       => isset( $shortcode_filters[ $id ]->label ) ? $shortcode_filters[ $id ]->label : '',
					'filter_description' => isset( $shortcode_filters[ $id ]->description ) ? $shortcode_filters[ $id ]->description : '',
				];
			}
		}

		$items = $this->filter( $items );
		$items = $this->sort( $items );

		$per_page = $this->get_items_per_page( 'shortcode_scrubber_filters_per_page', 10 );
		$offset   = ( $this->get_pagenum() - 1 ) * $per_page;

		$this->set_pagination_args(
			array(
				'per_page'    => $per_page,
				'total_items' => count( $items ),
			)
		);

		$this->items = array_slice( $items, $offset, $per_page );

	}

	/**
	 * Sort items
	 *
	 * @param array $items Items to be sorted
	 *
	 * @return array
	 */
	public function sort( array $items ) {

		$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'asc' ] ] );
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'title' ] ] );

		if ( ! array_key_exists( $orderby, $this->get_columns() ) ) {
			$orderby = $this->get_default_primary_column_name();
		}

		usort(
			$items,
			function ( $a, $b ) use ( $order, $orderby ) {

				if ( 'asc' === $order ) {
					return strcasecmp( $a[ $orderby ], $b[ $orderby ] );
				}

				return strcasecmp( $b[ $orderby ], $a[ $orderby ] );
			}
		);

		return $items;
	}

	/**
	 * Filter items
	 *
	 * @param array $items Items to be filtered
	 *
	 * @return array
	 */
	public function filter( array $items ) {

		$filters = $this->get_filters();

		return array_filter(
			$items,
			function ( $item ) use ( $filters ) {
				$valid = true;

				// Filter by shortcode
				if ( ! empty( $filters['shortcode'] ) && $item['shortcode'] !== $filters['shortcode'] ) {
					$valid = false;
				}

				// Filter by filter
				if ( ! empty( $filters['filter'] ) && $item['filter_id'] !== $filters['filter'] ) {
					$valid = false;
				}

				return $valid;
			}
		);
	}

	/**
	 * Get a list of columns
	 *
	 * @return array
	 */
	public function get_columns() {

		return array(
			'shortcode'          => esc_html__( 'Shortcode', 'shortcode-scrubber' ),
			'filter_label'       => esc_html__( 'Filter', 'shortcode-scrubber' ),
			'filter_description' => esc_html__( 'Description', 'shortcode-scrubber' ),
		);

	}

	/**
	 * Get a list of sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'shortcode' => array( 'shortcode', true ),
		);

	}

	/**
	 * Default callback for column display
	 *
	 * @param array  $item Items
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
	protected function column_shortcode( $item ) {

		$actions = [
			'edit'         => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( '/admin.php?page=shortcode-scrubber-manage&shortcode=' . $item['shortcode'] ) ),
				esc_html__( 'Manage', 'shortcode-scrubber' )
			),
			'find_posts'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( '/admin.php?page=shortcode-scrubber-post-usages&filter_shortcode=' . $item['shortcode'] ) ),
				esc_html__( 'Find Post Usages', 'shortcode-scrubber' )
			),
			'find_widgets' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( '/admin.php?page=shortcode-scrubber-widget-usages&filter_shortcode=' . $item['shortcode'] ) ),
				esc_html__( 'Find Widget Usages', 'shortcode-scrubber' )
			),
			'delete'       => sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( '/admin.php?page=shortcode-scrubber-filters&action=delete&shortcode=' . $item['shortcode'] ) ),
				esc_html__( 'Delete', 'shortcode-scrubber' )
			),
		];

		$edit_link = sprintf(
			'<a href="%s" style="%s">%s</a>',
			esc_url( admin_url( '/admin.php?page=shortcode-scrubber-manage&shortcode=' . $item['shortcode'] ) ),
			shortcode_exists( $item['shortcode'] ) ? 'color: inherit;' : 'color: red;',
			esc_html( "[{$item['shortcode']}]" )
		);

		return $edit_link . $this->row_actions( $actions );
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

			$shortcodes        = array_keys( Options::get( 'applied_filters', [] ) );
			$shortcode_filters = get_shortcode_filters();

			asort( $shortcodes );
			asort( $shortcode_filters );

			?>
			<div class="alignleft actions">

				<input type="hidden" name="page"
						value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>" />

				<label for="filter_shortcode" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Shortcode', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_shortcode" name="filter_shortcode">
					<option value=""><?php esc_html_e( 'Filter By Shortcode', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( $shortcodes as $shortcode ) : ?>
						<option value="<?php echo esc_html( $shortcode ); ?>" <?php selected( $filters['shortcode'], $shortcode ); ?> >
							[<?php echo esc_html( $shortcode ); ?>]
						</option>
					<?php endforeach; ?>
				</select>

				<label for="filter_shortcode_filter" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Widget Area', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_shortcode_filter" name="filter_shortcode_filter">
					<option value=""><?php esc_html_e( 'Filter By Shortcode Filter', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( $shortcode_filters as $shortcode_filter ) : ?>
						<option value="<?php echo esc_html( $shortcode_filter->id ); ?>"<?php selected( filter_input( INPUT_GET, 'filter_shortcode_filter' ), $shortcode_filter->id ); ?>>
							<?php echo esc_html( $shortcode_filter->label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<input type="submit"
						id="post-query-submit"
						class="button"
						value="<?php esc_attr_e( 'Filter', 'shortcode-scrubber' ); ?>" />

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
		esc_html_e( 'No shortcode filters found.', 'shortcode-scrubber' );
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
			'search'    => $search,
			'shortcode' => empty( $search ) ? $shortcode : $search,
			'filter'    => filter_input( INPUT_GET, 'filter_shortcode_filter', FILTER_SANITIZE_STRING ),
		);
	}

}
