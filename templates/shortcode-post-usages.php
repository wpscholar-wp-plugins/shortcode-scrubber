<?php
/**
 * Show posts using shortcodes.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Posts Using Shortcodes', 'shortcode-scrubber' ); ?></h1>
	<?php
	$list = new ShortcodePostListTable();
	$list->prepare_items();
	$list->display();
	?>
</div>
