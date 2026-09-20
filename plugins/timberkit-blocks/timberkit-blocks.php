<?php
/**
 * Plugin Name:       Timberkit Blocks
 * Description:       A third pack of extra blocks. Written for this demo. Every block it registers is also registered by one of the other two packs, which is exactly the situation the plugin audit is built to spot.
 * Version:           1.0.7
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       timberkit-blocks
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

function wf_timberkit_register_blocks() {
	foreach ( array( 'price-list', 'team-card' ) as $wf_block ) {
		register_block_type( __DIR__ . '/blocks/' . $wf_block );
	}
}
add_action( 'init', 'wf_timberkit_register_blocks' );

function wf_timberkit_editor_assets() {
	wp_enqueue_script(
		'wf-timberkit-editor',
		plugins_url( 'editor.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
		'1.0.7',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wf_timberkit_editor_assets' );
