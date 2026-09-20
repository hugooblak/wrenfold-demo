<?php
/**
 * Title: Header
 * Slug: wrenfold/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * Site header: logo, main menu (collapses on mobile) and the quote button.
 *
 * The menu is the "Main menu" in Appearance > Editor > Navigation, so the team
 * edits links there. If that menu doesn't exist yet, a default set of links shows.
 */

$wrenfold_menu    = get_page_by_path( 'wrenfold-main-menu', OBJECT, 'wp_navigation' );
$wrenfold_menu_id = $wrenfold_menu ? $wrenfold_menu->ID : 0;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px","style":"solid"}}},"backgroundColor":"base","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-style:solid;border-bottom-width:1px;padding-top:1rem;padding-bottom:1rem">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group">
<!-- wp:group {"style":{"spacing":{"blockGap":"0.6rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group">
<!-- wp:image {"width":"36px","height":"36px","scale":"contain","sizeSlug":"full","linkDestination":"none","className":"wf-logo-mark"} -->
<figure class="wp-block-image size-full is-resized wf-logo-mark"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" style="object-fit:contain;width:36px;height:36px"/></figure>
<!-- /wp:image -->
<!-- wp:site-title {"level":0} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group">
<?php if ( $wrenfold_menu_id ) : ?>
<!-- wp:navigation {"ref":<?php echo (int) $wrenfold_menu_id; ?>,"overlayMenu":"mobile","ariaLabel":"Main","layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} /-->
<?php else : ?>
<!-- wp:navigation {"overlayMenu":"mobile","ariaLabel":"Main","layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
<!-- wp:navigation-link {"label":"What we make","url":"<?php echo esc_url( home_url( '/what-we-make/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"About","url":"<?php echo esc_url( home_url( '/about/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"FAQs","url":"<?php echo esc_url( home_url( '/faqs/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Site health","url":"<?php echo esc_url( home_url( '/site-health-report/' ) ); ?>","kind":"custom"} /-->
<!-- /wp:navigation -->
<?php endif; ?>

<!-- wp:buttons {"className":"wf-header-cta"} -->
<div class="wp-block-buttons wf-header-cta">
<!-- wp:button {"style":{"spacing":{"padding":{"top":"0.6rem","bottom":"0.6rem","left":"1rem","right":"1rem"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="padding-top:0.6rem;padding-right:1rem;padding-bottom:0.6rem;padding-left:1rem">Ask for a quote</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
