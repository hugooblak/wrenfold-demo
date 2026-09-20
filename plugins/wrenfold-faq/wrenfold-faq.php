<?php
/**
 * Plugin Name:       Wrenfold Joinery FAQ Library
 * Description:       An FAQ library the team manages in the WordPress admin: add, edit, categorise, reorder, publish and remove FAQs. Includes an "FAQ list" block that shows them on any page.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       wrenfold-faq
 *
 * Why a plugin and not theme code: FAQs are content. If the site ever
 * changes theme, the FAQs and the block keep working.
 *
 * @package WrenfoldFAQ
 */

defined( 'ABSPATH' ) || exit;

define( 'WRENFOLD_FAQ_DIR', __DIR__ );

require_once WRENFOLD_FAQ_DIR . '/includes/post-type.php';
require_once WRENFOLD_FAQ_DIR . '/includes/admin.php';
require_once WRENFOLD_FAQ_DIR . '/includes/schema.php';

/**
 * Register the FAQ list block from its block.json.
 */
function wrenfold_faq_register_block() {
	register_block_type( WRENFOLD_FAQ_DIR . '/blocks/faq-list' );
}
add_action( 'init', 'wrenfold_faq_register_block' );
