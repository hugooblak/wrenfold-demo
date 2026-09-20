<?php
/**
 * Title: 404 helpful links
 * Slug: wrenfold/404-links
 * Categories: wrenfold
 * Inserter: no
 *
 * Buttons that get lost visitors back on a useful path.
 */
?>
<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the homepage</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/what-we-make/' ) ); ?>">See what we make</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
