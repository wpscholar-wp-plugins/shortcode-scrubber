<?php
/**
 * Form for managing shortcode filters.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

$current_shortcode = filter_input( INPUT_GET, 'shortcode', FILTER_SANITIZE_STRING );
if ( empty( $current_shortcode ) ) {
	$current_shortcode = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
}

$filters         = get_actionable_shortcode_filters( $current_shortcode );
$applied_filters = Options::get( 'applied_filters', [] );

if ( isset( $_POST['apply'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$shortcode       = filter_input( INPUT_POST, 'shortcode', FILTER_SANITIZE_STRING );
	$filter_to_apply = filter_input( INPUT_POST, 'shortcode-filter', FILTER_SANITIZE_STRING );
	if ( $shortcode && $filter_to_apply ) {
		activate_shortcode_filter( $shortcode, $filter_to_apply );
		if ( isset( $_POST['persist'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			freeze_shortcode( $shortcode );
			deactivate_shortcode_filter( $shortcode );
		}
		printf(
			'<div class="notice notice-success"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Filter applied.', 'shortcode-scrubber' ),
			esc_url( admin_url( '/admin.php?page=shortcode-scrubber-filters' ) ),
			esc_html__( 'View all filters', 'shortcode-scrubber' )
		);
	}
} elseif ( isset( $_POST['clear'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$shortcode = filter_input( INPUT_POST, 'shortcode', FILTER_SANITIZE_STRING );
	if ( $shortcode ) {
		deactivate_shortcode_filter( $shortcode );
		unset( $applied_filters[ $shortcode ] );
		Options::set( 'applied_filters', $applied_filters );
		printf(
			'<div class="notice notice-success"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Filter removed.', 'shortcode-scrubber' ),
			esc_url( admin_url( '/admin.php?page=shortcode-scrubber-filters' ) ),
			esc_html__( 'View all filters', 'shortcode-scrubber' )
		);
	}
}

?>
<form id="shortcode-scrubber-manage" method="post">

	<h2>
		<?php
		/* translators: shortcode name */
		printf( esc_html__( 'Shortcode: %s', 'shortcode-scrubber' ), esc_html( '[' . $current_shortcode . ']' ) );
		?>
	</h2>

	<fieldset class="field field-radio-group">

		<legend><?php esc_html_e( 'Select the filter you would like to apply:', 'shortcode-scrubber' ); ?></legend>

		<?php foreach ( $filters as $filter ) : ?>
			<div class="field field-radio">
				<label>
					<input<?php checked( isset( $applied_filters[ $current_shortcode ] ) && $filter->id === $applied_filters[ $current_shortcode ] ); ?> type="radio" name="shortcode-filter" value="<?php echo esc_attr( $filter->id ); ?>" />
					<span><?php echo esc_html( $filter->label ); ?></span>
				</label>
				<?php if ( isset( $filter->description ) ) : ?>
					<p class="field__description"><?php echo esc_html( $filter->description ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

	</fieldset>

	<a href="#advanced-options" onclick="shortcodeScrubberToggleElement('advanced-options')">
		<?php esc_html_e( 'Advanced Options', 'shortcode-scrubber' ); ?>
	</a>

	<div id="advanced-options" style="display:none;">
		<div class="field">
			<label>
				<input type="checkbox" name="persist" value="true" />
				<span><?php esc_html_e( 'Apply this filter permanently', 'shortcode-scrubber' ); ?></span>
			</label>
			<p class="field__description danger"><?php esc_html_e( 'This operation is irreversible! By selecting this option, you verify that you have a recent backup of your database.', 'shortcode-scrubber' ); ?></p>
		</div>
	</div>

	<p>
		<?php submit_button( esc_html__( 'Apply', 'shortcode-scrubber' ), 'primary', 'apply', false ); ?>
		<?php submit_button( esc_html__( 'Clear', 'shortcode-scrubber' ), 'secondary', 'clear', false ); ?>
		<a class="alignright" href="<?php echo esc_url( add_query_arg( 'page', 'shortcode-scrubber-manage', admin_url( 'admin.php' ) ) ); ?>">
			<?php esc_html_e( 'Cancel', 'shortcode-scrubber' ); ?>
		</a>
	</p>

	<input type="hidden" name="shortcode" value="<?php echo esc_attr( $current_shortcode ); ?>" />

</form>

<style>
	#shortcode-scrubber-manage {
		background: white;
		padding: 1em 2em;
		margin: 1em 0;
		border: 1px solid #ccc;
	}

	#shortcode-scrubber-manage .danger {
		color: red !important;
	}

	#shortcode-scrubber-manage legend {
		font-weight: bold;
	}

	#shortcode-scrubber-manage .field {
		display: block;
		margin: 1em 0;
	}

	#shortcode-scrubber-manage .field-radio {
		margin-left: 1em;
	}

	#shortcode-scrubber-manage .field__description {
		font-style: italic;
		color: #666;
		margin: .25em 0 0 1.8em;
	}
</style>

<script>
	function shortcodeScrubberToggleElement(id) {
		const el = document.getElementById(id);
		let display = el.style.display;
		if ('none' === display) {
			el.style.display = 'block';
		} else {
			el.style.display = 'none';
		}
	}
</script>
