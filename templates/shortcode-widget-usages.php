<?php
/**
 * Show widgets using shortcodes.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Widgets Using Shortcodes', 'shortcode-scrubber' ); ?></h1>
	<?php
	$list = new ShortcodeWidgetListTable();
	$list->prepare_items();
	$list->display();
	?>
</div>
