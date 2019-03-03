<?php
/**
 * Show registered shortcodes.
 *
 * @package ShortcodeScrubber
 */

namespace ShortcodeScrubber;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Registered Shortcodes', 'shortcode-scrubber' ); ?></h1>
	<?php
	$list = new ShortcodeListTable( [ 'shortcodes' => new ShortcodeCollection() ] );
	$list->prepare_items();
	$list->display();
	?>
</div>
