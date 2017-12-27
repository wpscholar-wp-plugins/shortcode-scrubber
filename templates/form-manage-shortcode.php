<?php

namespace ShortcodeScrubber;

$current_shortcode = filter_input( INPUT_GET, 'shortcode', FILTER_SANITIZE_STRING );

?>
<style>
    #shortcode-scrubber-manage {
        background: white;
        padding: 1em 2em;
        margin: 1em 0;
        border: 1px solid #ccc;
    }

    #shortcode-scrubber-manage legend {
        font-weight: bold;
    }

    #shortcode-scrubber-manage textarea {
        width: 100%;
        min-height: 150px;
    }

    #shortcode-scrubber-manage .field {
        display: block;
        margin: 1em 0;
    }

    #shortcode-scrubber-manage .field__description {
        font-style: italic;
        color: #666;
    }

    #shortcode-scrubber-manage .field-radio-group label {
        display: block;
        margin: .5em 0;
    }
</style>
<form id="shortcode-scrubber-manage" method="post">

    <h2>
        Shortcode: <?php echo esc_html( '[' . $current_shortcode . ']' ); ?>
    </h2>

    <fieldset class="field field-radio-group">
        <legend>What would you like to do with this shortcode?</legend>
        <label class="field-radio">
            <input type="radio" name="action" value="remove" />
            <span class="field__label-text"><?php esc_html_e( 'Remove all instances of this shortcode', 'shortcode-scrubber' ); ?></span>
        </label>
        <label class="field-radio">
            <input type="radio" name="action" value="replace" />
            <span class="field__label-text"><?php esc_html_e( 'Replace the shortcode output', 'shortcode-scrubber' ); ?></span>
        </label>
    </fieldset>

    <fieldset class="field field-radio-group">
        <legend>How do you want to handle the shortcode content?</legend>
        <label class="field-radio">
            <input type="radio" name="content" value="keep" />
            <span class="field__label-text"><?php esc_html_e( 'Keep the content', 'shortcode-scrubber' ); ?></span>
        </label>
        <label class="field-radio">
            <input type="radio" name="content" value="remove" />
            <span class="field__label-text"><?php esc_html_e( 'Remove the content', 'shortcode-scrubber' ); ?></span>
        </label>
        <p class="description">
            This only applies to shortcodes that have a start and end tag. The content is the text between the start and
            end tags. When in doubt, keep the content.
        </p>
    </fieldset>

    <fieldset class="field field-radio-group">
        <legend>What type of replacement would you like to do?</legend>
        <label class="field-radio">
            <input type="radio" name="replace" value="generated" />
            <span class="field__label-text"><?php esc_html_e( 'Replace with generated output', 'shortcode-scrubber' ); ?></span>
        </label>
        <label class="field-radio">
            <input type="radio" name="replace" value="static" />
            <span class="field__label-text"><?php esc_html_e( 'Replace with static output', 'shortcode-scrubber' ); ?></span>
        </label>
        <label class="field-radio">
            <input type="radio" name="replace" value="dynamic" />
            <span class="field__label-text"><?php esc_html_e( 'Replace with dynamic output', 'shortcode-scrubber' ); ?></span>
        </label>
    </fieldset>

    <fieldset class="field field-radio-group">
        <legend>How would you like your changes to persist?</legend>
        <label class="field-radio">
            <input type="radio" name="permanence" value="" />
            <span class="field__label-text"><?php esc_html_e( 'Temporary', 'shortcode-scrubber' ); ?></span>
            <span class="field__description"> - The safe option. Content is unchanged, except when displayed. Can be undone at any time.</span>
        </label>
        <label class="field-radio">
            <input type="radio" name="" value="" />
            <span class="field__label-text"><?php esc_html_e( 'Permanent', 'shortcode-scrubber' ); ?></span>
            <span class="field__description"> - The risky option. Content is permanently changed. Cannot be undone. A database backup is recommended.</span>
        </label>
    </fieldset>


    <!-- <div class="field field-radio">
        <label>
            <input type="radio" name="action" value="delete" />
            <span class="field__label-text"><?php /*esc_html_e( 'Delete', 'shortcode-scrubber' ); */
	?></span>
            <span class="field__description">Permanently remove all instances of this shortcode</span>
        </label>
    </div>-->

    <!-- <div class="field">
		 <label>
			 <input type="checkbox" name="actions[]" value="strip-content" />
			 <span class="field__label-text">Strip Content</span>
		 </label>
	 </div>-->

    <p>
		<?php submit_button( esc_html__( 'Submit', 'shortcode-scrubber' ), 'primary', 'submit', false ); ?>
        <a class="button button-secondary"
           href="<?php echo esc_url( add_query_arg( 'page', 'shortcode-scrubber-actions', admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Cancel', 'shortcode-scrubber' ); ?></a>
    </p>

</form>