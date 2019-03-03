<?php
/**
 * Show shortcode filters.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Shortcode Filters', 'shortcode-scrubber' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( '/admin.php?page=shortcode-scrubber-manage' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New', 'shortcode-scrubber' ); ?>
	</a>
	<?php
	$list = new ShortcodeFilterListTable();
	$list->prepare_items();
	$list->display();
	?>
</div>
