<?php
/**
 * Title: FAQs page layout
 * Slug: wrenfold/page-faqs
 * Categories: wrenfold-pages
 * Post Types: page
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Questions grouped by topic. Each group is an FAQ list block, so the team adds
 * and reorders questions under FAQs in the admin, not in this page.
 */
?>
<!-- wp:group {"align":"full","className":"is-style-section-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-section-surface" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"760px","justifyContent":"left"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"is-eyebrow","style":{"color":{"text":"var:preset|color|primary"}}} -->
<p class="is-eyebrow has-text-color" style="color:var(--wp--preset--color--primary)">Common questions</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Questions we get asked every week</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">If your question is not here, send it over. We add the good ones to this page.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:heading {"level":2,"anchor":"quotes-and-prices"} -->
<h2 class="wp-block-heading" id="quotes-and-prices">Quotes and prices</h2>
<!-- /wp:heading -->
<!-- wp:wrenfold/faq-list {"topic":"quotes-and-prices","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} /-->
<!-- wp:heading {"level":2,"anchor":"timings","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading" id="timings" style="margin-top:var(--wp--preset--spacing--50)">Timings</h2>
<!-- /wp:heading -->
<!-- wp:wrenfold/faq-list {"topic":"timings","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} /-->
<!-- wp:heading {"level":2,"anchor":"materials-and-care","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading" id="materials-and-care" style="margin-top:var(--wp--preset--spacing--50)">Materials and looking after them</h2>
<!-- /wp:heading -->
<!-- wp:wrenfold/faq-list {"topic":"materials-and-care","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} /-->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"wrenfold/contact-options"} /-->
