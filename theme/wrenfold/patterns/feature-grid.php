<?php
/**
 * Title: What is included (6 cards)
 * Slug: wrenfold/feature-grid
 * Categories: wrenfold, featured
 * Keywords: features, grid, included
 * Viewport Width: 1400
 *
 * Section heading plus six cards in two rows of three.
 */

$wrenfold_features = array(
	array( __( 'Solid timber doors', 'wrenfold' ), __( 'Oak, ash or painted tulipwood. Doors are made as frames and panels, so they move with the seasons instead of splitting.', 'wrenfold' ) ),
	array( __( 'Drawer boxes, not trays', 'wrenfold' ), __( 'Dovetailed birch boxes on soft-close runners. They hold pans, and they still shut square in ten years.', 'wrenfold' ) ),
	array( __( 'Worktops cut on site', 'wrenfold' ), __( 'Walls are never straight. We scribe the worktop to the wall rather than filling the gap with sealant.', 'wrenfold' ) ),
	array( __( 'Hardware you can replace', 'wrenfold' ), __( 'Standard-size hinges and runners from brands with spare parts, so a broken part is a small job.', 'wrenfold' ) ),
	array( __( 'A drawing before the price', 'wrenfold' ), __( 'You see the layout, the sizes and the timber before you are asked to commit to anything.', 'wrenfold' ) ),
	array( __( 'A visit after fitting', 'wrenfold' ), __( 'Two weeks later we come back and adjust doors and drawers once the room has settled.', 'wrenfold' ) ),
);
$wrenfold_rows = array_chunk( $wrenfold_features, 3 );
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"720px","justifyContent":"left"}} -->
<div class="wp-block-group">
<!-- wp:heading -->
<h2 class="wp-block-heading">What comes as standard</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large","style":{"color":{"text":"var:preset|color|muted"}}} -->
<p class="has-text-color has-large-font-size" style="color:var(--wp--preset--color--muted)">The same six things are in every quote. They are not upgrades and they are not priced separately.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php foreach ( $wrenfold_rows as $wrenfold_row ) : ?>
<!-- wp:columns {"className":"is-equal-cards","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"},"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns is-equal-cards" style="margin-top:var(--wp--preset--spacing--40)">
<?php foreach ( $wrenfold_row as $wrenfold_feature ) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-card","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $wrenfold_feature[0] ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}}} -->
<p class="has-text-color" style="color:var(--wp--preset--color--muted)"><?php echo esc_html( $wrenfold_feature[1] ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
