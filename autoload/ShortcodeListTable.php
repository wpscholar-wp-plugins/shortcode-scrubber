<?php
/**
 * List table for displaying shortcodes.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ShortcodeListTable
 *
 * @package ShortcodeScrubber
 */
class ShortcodeListTable extends \WP_List_Table {

	/**
	 * Collection of shortcodes.
	 *
	 * @var ShortcodeCollection
	 */
	protected $collection;

	/**
	 * Collection of items.
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * ShortcodeListTable constructor.
	 *
	 * @param array $args Arguments
	 *
	 * @throws \InvalidArgumentException If shortcodes argument isn't set.
	 */
	public function __construct( $args = [] ) {

		if ( ! isset( $args['shortcodes'] ) ) {
			throw new \InvalidArgumentException( 'Must pass a ShortcodeCollection to $args["shortcodes"]' );
		}

		parent::__construct(
			array_merge(
				$args,
				[
					'singular' => 'shortcode',
					'plural'   => 'shortcodes',
				]
			)
		);

	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$items = [];

		$this->collection      = $this->_args['shortcodes'];
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		foreach ( $this->collection as $shortcode ) {
			$items[] = array(
				'tag'      => $shortcode->getTag(),
				'context'  => $shortcode->getContext(),
				'provider' => $shortcode->getProvider(),
			);
		}

		$items = $this->filter( $items );
		$items = $this->sort( $items );

		$per_page = $this->get_items_per_page( 'shortcode_scrubber_items_per_page', 10 );
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
	 * Get a list of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'tag'      => esc_html__( 'Shortcode', 'shortcode-scrubber' ),
			'context'  => esc_html__( 'Context', 'shortcode-scrubber' ),
			'provider' => esc_html__( 'Provider', 'shortcode-scrubber' ),
		);
	}

	/**
	 * Get a list of sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'tag'      => array( 'tag', true ),
			'context'  => array( 'context', false ),
			'provider' => array( 'provider', false ),
		);
	}

	/**
	 * Sort items
	 *
	 * @param array $items Items to be sorted
	 *
	 * @return array
	 */
	public function sort( $items ) {

		$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'asc' ] ] );
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING, [ 'options' => [ 'default' => 'tag' ] ] );

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
	public function filter( $items ) {

		$filters = $this->get_filters();

		return array_filter(
			$items,
			function ( $item ) use ( $filters ) {

				$valid = true;

				// Filter by context
				if ( ! empty( $filters['context'] ) ) {

					if ( $item['context'] !== $filters['context'] ) {
						$valid = false;
					}

					if ( 'theme' === $filters['context'] && 0 === strpos( $item['context'], 'theme' ) ) {
						$valid = true;
					}
				}

				// Filter by provider
				if ( ! empty( $filters['provider'] ) && $item['provider'] !== $filters['provider'] ) {
					$valid = false;
				}

				return $valid;

			}
		);
	}

	/**
	 * Default callback for column display
	 *
	 * @param array  $item Item
	 * @param string $column_name Column name
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '-';
	}

	/**
	 * Callback for displaying the shortcode context column
	 *
	 * @param array $item Item
	 *
	 * @return string
	 */
	protected function column_context( $item ) {
		switch ( $item['context'] ) {
			case 'theme-child':
				return esc_html__( 'Theme (Child)', 'shortcode-scrubber' );
			case 'theme-parent':
				return esc_html__( 'Theme (Parent)', 'shortcode-scrubber' );
			default:
				return ucfirst( $item['context'] );
		}
	}

	/**
	 * Callback for displaying the shortcode tag column
	 *
	 * @param array $item Item
	 *
	 * @return string
	 */
	protected function column_tag( $item ) {

		$manage_url = add_query_arg(
			[
				'page'      => 'shortcode-scrubber-manage',
				'shortcode' => $item['tag'],
			],
			admin_url( 'admin.php' )
		);

		$post_usage_url = add_query_arg(
			[
				'page'             => 'shortcode-scrubber-post-usages',
				'filter_shortcode' => $item['tag'],
			],
			admin_url( 'admin.php' )
		);

		$widget_usage_url = add_query_arg(
			[
				'page'             => 'shortcode-scrubber-widget-usages',
				'filter_shortcode' => $item['tag'],
			],
			admin_url( 'admin.php' )
		);

		$actions = [
			'manage'       => '<a href="' . esc_url( $manage_url ) . '">' . esc_html__( 'Manage', 'shortcode-scrubber' ) . '</a>',
			'find_posts'   => '<a href="' . esc_url( $post_usage_url ) . '">' . esc_html__( 'Find Post Usages', 'shortcode-scrubber' ) . '</a>',
			'find_widgets' => '<a href="' . esc_url( $widget_usage_url ) . '">' . esc_html__( 'Find Widget Usages', 'shortcode-scrubber' ) . '</a>',
			// 'docs' => '<a href="' . esc_url( ... ) . '">' . esc_html__( 'Documentation', 'shortcode-scrubber' ) . '</a>',
		];

		return esc_html( '[' . $item['tag'] . ']' ) . $this->row_actions( $actions );
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		esc_html_e( 'No shortcodes found.', 'shortcode-scrubber' );
	}

	/**
	 * Get a list of contexts for filtering purposes
	 *
	 * @return array
	 */
	protected function get_contexts() {
		return array(
			'core'   => esc_html__( 'Core', 'shortcode-scrubber' ),
			'plugin' => esc_html__( 'Plugin', 'shortcode-scrubber' ),
			'theme'  => esc_html__( 'Theme', 'shortcode-scrubber' ),
		);
	}

	/**
	 * Get a list of providers for filtering purposes
	 *
	 * @return array
	 */
	protected function get_providers() {
		$providers = [];
		foreach ( $this->collection as $shortcode ) {
			$providers[] = $shortcode->getProvider();
		}

		$providers = array_unique( $providers );
		sort( $providers, SORT_NATURAL | SORT_FLAG_CASE );

		return $providers;
	}

	/**
	 * Get current filters
	 *
	 * @return array
	 */
	protected function get_filters() {
		return array(
			'context'  => filter_input( INPUT_GET, 'filter_context', FILTER_SANITIZE_STRING ),
			'provider' => filter_input( INPUT_GET, 'filter_provider', FILTER_SANITIZE_STRING ),
		);
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
	 * Display filter dropdowns in table nav
	 *
	 * @param string $which Name of table nav
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' !== $which ) {
			return;
		}

		$filters = $this->get_filters();
		?>
		<div class="alignleft actions">
			<form method="get">

				<input type="hidden" name="page"
						value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>" />

				<label for="filter_context" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Context', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_context" name="filter_context">
					<option value=""><?php esc_html_e( 'Filter By Context', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( $this->get_contexts() as $value => $label ) : ?>
						<option value="<?php echo esc_html( $value ); ?>" <?php selected( $filters['context'], $value ); ?> >
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label for="filter_provider" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Provider', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_provider" name="filter_provider">
					<option value=""><?php esc_html_e( 'Filter By Provider', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( $this->get_providers() as $value ) : ?>
						<option value="<?php echo esc_html( $value ); ?>" <?php selected( $filters['provider'], $value ); ?> >
							<?php echo esc_html( $value ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<input type="submit"
						id="post-query-submit"
						class="button"
						value="<?php esc_attr_e( 'Filter', 'shortcode-scrubber' ); ?>" />

			</form>
		</div>
		<?php
	}

}
