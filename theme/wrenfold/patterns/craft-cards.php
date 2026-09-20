<?php
/**
 * Title: Three things we make
 * Slug: wrenfold/craft-cards
 * Categories: wrenfold, call-to-action
 * Keywords: services, kitchens, staircases, shopfitting
 * Viewport Width: 1400
 *
 * Three cards, one per kind of work, each linking to the same page anchor.
 */

$wrenfold_cards = array(
	array(
		'icon'  => 'icon-kitchen.svg',
		'label' => __( 'Kitchens', 'wrenfold' ),
		'title' => __( 'Fitted kitchens', 'wrenfold' ),
		'text'  => __( 'Built to the room you have, not to a catalogue size. Solid doors, drawer boxes that stay square, and a worktop cut to the wall.', 'wrenfold' ),
		'anchor' => '/what-we-make/#kitchens',
	),
	array(
		'icon'  => 'icon-stairs.svg',
		'label' => __( 'Staircases', 'wrenfold' ),
		'title' => __( 'Stairs and handrails', 'wrenfold' ),
		'text'  => __( 'Straight flights, winders and handrails. We template on site, so treads meet the wall without packing pieces behind them.', 'wrenfold' ),
		'anchor' => '/what-we-make/#staircases',
	),
	array(
		'icon'  => 'icon-shopfit.svg',
		'label' => __( 'Shopfitting', 'wrenfold' ),
		'title' => __( 'Counters and shop interiors', 'wrenfold' ),
		'text'  => __( 'Serving counters, display units and back-of-house storage for small shops and cafes. Fitted out of hours where the lease allows it.', 'wrenfold' ),
		'anchor' => '/what-we-make/#shopfitting',
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Three kinds of work</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"is-equal-cards","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns is-equal-cards" style="margin-top:var(--wp--preset--spacing--50)">
<?php foreach ( $wrenfold_cards as $wrenfold_card ) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"is-style-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
<!-- wp:image {"width":"56px","height":"56px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $wrenfold_card['icon'] ) ); ?>" alt="" style="width:56px;height:56px"/></figure>
<!-- /wp:image -->
<!-- wp:paragraph {"className":"is-eyebrow","style":{"color":{"text":"var:preset|color|primary"}}} -->
<p class="is-eyebrow has-text-color" style="color:var(--wp--preset--color--primary)"><?php echo esc_html( $wrenfold_card['label'] ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"fontSize":"x-large"} -->
<h3 class="wp-block-heading has-x-large-font-size"><?php echo esc_html( $wrenfold_card['title'] ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html( $wrenfold_card['text'] ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"},"spacing":{"margin":{"top":"auto"}}}} -->
<p style="margin-top:auto;font-weight:600"><a href="<?php echo esc_url( home_url( $wrenfold_card['anchor'] ) ); ?>"><?php /* translators: %s: kind of work, e.g. Kitchens. */ printf( esc_html__( 'More about %s', 'wrenfold' ), esc_html( strtolower( $wrenfold_card['label'] ) ) ); ?></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
