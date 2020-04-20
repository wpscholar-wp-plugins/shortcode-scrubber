<?php
/**
 * List table for displaying posts.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ShortcodePostListTable
 *
 * @package ShortcodeScrubber
 */
class ShortcodePostListTable extends \WP_List_Table {

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
				'singular' => 'post',
				'plural'   => 'posts',
				'ajax'     => false,
			]
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$filters  = $this->get_filters();
		$per_page = $this->get_items_per_page( 'shortcode_scrubber_posts_per_page', 10 );

		$posts = new \WP_Query(
			array(
				'order'          => $filters['order'],
				'orderby'        => $filters['orderby'],
				'paged'          => $this->get_pagenum(),
				'post_type'      => empty( $filters['post_type'] ) ? $this->get_post_types() : $filters['post_type'],
				'posts_per_page' => $per_page,
				's'              => '[' . $filters['shortcode'],
			)
		);

		foreach ( $posts->posts as $post ) {

			$this->items[] = array(
				'date'        => $post,
				'id'          => $post->ID,
				'shortcodes'  => find_shortcode_tags( $post->post_content ),
				'title'       => get_the_title( $post ),
				'post_type'   => get_post_type_object( $post->post_type )->labels->singular_name,
				'post_status' => get_post_status_object( $post->post_status )->label,
			);

		}

		$this->set_pagination_args(
			array(
				'per_page'    => $per_page,
				'total_items' => $posts->found_posts,
			)
		);

	}

	/**
	 * Get a list of columns
	 *
	 * @return array
	 */
	public function get_columns() {

		return array(
			'title'       => esc_html__( 'Title', 'shortcode-scrubber' ),
			'post_type'   => esc_html__( 'Post Type', 'shortcode-scrubber' ),
			'post_status' => esc_html__( 'Post Status', 'shortcode-scrubber' ),
			'shortcodes'  => esc_html__( 'Shortcodes In Use', 'shortcode-scrubber' ),
			'date'        => esc_html__( 'Date', 'shortcode-scrubber' ),
		);

	}

	/**
	 * Get a list of sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return array(
			'title'       => array( 'title', true ),
			'post_type'   => array( 'post_type', false ),
			'post_status' => array( 'post_status', false ),
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
	 * Handles the post date column output
	 *
	 * @param array $item Item
	 */
	public function column_date( $item ) {

		$post = $item['date'];

		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$h_time    = esc_html__( 'Unpublished', 'shortcode-scrubber' );
			$t_time    = $h_time;
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'shortcode-scrubber' ), $post );
			$m_time = $post->post_date;
			$time   = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				/* translators: human time diff */
				$h_time = sprintf( __( '%s ago', 'shortcode-scrubber' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d', 'shortcode-scrubber' ), $m_time );
			}
		}

		if ( 'publish' === $post->post_status ) {
			$status = __( 'Published', 'shortcode-scrubber' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				$status = '<strong class="error-message">' . __( 'Missed schedule', 'shortcode-scrubber' ) . '</strong>';
			} else {
				$status = __( 'Scheduled', 'shortcode-scrubber' );
			}
		} else {
			$status = __( 'Last Modified', 'shortcode-scrubber' );
		}

		$status = apply_filters( 'post_date_column_status', $status, $post, 'date', 'list' );

		if ( $status ) {
			echo esc_html( $status ) . '<br />';
		}

		echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, 'date', 'list' ) ) . '</abbr>';
	}

	/**
	 * Callback for displaying the title column
	 *
	 * @param array $item Item
	 *
	 * @return string
	 */
	protected function column_title( $item ) {

		$actions   = [];
		$edit_link = esc_html( $item['title'] );

		if ( current_user_can( 'edit_post', $item['id'] ) ) {
			$edit_link       = '<a class="row-title" href="' . get_edit_post_link( $item['id'] ) . '">' . esc_html( $item['title'] ) . '</a>';
			$actions['edit'] = '<a href="' . get_edit_post_link( $item['id'] ) . '">' . esc_html__( 'Edit', 'shortcode-scrubber' ) . '</a>';
		}

		$actions['view'] = '<a href="' . get_permalink( $item['id'] ) . '">' . esc_html__( 'View', 'shortcode-scrubber' ) . '</a>';

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

			$filters    = $this->get_filters();
			$post_types = $this->get_post_types();

			?>
			<div class="alignleft actions">

				<input type="hidden" name="page"
						value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>"/>

				<label for="filter_post_type" class="screen-reader-text">
					<?php esc_html_e( 'Filter By Post Type', 'shortcode-scrubber' ); ?>
				</label>
				<select id="filter_post_type" name="filter_post_type">
					<option value=""><?php esc_html_e( 'Filter By Post Type', 'shortcode-scrubber' ); ?></option>
					<?php foreach ( $post_types as $post_type ) : ?>
						<?php $post_type_object = get_post_type_object( $post_type ); ?>
						<?php if ( $post_type_object && is_object( $post_type_object ) ) : ?>
							<option value="<?php echo esc_html( $post_type_object->name ); ?>"<?php selected( filter_input( INPUT_GET, 'filter_post_type' ), $post_type_object->name ); ?>>
								<?php echo esc_html( $post_type_object->labels->singular_name ); ?>
							</option>
						<?php endif; ?>
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

				<input type="submit"
						id="post-query-submit"
						class="button"
						value="<?php esc_attr_e( 'Filter', 'shortcode-scrubber' ); ?>"/>

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
		esc_html_e( 'No posts found.', 'shortcode-scrubber' );
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
					<input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>"/>
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
			'order'     => isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC', // phpcs:ignore WordPress.Security.NonceVerification
			'orderby'   => isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title', // phpcs:ignore WordPress.Security.NonceVerification
			'post_type' => filter_input( INPUT_GET, 'filter_post_type', FILTER_SANITIZE_STRING ),
			'search'    => $search,
			'shortcode' => empty( $search ) ? $shortcode : $search,
		);
	}

	/**
	 * Get post types that can be filtered
	 *
	 * @return array
	 */
	public function get_post_types() {

		$post_types = wp_filter_object_list(
			array_map( 'get_post_type_object', get_post_types_by_support( 'editor' ) ),
			[ 'public' => true ]
		);

		return wp_list_pluck( $post_types, 'name' );
	}

}
