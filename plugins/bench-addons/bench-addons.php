<?php
/**
 * Plugin Name:       Bench Addons
 * Description:       A small pack of extra blocks and one shortcode. Written for this demo so the Site Triage plugin has a real add-on pack to audit. Two of the three packs on this site overlap with it on purpose.
 * Version:           1.2.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       bench-addons
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register this pack's blocks from their block.json files.
 */
function wf_bench_register_blocks() {
	foreach ( array( 'spec-table', 'team-card' ) as $wf_block ) {
		register_block_type( __DIR__ . '/blocks/' . $wf_block );
	}
}
add_action( 'init', 'wf_bench_register_blocks' );

/**
 * Editor script: registers the same blocks on the JavaScript side so they
 * appear in the inserter and preview correctly.
 */
function wf_bench_editor_assets() {
	wp_enqueue_script(
		'wf-bench-editor',
		plugins_url( 'editor.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
		'1.2.0',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wf_bench_editor_assets' );

/**
 * Photo gallery shortcode. Workshop Widgets ships one that does the same thing.
 */
function wf_bench_gallery_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'columns' => 3 ), $atts, 'bench_gallery' );
	return '<p class="wf-addon-note">' . esc_html(
		sprintf(
			/* translators: %d: number of columns. */
			__( 'Bench Addons gallery placeholder, %d columns.', 'bench-addons' ),
			(int) $atts['columns']
		)
	) . '</p>';
}
add_shortcode( 'bench_gallery', 'wf_bench_gallery_shortcode' );
