<?php
/**
 * Site health report block: front-end output.
 *
 * Reads the stored scan and writes it out in plain language. Nothing here runs
 * a scan: a page view should never do that much work. The report shows when it
 * was taken, so an old number is obvious rather than quietly wrong.
 *
 * @var array $attributes Block settings.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

$wf_scan = wrenfold_triage_get_scan();
$wf_show = isset( $attributes['show'] ) ? $attributes['show'] : 'all';

if ( empty( $wf_scan['time'] ) ) {
	echo '<div ' . get_block_wrapper_attributes( array( 'class' => 'wf-report' ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p>' . esc_html__( 'No scan has been run on this site yet.', 'wrenfold-triage' ) . '</p>';
	echo '</div>';
	return;
}

$wf_counts   = $wf_scan['counts'];
$wf_problems = 0;
foreach ( $wf_scan['security'] as $wf_finding ) {
	if ( 'ok' !== $wf_finding['severity'] ) {
		++$wf_problems;
	}
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wf-report' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: date, 2: time. */
				__( 'Scanned on %1$s at %2$s.', 'wrenfold-triage' ),
				wp_date( 'j F Y', $wf_scan['time'] ),
				wp_date( 'H:i', $wf_scan['time'] )
			)
		);
		?>
	</p>

	<ul class="wf-report__summary">
		<?php
		$wf_tiles = array(
			'high'   => __( 'need attention', 'wrenfold-triage' ),
			'medium' => __( 'worth fixing', 'wrenfold-triage' ),
			'low'    => __( 'to tidy up', 'wrenfold-triage' ),
			'ok'     => __( 'checks came back clear', 'wrenfold-triage' ),
		);
		foreach ( $wf_tiles as $wf_key => $wf_label ) :
			?>
			<li>
				<span class="wf-report__count"><?php echo esc_html( (string) $wf_counts[ $wf_key ] ); ?></span>
				<span class="wf-report__label"><?php echo esc_html( $wf_label ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( 'summary' === $wf_show ) : ?>
		<p><?php esc_html_e( 'The full report is in the admin, under Site Triage.', 'wrenfold-triage' ); ?></p>
	</div>
		<?php
		return;
	endif;
	?>

	<div class="wf-report__group">
		<h2><?php esc_html_e( 'Security and leftovers', 'wrenfold-triage' ); ?></h2>
		<?php if ( ! $wf_problems ) : ?>
			<p><?php esc_html_e( 'Every security check came back clear.', 'wrenfold-triage' ); ?></p>
		<?php else : ?>
			<?php wrenfold_report_open_scroll( __( 'Security findings', 'wrenfold-triage' ) ); ?>
			<table>
				<caption><?php esc_html_e( 'What the scan found, worst first. The evidence behind each line stays in the admin: account names, file paths and plugin versions are exactly what somebody probing a site is looking for, and a page about hardening should not hand them over.', 'wrenfold-triage' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'What was found', 'wrenfold-triage' ); ?></th>
						<th scope="col"><?php esc_html_e( 'How serious', 'wrenfold-triage' ); ?></th>
						<th scope="col"><?php esc_html_e( 'What to do', 'wrenfold-triage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$wf_order = array( 'high' => 0, 'medium' => 1, 'low' => 2, 'ok' => 3 );
					$wf_rows  = $wf_scan['security'];
					usort(
						$wf_rows,
						function ( $a, $b ) use ( $wf_order ) {
							return $wf_order[ $a['severity'] ] <=> $wf_order[ $b['severity'] ];
						}
					);
					$wf_words = array(
						'high'   => __( 'Needs attention', 'wrenfold-triage' ),
						'medium' => __( 'Worth fixing', 'wrenfold-triage' ),
						'low'    => __( 'Tidy up', 'wrenfold-triage' ),
					);
					foreach ( $wf_rows as $wf_row ) :
						if ( 'ok' === $wf_row['severity'] ) {
							continue;
						}
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $wf_row['title'] ); ?></th>
							<td><span class="wf-badge wf-badge--<?php echo esc_attr( $wf_row['severity'] ); ?>"><?php echo esc_html( $wf_words[ $wf_row['severity'] ] ); ?></span></td>
							<td><?php echo esc_html( $wf_row['fix'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>

	<div class="wf-report__group">
		<h2><?php esc_html_e( 'Add-on plugins', 'wrenfold-triage' ); ?></h2>
		<p>
			<?php
			$wf_unused = 0;
			foreach ( $wf_scan['plugins']['plugins'] as $wf_plugin ) {
				if ( $wf_plugin['features'] > 0 && 0 === $wf_plugin['total'] ) {
					++$wf_unused;
				}
			}
			echo esc_html(
				sprintf(
					/* translators: 1: number of unused plugins, 2: number of duplicated features. */
					__( '%1$d plugins add blocks or shortcodes that appear nowhere on this site. %2$d features are provided by more than one plugin.', 'wrenfold-triage' ),
					$wf_unused,
					count( $wf_scan['plugins']['overlap'] )
				)
			);
			?>
		</p>
		<?php wrenfold_report_open_scroll( __( 'Plugin audit', 'wrenfold-triage' ) ); ?>
		<table>
			<caption><?php esc_html_e( 'Counted from the saved content of every page, post, template and saved section.', 'wrenfold-triage' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Plugin', 'wrenfold-triage' ); ?></th>
					<th scope="col"><?php esc_html_e( 'What it adds', 'wrenfold-triage' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Used', 'wrenfold-triage' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Verdict', 'wrenfold-triage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wf_scan['plugins']['plugins'] as $wf_plugin ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $wf_plugin['name'] ); ?></th>
						<td>
							<?php
							if ( $wf_plugin['uses'] ) {
								$wf_names = array();
								foreach ( $wf_plugin['uses'] as $wf_use ) {
									$wf_names[] = $wf_use['name'] . ' (' . $wf_use['kind'] . ')';
								}
								echo esc_html( implode( ', ', $wf_names ) );
							} else {
								esc_html_e( 'no blocks or shortcodes of its own', 'wrenfold-triage' );
							}
							?>
						</td>
						<td><?php echo esc_html( (string) $wf_plugin['total'] ); ?></td>
						<td>
							<?php
							if ( 0 === $wf_plugin['features'] ) {
								esc_html_e( 'Not an add-on pack', 'wrenfold-triage' );
							} elseif ( $wf_plugin['total'] > 0 ) {
								esc_html_e( 'Keep', 'wrenfold-triage' );
							} else {
								esc_html_e( 'Safe to remove', 'wrenfold-triage' );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>

	<div class="wf-report__group">
		<h2><?php esc_html_e( 'Page builders', 'wrenfold-triage' ); ?></h2>
		<p><?php echo esc_html( wrenfold_triage_builder_advice( $wf_scan['builders'] ) ); ?></p>
		<?php wrenfold_report_open_scroll( __( 'Builders in use', 'wrenfold-triage' ) ); ?>
		<table>
			<caption><?php esc_html_e( 'Read from the hidden fields each builder leaves on a page.', 'wrenfold-triage' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Builder', 'wrenfold-triage' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Pages it draws', 'wrenfold-triage' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Pages carrying its data', 'wrenfold-triage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wf_scan['builders']['counts'] as $wf_builder => $wf_count ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $wf_builder ); ?></th>
						<td><?php echo esc_html( (string) ( isset( $wf_scan['builders']['primary'][ $wf_builder ] ) ? $wf_scan['builders']['primary'][ $wf_builder ] : 0 ) ); ?></td>
						<td><?php echo esc_html( (string) $wf_count ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>

	<div class="wf-report__group">
		<h2><?php esc_html_e( 'On a phone', 'wrenfold-triage' ); ?></h2>
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: number clean, 2: number checked. */
					__( '%1$d of %2$d saved items came back clean.', 'wrenfold-triage' ),
					$wf_scan['mobile']['clean'],
					$wf_scan['mobile']['checked']
				)
			);
			?>
		</p>
		<?php if ( $wf_scan['mobile']['rows'] ) : ?>
			<?php wrenfold_report_open_scroll( __( 'Mobile problems found', 'wrenfold-triage' ) ); ?>
			<table>
				<caption><?php esc_html_e( 'Saved content that causes trouble on a narrow screen.', 'wrenfold-triage' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Where', 'wrenfold-triage' ); ?></th>
						<th scope="col"><?php esc_html_e( 'What is in it', 'wrenfold-triage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $wf_scan['mobile']['rows'] as $wf_row ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $wf_row['label'] ); ?></th>
							<td>
								<?php
								$wf_labels = array();
								foreach ( $wf_row['issues'] as $wf_issue ) {
									$wf_labels[] = $wf_issue['label'];
								}
								echo esc_html( implode( ', ', $wf_labels ) );
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>

	<p class="wf-report__label"><?php esc_html_e( 'The scan reads this install only. It does not send anything anywhere. On a real site a page like this would sit behind a login; it is public here so the demo can be read without one.', 'wrenfold-triage' ); ?></p>
</div>
