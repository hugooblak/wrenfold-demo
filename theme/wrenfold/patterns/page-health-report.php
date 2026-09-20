<?php
/**
 * Title: Site health report page layout
 * Slug: wrenfold/page-health-report
 * Categories: wrenfold-pages
 * Post Types: page
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * The public version of the Site Triage scan. Same numbers the admin screens show,
 * written for someone who does not work on websites.
 */
?>
<!-- wp:group {"align":"full","className":"is-style-section-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-section-surface" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"760px","justifyContent":"left"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"is-eyebrow","style":{"color":{"text":"var:preset|color|primary"}}} -->
<p class="is-eyebrow has-text-color" style="color:var(--wp--preset--color--primary)">Site health</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">What the last scan of this site found</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Every number below was read from this WordPress install by the last scan, on the date shown. None of it is typed in by hand.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|30"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:paragraph -->
<p>This site was set up with problems on purpose, so the scan has something real to find: a stray PHP file in the uploads folder, a scheduled job whose code no longer exists, an extra administrator account, two add-on plugins nobody uses, a stylesheet that is asked for but not there, and the old homepage kept the way a migration usually keeps it.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>The same scan runs on a real site without any of that being planted. It reads what is actually there. The full version of these screens, with the evidence behind each line and a fix button beside the ones that can be fixed safely, is in the WordPress admin under Site Triage.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1140px"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:wrenfold/health-report /-->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"wrenfold/cta-banner"} /-->
