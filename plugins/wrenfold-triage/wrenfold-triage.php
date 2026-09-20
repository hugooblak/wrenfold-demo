<?php
/**
 * Plugin Name:       Wrenfold Site Triage
 * Description:       Reads this WordPress install and reports what is wrong with it: security leftovers after a clean-up, add-on plugins nobody uses, pages built with different page builders, and content that breaks on phones. Every finding shows the evidence it was based on. Fixes are opt-in, one at a time, and logged.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       wrenfold-triage
 *
 * Why a plugin and not theme code: this is a tool, not part of the design.
 * It keeps working if the site changes theme, and it can be removed when the
 * clean-up is finished without taking any content with it.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

define( 'WRENFOLD_TRIAGE_DIR', __DIR__ );
define( 'WRENFOLD_TRIAGE_VERSION', '1.0.0' );
define( 'WRENFOLD_TRIAGE_OPTION', 'wrenfold_triage_last_scan' );
define( 'WRENFOLD_TRIAGE_LOG', 'wrenfold_triage_fix_log' );

require_once WRENFOLD_TRIAGE_DIR . '/includes/scan.php';
require_once WRENFOLD_TRIAGE_DIR . '/includes/fixes.php';
require_once WRENFOLD_TRIAGE_DIR . '/includes/admin.php';
require_once WRENFOLD_TRIAGE_DIR . '/includes/plant.php';

/**
 * Register the public "site health report" block.
 */
function wrenfold_triage_register_block() {
	register_block_type( WRENFOLD_TRIAGE_DIR . '/blocks/health-report' );
}
add_action( 'init', 'wrenfold_triage_register_block' );

/**
 * A stylesheet handle that points at a file which is not on disk.
 *
 * This is here on purpose. It is the demo's version of the problem the job
 * describes: buttons that render as plain links because the stylesheet that
 * styles them never loads. The handle is registered but never enqueued, so no
 * visitor ever requests the missing file. The asset check finds it anyway,
 * which is the point: you can spot this before a visitor does.
 */
function wrenfold_triage_register_broken_handle() {
	wp_register_style(
		'wf-legacy-hero-buttons',
		plugins_url( 'assets/legacy-hero-buttons.css', __FILE__ ),
		array(),
		'0.9.3'
	);
}
add_action( 'wp_enqueue_scripts', 'wrenfold_triage_register_broken_handle' );
add_action( 'admin_enqueue_scripts', 'wrenfold_triage_register_broken_handle' );
