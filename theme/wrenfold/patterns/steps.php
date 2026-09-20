<?php
/**
 * Title: How a job runs (3 steps)
 * Slug: wrenfold/steps
 * Categories: wrenfold, featured
 * Keywords: steps, process, how it works
 * Viewport Width: 1400
 *
 * Three numbered steps on a warm background.
 */

$wrenfold_steps = array(
	array( __( 'Measure and draw', 'wrenfold' ), __( 'We visit, measure the room and send a drawing with a price. There is no charge for this and no pressure to book.', 'wrenfold' ) ),
	array( __( 'Make it', 'wrenfold' ), __( 'Your job gets a bench and a start date. We send photos as it comes together, so nothing is a surprise on fitting day.', 'wrenfold' ) ),
	array( __( 'Fit it', 'wrenfold' ), __( 'Two fitters, dust sheets down, and a visit two weeks later to sort anything that has moved or sticks.', 'wrenfold' ) ),
);
?>
<!-- wp:group {"align":"full","className":"is-style-section-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-section-surface" style="margin-top:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
<!-- wp:heading -->
<h2 class="wp-block-heading">How a job runs</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--50)">
<?php foreach ( $wrenfold_steps as $wrenfold_n => $wrenfold_step ) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"is-step-number"} -->
<p class="is-step-number"><?php echo (int) $wrenfold_n + 1; ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $wrenfold_step[0] ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html( $wrenfold_step[1] ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
