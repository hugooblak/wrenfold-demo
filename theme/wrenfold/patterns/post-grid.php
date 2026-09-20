<?php
/**
 * Title: Post grid
 * Slug: wrenfold/post-grid
 * Categories: wrenfold, query
 * Block Types: core/query
 * Keywords: blog, posts, articles
 * Viewport Width: 1400
 *
 * Blog listing: cards in a 3-column grid with category, title, excerpt and date.
 * Inherits the current query, so it works on the blog page, categories and search.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70)">
<!-- wp:query {"queryId":1,"query":{"perPage":9,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":true}} -->
<div class="wp-block-query">
<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"18rem"}} -->
<!-- wp:group {"className":"is-style-card","style":{"dimensions":{"minHeight":"100%"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card" style="min-height:100%">
<!-- wp:post-terms {"term":"category"} /-->
<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"x-large"} /-->
<!-- wp:post-excerpt {"excerptLength":24,"style":{"color":{"text":"var:preset|color|muted"}}} /-->
<!-- wp:post-date {"style":{"spacing":{"margin":{"top":"auto"}}}} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No posts found. Try the FAQs, or search again.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
