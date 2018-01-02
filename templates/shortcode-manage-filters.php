<?php

namespace ShortcodeScrubber;

$current_shortcode = filter_input( INPUT_GET, 'shortcode', FILTER_SANITIZE_STRING );
if ( empty( $current_shortcode ) ) {
	$current_shortcode = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Manage Shortcode Filters', 'shortcode-scrubber' ); ?></h1>
	<?php
	$template = $current_shortcode ? 'form-manage-shortcode.php' : 'form-select-shortcode.php';
	require SHORTCODE_SCRUBBER_DIR . '/templates/' . $template;
	?>
</div>