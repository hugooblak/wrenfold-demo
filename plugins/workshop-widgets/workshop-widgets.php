<?php
/**
 * Plugin Name:       Workshop Widgets
 * Description:       A second pack of extra blocks and a gallery shortcode. Written for this demo. It overlaps with Bench Addons and Timberkit Blocks on purpose, so the plugin audit has something real to find.
 * Version:           2.4.1
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       workshop-widgets
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

function wf_workshop_register_blocks() {
	foreach ( array( 'spec-table', 'price-list' ) as $wf_block ) {
		register_block_type( __DIR__ . '/blocks/' . $wf_block );
	}
}
add_action( 'init', 'wf_workshop_register_blocks' );

function wf_workshop_editor_assets() {
	wp_enqueue_script(
		'wf-workshop-editor',
		plugins_url( 'editor.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
		'2.4.1',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wf_workshop_editor_assets' );

/**
 * Load this pack's stylesheet on every front-end page.
 *
 * Deliberately unconditional. It is what most add-on packs do, and it is the
 * behaviour the Site Triage plugin is meant to catch.
 */
function wf_workshop_front_assets() {
	wp_enqueue_style(
		'wf-workshop-widgets',
		plugins_url( 'assets/workshop-widgets.css', __FILE__ ),
		array(),
		'2.4.1'
	);
}
add_action( 'wp_enqueue_scripts', 'wf_workshop_front_assets' );

function wf_workshop_gallery_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'columns' => 3 ), $atts, 'workshop_gallery' );
	return '<p class="wf-addon-note">' . esc_html(
		sprintf(
			/* translators: %d: number of columns. */
			__( 'Workshop Widgets gallery placeholder, %d columns.', 'workshop-widgets' ),
			(int) $atts['columns']
		)
	) . '</p>';
}
add_shortcode( 'workshop_gallery', 'wf_workshop_gallery_shortcode' );
