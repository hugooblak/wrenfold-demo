<?php
/**
 * Wrenfold Joinery theme functions.
 *
 * Kept deliberately short. Layout, colours and fonts live in theme.json,
 * page sections live in /patterns, and the FAQ library is a separate plugin
 * (so FAQs survive a future theme change).
 *
 * @package WrenfoldJoinery
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the small stylesheet (focus styles, form styles) on the site and in the editor.
 */
function wrenfold_enqueue_styles() {
	$theme = wp_get_theme();
	wp_enqueue_style( 'wrenfold', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'wrenfold_enqueue_styles' );

function wrenfold_setup() {
	add_editor_style( 'style.css' );

	// Lets editors write a short summary for pages. Used for the meta description.
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'after_setup_theme', 'wrenfold_setup' );

/**
 * Group the theme's patterns under one "Wrenfold Joinery" heading in the inserter.
 */
function wrenfold_pattern_categories() {
	register_block_pattern_category( 'wrenfold', array( 'label' => __( 'Wrenfold Joinery sections', 'wrenfold' ) ) );
	register_block_pattern_category( 'wrenfold-pages', array( 'label' => __( 'Wrenfold Joinery page layouts', 'wrenfold' ) ) );
}
add_action( 'init', 'wrenfold_pattern_categories' );

/**
 * Only show this theme's patterns, not the remote pattern directory.
 * Keeps the inserter focused on on-brand sections.
 */
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Preload the one font file so text renders in the right font without a flash.
 */
function wrenfold_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/schibsted-grotesk-latin-wght-normal.woff2' ) )
	);
}
add_action( 'wp_head', 'wrenfold_preload_font', 1 );

/**
 * Use the logo mark as the browser tab icon, until the team uploads a
 * Site Icon in Appearance > Editor > Styles or Settings.
 */
function wrenfold_default_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ) );
}
add_action( 'wp_head', 'wrenfold_default_favicon', 3 );

/**
 * Remove the emoji detection script and styles WordPress adds to every page.
 * Modern browsers show emoji natively, so this is dead weight.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Contact Form 7 loads its script and styles on every page by default.
 * Only load them on pages that actually contain a form.
 */
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

function wrenfold_load_form_assets() {
	if ( ! function_exists( 'wpcf7_enqueue_scripts' ) || ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post && ( has_block( 'contact-form-7/contact-form-selector', $post ) || has_shortcode( $post->post_content, 'contact-form-7' ) ) ) {
		wpcf7_enqueue_scripts();
		wpcf7_enqueue_styles();
	}
}
add_action( 'wp_enqueue_scripts', 'wrenfold_load_form_assets', 20 );

/**
 * Basic meta description.
 *
 * Uses the page or post excerpt, or the site tagline on the front page.
 * If the team later installs a full SEO plugin, this steps aside automatically.
 */
function wrenfold_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		return;
	}

	$description = '';
	if ( is_front_page() ) {
		$description = has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' );
	} elseif ( is_singular() ) {
		$description = get_the_excerpt();
	} elseif ( is_home() ) {
		$description = get_the_excerpt( get_option( 'page_for_posts' ) );
	} elseif ( is_category() ) {
		$description = wp_strip_all_tags( term_description() );
	}

	$description = trim( wp_strip_all_tags( $description ) );
	if ( '' === $description ) {
		return;
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_trim_words( $description, 30, '…' ) ) );
}
add_action( 'wp_head', 'wrenfold_meta_description', 2 );
