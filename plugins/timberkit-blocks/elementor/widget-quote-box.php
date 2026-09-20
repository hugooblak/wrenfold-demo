<?php
/**
 * Timberkit Blocks: an Elementor widget.
 *
 * Never included; see the note in the Bench Addons widget file. It exists so
 * the plugin audit can show a widget that is registered and used nowhere,
 * next to one that is registered and used on a page.
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

class Wf_Timberkit_Quote_Box_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wf-timberkit-quote';
	}

	public function get_title() {
		return __( 'Quote box', 'timberkit-blocks' );
	}

	protected function render() {
		echo '<p>' . esc_html__( 'Quote box placeholder from Timberkit Blocks.', 'timberkit-blocks' ) . '</p>';
	}
}
