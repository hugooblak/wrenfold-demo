<?php
/**
 * Specification table block: front-end output.
 *
 * @package WrenfoldDemoAddons
 */

defined( 'ABSPATH' ) || exit;

$wf_rows = array(
	array( __( 'Base units', 'bench-addons' ), '575 mm', '720 mm', __( '300, 450, 500, 600, 800, 900 mm', 'bench-addons' ) ),
	array( __( 'Tall units', 'bench-addons' ), '575 mm', '2150 mm', __( '500, 600 mm', 'bench-addons' ) ),
	array( __( 'Wall units', 'bench-addons' ), '330 mm', '720 mm', __( '300, 450, 600, 800 mm', 'bench-addons' ) ),
	array( __( 'Drawer boxes', 'bench-addons' ), '500 mm', __( 'to suit', 'bench-addons' ), __( 'cut to the opening', 'bench-addons' ) ),
);
?>
<div <?php echo get_block_wrapper_attributes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	array(
		'class'      => 'wf-report__scroll',
		'role'       => 'region',
		'tabindex'   => '0',
		'aria-label' => esc_attr__( 'Standard carcass sizes', 'bench-addons' ),
	)
); ?>>
	<table>
		<caption><?php esc_html_e( 'Standard carcass sizes used in every kitchen we make.', 'bench-addons' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Unit', 'bench-addons' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Depth', 'bench-addons' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Height', 'bench-addons' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Widths', 'bench-addons' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $wf_rows as $wf_row ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $wf_row[0] ); ?></th>
					<td><?php echo esc_html( $wf_row[1] ); ?></td>
					<td><?php echo esc_html( $wf_row[2] ); ?></td>
					<td><?php echo esc_html( $wf_row[3] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
