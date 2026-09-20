<?php
/**
 * Title: Latest posts (3)
 * Slug: wrenfold/latest-posts
 * Categories: wrenfold, query
 * Keywords: blog, news, latest
 * Viewport Width: 1400
 *
 * The three newest posts with a link to the blog. Updates itself when you publish.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group">
<!-- wp:heading -->
<h2 class="wp-block-heading">Notes from the workshop</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
<p style="font-weight:600"><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">See all posts</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":2,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-query" style="margin-top:var(--wp--preset--spacing--40)">
<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"18rem"}} -->
<!-- wp:group {"className":"is-style-card","style":{"dimensions":{"minHeight":"100%"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card" style="min-height:100%">
<!-- wp:post-terms {"term":"category"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"x-large"} /-->
<!-- wp:post-excerpt {"excerptLength":20,"style":{"color":{"text":"var:preset|color|muted"}}} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
