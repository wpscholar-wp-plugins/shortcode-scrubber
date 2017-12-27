<?php

namespace ShortcodeScrubber;

$current_shortcode = filter_input( INPUT_GET, 'shortcode', FILTER_SANITIZE_STRING );

?>
<form method="get">

    <input type="hidden" name="page"
           value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>" />

    <p>
        <label>
            <span class="screen-reader-text"><?php esc_html_e( 'Shortcode', 'shortcode-scrubber' ); ?></span>
            <select name="shortcode">
                <option value=""><?php esc_html_e( 'Select a Shortcode', 'shortcode-scrubber' ); ?></option>
				<?php foreach ( array_keys( get_shortcodes() ) as $shortcode ): ?>
                    <option value="<?php echo esc_attr( $shortcode ); ?>"<?php selected( $current_shortcode, $shortcode ); ?>>
						<?php echo esc_html( '[' . $shortcode . ']' ); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </label>
    </p>

	<?php submit_button( esc_html__( 'Continue', 'shortcode-scrubber' ), 'primary', '' ); ?>

</form>