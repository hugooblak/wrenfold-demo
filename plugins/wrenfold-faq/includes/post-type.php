<?php
/**
 * FAQ post type and FAQ topic taxonomy.
 *
 * - Question = post title, answer = post content (normal block editor).
 * - Topic = category-style taxonomy, e.g. "Quotes and prices", "Timings".
 * - Order = the built-in "Order" field (menu_order). Lower numbers show first.
 *
 * FAQs have no pages of their own. They only appear through the FAQ list block,
 * so they are not public and don't create thin pages for search engines.
 *
 * @package WrenfoldFAQ
 */

defined( 'ABSPATH' ) || exit;

function wrenfold_faq_register_types() {
	register_post_type(
		'wf_faq',
		array(
			'labels'              => array(
				'name'               => __( 'FAQs', 'wrenfold-faq' ),
				'singular_name'      => __( 'FAQ', 'wrenfold-faq' ),
				'add_new'            => __( 'Add FAQ', 'wrenfold-faq' ),
				'add_new_item'       => __( 'Add new FAQ', 'wrenfold-faq' ),
				'edit_item'          => __( 'Edit FAQ', 'wrenfold-faq' ),
				'new_item'           => __( 'New FAQ', 'wrenfold-faq' ),
				'search_items'       => __( 'Search FAQs', 'wrenfold-faq' ),
				'not_found'          => __( 'No FAQs found.', 'wrenfold-faq' ),
				'not_found_in_trash' => __( 'No FAQs in the bin.', 'wrenfold-faq' ),
				'all_items'          => __( 'All FAQs', 'wrenfold-faq' ),
				'menu_name'          => __( 'FAQs', 'wrenfold-faq' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true, // Needed for the block editor.
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-editor-help',
			'supports'            => array( 'title', 'editor', 'page-attributes', 'revisions' ),
			// Start every new answer with a single paragraph, so editors just type.
			'template'            => array(
				array( 'core/paragraph', array( 'placeholder' => __( 'Write the answer. Keep it short and plain.', 'wrenfold-faq' ) ) ),
			),
		)
	);

	register_taxonomy(
		'wf_faq_topic',
		'wf_faq',
		array(
			'labels'            => array(
				'name'          => __( 'FAQ topics', 'wrenfold-faq' ),
				'singular_name' => __( 'FAQ topic', 'wrenfold-faq' ),
				'add_new_item'  => __( 'Add new topic', 'wrenfold-faq' ),
				'edit_item'     => __( 'Edit topic', 'wrenfold-faq' ),
				'all_items'     => __( 'All topics', 'wrenfold-faq' ),
				'menu_name'     => __( 'Topics', 'wrenfold-faq' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true, // Checkbox UI, like post categories.
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'wrenfold_faq_register_types' );

/**
 * Title field says "Question" instead of "Add title".
 */
function wrenfold_faq_title_placeholder( $text, $post ) {
	return 'wf_faq' === $post->post_type ? __( 'Question', 'wrenfold-faq' ) : $text;
}
add_filter( 'enter_title_here', 'wrenfold_faq_title_placeholder', 10, 2 );
