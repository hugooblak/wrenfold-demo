<?php
/**
 * Title: Contact options
 * Slug: wrenfold/contact-options
 * Categories: wrenfold, call-to-action
 * Keywords: contact, workshop, visit
 * Viewport Width: 1400
 *
 * Two cards: come to the workshop, or send the room details over.
 */
?>
<!-- wp:group {"align":"full","className":"is-style-section-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-section-surface" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:heading -->
<h2 class="wp-block-heading">Two ways to start</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"is-equal-cards","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"},"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns is-equal-cards" style="margin-top:var(--wp--preset--spacing--40)">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-card","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Come to the workshop</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Unit 4, Wrenfold Yard. Open Tuesday to Friday, 8am to 4pm. There is usually a kitchen on the bench you can open and shut.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}},"fontSize":"small"} -->
<p class="has-text-color has-small-font-size" style="color:var(--wp--preset--color--muted)">The address is made up. This is a demo site.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-card","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Send the room over</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Rough measurements and one photo are enough. We reply within two working days with a price range.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"auto"}}}} -->
<div class="wp-block-buttons" style="margin-top:auto">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask for a quote</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
