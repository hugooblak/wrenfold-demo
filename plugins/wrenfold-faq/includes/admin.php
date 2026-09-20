<?php
/**
 * Admin list improvements for FAQs.
 *
 * - Adds a sortable "Order" column.
 * - Sorts the FAQ list by Order by default, so the admin list matches the site.
 * - Adds a topic filter dropdown above the list.
 *
 * @package WrenfoldFAQ
 */

defined( 'ABSPATH' ) || exit;

function wrenfold_faq_columns( $columns ) {
	$columns['menu_order'] = __( 'Order', 'wrenfold-faq' );
	return $columns;
}
add_filter( 'manage_fp_faq_posts_columns', 'wrenfold_faq_columns' );

function wrenfold_faq_column_content( $column, $post_id ) {
	if ( 'menu_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_fp_faq_posts_custom_column', 'wrenfold_faq_column_content', 10, 2 );

function wrenfold_faq_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-wf_faq_sortable_columns', 'wrenfold_faq_sortable_columns' );

function wrenfold_faq_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'wf_faq' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
	// Topic filter dropdown (topics aren't public, so we apply the filter ourselves).
	$topic = isset( $_GET['wf_faq_topic'] ) ? sanitize_key( wp_unslash( $_GET['wf_faq_topic'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $topic && '0' !== $topic ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'wf_faq_topic',
					'field'    => 'slug',
					'terms'    => $topic,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'wrenfold_faq_admin_order' );

function wrenfold_faq_topic_filter( $post_type ) {
	if ( 'wf_faq' !== $post_type ) {
		return;
	}
	wp_dropdown_categories(
		array(
			'taxonomy'        => 'wf_faq_topic',
			'name'            => 'wf_faq_topic',
			'value_field'     => 'slug',
			'show_option_all' => __( 'All topics', 'wrenfold-faq' ),
			'selected'        => isset( $_GET['wf_faq_topic'] ) ? sanitize_key( wp_unslash( $_GET['wf_faq_topic'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'hide_empty'      => false,
			'hierarchical'    => true,
		)
	);
}
add_action( 'restrict_manage_posts', 'wrenfold_faq_topic_filter' );


/**
 * Load the "Order" sidebar box on the FAQ edit screen.
 */
function wrenfold_faq_editor_assets() {
	if ( 'wf_faq' !== get_post_type() ) {
		return;
	}
	wp_enqueue_script(
		'wrenfold-faq-editor',
		plugins_url( 'assets/faq-editor.js', WRENFOLD_FAQ_DIR . '/wrenfold-faq.php' ),
		array( 'wp-plugins', 'wp-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-dom-ready' ),
		'1.0.0',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wrenfold_faq_editor_assets' );

/**
 * One-line tip above the FAQ list, so the team knows how ordering works.
 */
function wrenfold_faq_list_tip() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-wf_faq' !== $screen->id ) {
		return;
	}
	echo '<p class="description" style="margin:12px 0 0">' . esc_html__( 'FAQs show on the site in the order of the "Order" number, lowest first. Filter by topic to see one list. Change the number when editing an FAQ, or with Quick Edit.', 'wrenfold-faq' ) . '</p>';
}
add_action( 'all_admin_notices', 'wrenfold_faq_list_tip' );
