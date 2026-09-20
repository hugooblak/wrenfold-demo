<?php
/**
 * Title: Why people choose us (dark)
 * Slug: wrenfold/differentiators
 * Categories: wrenfold, featured
 * Keywords: benefits, differentiators, why
 * Viewport Width: 1400
 *
 * Dark section with four short reasons to choose Wrenfold Joinery.
 */

$wrenfold_items = array(
	array( __( 'One team, start to finish', 'wrenfold' ), __( 'The person who measures your room is in the workshop while it is made. Nothing is handed to a subcontractor.', 'wrenfold' ) ),
	array( __( 'Timber we can account for', 'wrenfold' ), __( 'We buy from two mills and keep the delivery notes. Ask where a board came from and we can tell you.', 'wrenfold' ) ),
	array( __( 'A fixed price before we start', 'wrenfold' ), __( 'You get a drawing and a price. If the price changes it is because you asked for a change, and you see it in writing first.', 'wrenfold' ) ),
	array( __( 'We clear up after ourselves', 'wrenfold' ), __( 'Dust sheets down, a vacuum at the end of each day, and the skip is on our bill, not yours.', 'wrenfold' ) ),
);
?>
<!-- wp:group {"align":"full","className":"is-style-section-dark","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-section-dark" style="margin-top:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
<!-- wp:heading -->
<h2 class="wp-block-heading">Why people pick a small workshop</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"is-four-up","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns is-four-up" style="margin-top:var(--wp--preset--spacing--50)">
<?php foreach ( $wrenfold_items as $wrenfold_item ) : ?>
<!-- wp:column {"style":{"border":{"top":{"color":"var:preset|color|accent","width":"3px","style":"solid"}},"spacing":{"padding":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-column" style="border-top-color:var(--wp--preset--color--accent);border-top-style:solid;border-top-width:3px;padding-top:var(--wp--preset--spacing--30)">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $wrenfold_item[0] ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html( $wrenfold_item[1] ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
