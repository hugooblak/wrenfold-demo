<?php
/**
 * FAQ structured data (schema.org FAQPage).
 *
 * The FAQ list block adds each question it shows to a list. At the end of the
 * page, this prints one JSON-LD script with all of them. It helps search engines
 * understand the page. (Google only shows FAQ rich results for some sites, so
 * treat it as a bonus, not a promise.)
 *
 * @package WrenfoldFAQ
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add or read the questions collected on this page.
 *
 * @param array|null $item array( 'q' => question, 'a' => answer text ) or null to read.
 * @return array All collected items.
 */
function wrenfold_faq_schema_items( $item = null ) {
	static $items = array();
	if ( is_array( $item ) ) {
		$items[ md5( $item['q'] ) ] = $item; // Same FAQ twice on a page = listed once.
	}
	return $items;
}

function wrenfold_faq_print_schema() {
	$items = wrenfold_faq_schema_items();
	if ( empty( $items ) ) {
		return;
	}

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(),
	);
	foreach ( $items as $item ) {
		$data['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => $item['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $item['a'],
			),
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
	);
}
add_action( 'wp_footer', 'wrenfold_faq_print_schema' );
