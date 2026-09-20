<?php
/**
 * FAQ list block: front-end output.
 *
 * Each FAQ is a native <details> element: it opens and closes without any
 * JavaScript, works with the keyboard and is announced by screen readers.
 *
 * @var array $attributes Block settings. "topic" = topic slug, or '' for all.
 *
 * @package WrenfoldFAQ
 */

defined( 'ABSPATH' ) || exit;

$wf_topic = isset( $attributes['topic'] ) ? sanitize_key( $attributes['topic'] ) : '';

$wf_args = array(
	'post_type'              => 'wf_faq',
	'post_status'            => 'publish',
	'posts_per_page'         => 100,
	'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), // The "Order" field set in the admin.
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
);
if ( $wf_topic ) {
	$wf_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		array(
			'taxonomy' => 'wf_faq_topic',
			'field'    => 'slug',
			'terms'    => $wf_topic,
		),
	);
}
$wf_faqs = get_posts( $wf_args );

if ( ! $wf_faqs ) {
	// Only the editor preview explains an empty list. Visitors see nothing.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		echo '<p>' . esc_html__( 'No published FAQs for this topic yet.', 'wrenfold-faq' ) . '</p>';
	}
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wf-faq-list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	foreach ( $wf_faqs as $wf_faq ) :
		$wf_question = get_the_title( $wf_faq );
		// Render the answer's blocks directly. Not 'the_content', so share buttons or
		// related-post plugins don't get added to every answer.
		$wf_answer   = wptexturize( do_blocks( $wf_faq->post_content ) );

		wrenfold_faq_schema_items(
			array(
				'q' => wp_strip_all_tags( $wf_question ),
				'a' => trim( wp_strip_all_tags( $wf_answer ) ),
			)
		);
		?>
		<details class="wf-faq">
			<summary class="wf-faq__question"><?php echo esc_html( $wf_question ); ?></summary>
			<div class="wf-faq__answer"><?php echo wp_kses_post( $wf_answer ); ?></div>
		</details>
	<?php endforeach; ?>
</div>
