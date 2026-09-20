<?php
/**
 * Bench Addons: an Elementor widget.
 *
 * This file is never included. Elementor is not installed on this demo, and
 * loading a class that extends one of its classes would stop the site dead.
 * It is here because the plugin audit reads plugin files rather than asking
 * WordPress, so it finds widgets in packs that are installed but switched off,
 * and in packs whose own framework is missing.
 *
 * That matters: an Elementor add-on pack registers no blocks and no shortcodes.
 * Counting only blocks would report every one of them as "adds nothing".
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

class Wf_Bench_Spec_Table_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wf-bench-spec';
	}

	public function get_title() {
		return __( 'Specification table', 'bench-addons' );
	}

	protected function render() {
		echo '<p>' . esc_html__( 'Specification table placeholder from Bench Addons.', 'bench-addons' ) . '</p>';
	}
}
