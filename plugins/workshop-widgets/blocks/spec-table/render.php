<?php
/**
 * Spec table block: front-end output.
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p><?php esc_html_e( 'Spec table placeholder from Workshop Widgets.', 'workshop-widgets' ); ?></p>
</div>
