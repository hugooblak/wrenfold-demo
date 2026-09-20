<?php
/**
 * The admin screens.
 *
 * One screen per question the site owner actually asked:
 * what is wrong, which plugins can go, which builder made what, what breaks on
 * a phone, why the buttons lost their styling, and what has been changed so far.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the menu.
 */
function wrenfold_triage_menu() {
	$cap = 'manage_options';
	add_menu_page( __( 'Site Triage', 'wrenfold-triage' ), __( 'Site Triage', 'wrenfold-triage' ), $cap, 'wrenfold-triage', 'wrenfold_triage_page_overview', 'dashicons-shield-alt', 3 );
	$pages = array(
		'wrenfold-triage'           => array( __( 'Overview', 'wrenfold-triage' ), 'wrenfold_triage_page_overview' ),
		'wrenfold-triage-security'  => array( __( 'Security', 'wrenfold-triage' ), 'wrenfold_triage_page_security' ),
		'wrenfold-triage-plugins'   => array( __( 'Plugin audit', 'wrenfold-triage' ), 'wrenfold_triage_page_plugins' ),
		'wrenfold-triage-builders'  => array( __( 'Builders', 'wrenfold-triage' ), 'wrenfold_triage_page_builders' ),
		'wrenfold-triage-mobile'    => array( __( 'Mobile', 'wrenfold-triage' ), 'wrenfold_triage_page_mobile' ),
		'wrenfold-triage-buttons'   => array( __( 'Unstyled buttons', 'wrenfold-triage' ), 'wrenfold_triage_page_buttons' ),
		'wrenfold-triage-log'       => array( __( 'Fix log', 'wrenfold-triage' ), 'wrenfold_triage_page_log' ),
		'wrenfold-triage-demo'      => array( __( 'How this demo was set up', 'wrenfold-triage' ), 'wrenfold_triage_page_demo' ),
	);
	foreach ( $pages as $slug => $page ) {
		add_submenu_page( 'wrenfold-triage', $page[0], $page[0], $cap, $slug, $page[1] );
	}
}
add_action( 'admin_menu', 'wrenfold_triage_menu' );

/**
 * Load the small stylesheet on this plugin's screens only.
 *
 * @param string $hook Current admin page.
 */
function wrenfold_triage_admin_assets( $hook ) {
	if ( false === strpos( $hook, 'wrenfold-triage' ) ) {
		return;
	}
	wp_enqueue_style( 'wrenfold-triage-admin', plugins_url( 'assets/admin.css', WRENFOLD_TRIAGE_DIR . '/wrenfold-triage.php' ), array(), WRENFOLD_TRIAGE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'wrenfold_triage_admin_assets' );

/* --------------------------------------------------------------------------
 * Buttons that do something
 * ----------------------------------------------------------------------- */

/**
 * Run a scan, then go back to the screen the button was pressed on.
 */
function wrenfold_triage_handle_scan() {
	check_admin_referer( 'wrenfold_triage_scan' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'wrenfold-triage' ) );
	}
	wrenfold_triage_run_scan();
	wrenfold_triage_go_back( 'scanned' );
}
add_action( 'admin_post_wrenfold_triage_scan', 'wrenfold_triage_handle_scan' );

/**
 * Apply one fix.
 */
function wrenfold_triage_handle_fix() {
	check_admin_referer( 'wrenfold_triage_fix' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'wrenfold-triage' ) );
	}
	$slug   = isset( $_POST['fix'] ) ? sanitize_key( wp_unslash( $_POST['fix'] ) ) : '';
	$result = wrenfold_triage_apply_fix( $slug );
	wrenfold_triage_run_scan();
	wrenfold_triage_go_back( $result['ok'] ? 'fixed' : 'failed', $result['message'] );
}
add_action( 'admin_post_wrenfold_triage_fix', 'wrenfold_triage_handle_fix' );

/**
 * Undo one fix.
 */
function wrenfold_triage_handle_undo() {
	check_admin_referer( 'wrenfold_triage_undo' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'wrenfold-triage' ) );
	}
	$index  = isset( $_POST['entry'] ) ? absint( wp_unslash( $_POST['entry'] ) ) : -1;
	$result = wrenfold_triage_undo_fix( $index );
	wrenfold_triage_run_scan();
	wrenfold_triage_go_back( $result['ok'] ? 'undone' : 'failed', $result['message'] );
}
add_action( 'admin_post_wrenfold_triage_undo', 'wrenfold_triage_handle_undo' );

/**
 * Send the browser back where it came from, with a message to show.
 *
 * @param string $status  One word for what happened.
 * @param string $message Optional detail.
 */
function wrenfold_triage_go_back( $status, $message = '' ) {
	$page = isset( $_POST['page'] ) ? sanitize_key( wp_unslash( $_POST['page'] ) ) : 'wrenfold-triage'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$url  = add_query_arg(
		array(
			'page'       => $page,
			'wf_status'  => $status,
			'wf_message' => rawurlencode( $message ),
		),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $url );
	exit;
}

/**
 * Show the message left by the last button press.
 */
function wrenfold_triage_notice() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['wf_status'] ) ) {
		return;
	}
	$status  = sanitize_key( wp_unslash( $_GET['wf_status'] ) );
	$message = isset( $_GET['wf_message'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['wf_message'] ) ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	$text = array(
		'scanned' => __( 'Scan finished. Everything on these screens was read just now.', 'wrenfold-triage' ),
		'fixed'   => __( 'Done, and written to the fix log.', 'wrenfold-triage' ),
		'undone'  => __( 'Put back the way it was.', 'wrenfold-triage' ),
		'failed'  => __( 'Nothing was changed.', 'wrenfold-triage' ),
	);
	printf(
		'<div class="notice notice-%s is-dismissible"><p>%s %s</p></div>',
		'failed' === $status ? 'warning' : 'success',
		esc_html( isset( $text[ $status ] ) ? $text[ $status ] : '' ),
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'wrenfold_triage_notice' );

/* --------------------------------------------------------------------------
 * Bits of markup used on more than one screen
 * ----------------------------------------------------------------------- */

/**
 * Page heading, the "run a scan" button and when the last scan happened.
 *
 * @param string $title Screen title.
 * @param string $intro One sentence under it.
 * @param string $page  Screen slug, so the button comes back here.
 */
function wrenfold_triage_header( $title, $intro, $page ) {
	$scan = wrenfold_triage_get_scan();
	echo '<div class="wrap wf-triage">';
	echo '<h1>' . esc_html( $title ) . '</h1>';
	echo '<p class="wf-triage__intro">' . esc_html( $intro ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="wf-triage__scan">';
	wp_nonce_field( 'wrenfold_triage_scan' );
	echo '<input type="hidden" name="action" value="wrenfold_triage_scan">';
	echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '">';
	submit_button( __( 'Run a scan now', 'wrenfold-triage' ), 'primary', 'submit', false );
	if ( ! empty( $scan['time'] ) ) {
		echo ' <span class="wf-triage__when">' . esc_html(
			sprintf(
				/* translators: %s: a date and time. */
				__( 'Last scan: %s', 'wrenfold-triage' ),
				wp_date( 'j M Y, H:i', $scan['time'] )
			)
		) . '</span>';
	} else {
		echo ' <span class="wf-triage__when">' . esc_html__( 'No scan has been run yet.', 'wrenfold-triage' ) . '</span>';
	}
	echo '</form>';
}

/**
 * Close the wrapper opened by the header.
 */
function wrenfold_triage_footer() {
	echo '</div>';
}

/**
 * A coloured severity label.
 *
 * @param string $severity high, medium, low or ok.
 */
function wrenfold_triage_badge( $severity ) {
	$words = array(
		'high'   => __( 'Needs attention', 'wrenfold-triage' ),
		'medium' => __( 'Worth fixing', 'wrenfold-triage' ),
		'low'    => __( 'Tidy up', 'wrenfold-triage' ),
		'ok'     => __( 'Clear', 'wrenfold-triage' ),
	);
	printf(
		'<span class="wf-badge wf-badge--%s">%s</span>',
		esc_attr( $severity ),
		esc_html( isset( $words[ $severity ] ) ? $words[ $severity ] : $severity )
	);
}

/**
 * The button that applies one fix.
 *
 * @param string $slug Fix name.
 * @param string $page Screen slug.
 */
function wrenfold_triage_fix_button( $slug, $page ) {
	$list = wrenfold_triage_fix_list();
	if ( ! $slug || ! isset( $list[ $slug ] ) ) {
		return;
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="wf-triage__fix">';
	wp_nonce_field( 'wrenfold_triage_fix' );
	echo '<input type="hidden" name="action" value="wrenfold_triage_fix">';
	echo '<input type="hidden" name="fix" value="' . esc_attr( $slug ) . '">';
	echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '">';
	submit_button( $list[ $slug ], 'secondary', 'submit', false );
	echo '</form>';
}

/**
 * Tell the reader there is no scan yet, and stop.
 *
 * @param array $scan The stored scan.
 * @return bool True when there is nothing to show.
 */
function wrenfold_triage_needs_scan( $scan ) {
	if ( ! empty( $scan['time'] ) ) {
		return false;
	}
	echo '<p>' . esc_html__( 'Press "Run a scan now" to fill this screen. It reads the site as it is at that moment; nothing is stored in advance.', 'wrenfold-triage' ) . '</p>';
	wrenfold_triage_footer();
	return true;
}

/* --------------------------------------------------------------------------
 * The screens
 * ----------------------------------------------------------------------- */

/**
 * Overview: the counts, and where to go next.
 */
function wrenfold_triage_page_overview() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'Site Triage', 'wrenfold-triage' ),
		__( 'What this WordPress install looks like from the inside, read fresh every time you press the button.', 'wrenfold-triage' ),
		'wrenfold-triage'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}

	$counts = $scan['counts'];
	echo '<ul class="wf-triage__tiles">';
	$tiles = array(
		'high'   => __( 'need attention', 'wrenfold-triage' ),
		'medium' => __( 'worth fixing', 'wrenfold-triage' ),
		'low'    => __( 'tidy up', 'wrenfold-triage' ),
		'ok'     => __( 'checks came back clear', 'wrenfold-triage' ),
	);
	foreach ( $tiles as $key => $label ) {
		echo '<li class="wf-tile wf-tile--' . esc_attr( $key ) . '"><span class="wf-tile__n">' . esc_html( (string) $counts[ $key ] ) . '</span><span class="wf-tile__l">' . esc_html( $label ) . '</span></li>';
	}
	echo '</ul>';

	$plugins  = $scan['plugins'];
	$unused   = 0;
	foreach ( $plugins['plugins'] as $row ) {
		if ( $row['features'] > 0 && 0 === $row['total'] ) {
			++$unused;
		}
	}
	$builders = $scan['builders'];
	$mobile   = $scan['mobile'];

	echo '<h2>' . esc_html__( 'The four questions', 'wrenfold-triage' ) . '</h2>';
	echo '<table class="widefat striped wf-triage__table"><tbody>';
	$lines = array(
		array(
			__( 'Is anything still wrong after the clean-up?', 'wrenfold-triage' ),
			sprintf(
				/* translators: 1: number needing attention, 2: number worth fixing. */
				__( '%1$d need attention, %2$d worth fixing.', 'wrenfold-triage' ),
				$counts['high'],
				$counts['medium']
			),
			'wrenfold-triage-security',
		),
		array(
			__( 'Which add-on plugins can go?', 'wrenfold-triage' ),
			sprintf(
				/* translators: 1: number of plugins, 2: number of duplicated features. */
				__( '%1$d plugins register something but are used nowhere. %2$d features are provided by more than one plugin.', 'wrenfold-triage' ),
				$unused,
				count( $plugins['overlap'] )
			),
			'wrenfold-triage-plugins',
		),
		array(
			__( 'How many builders are in use?', 'wrenfold-triage' ),
			sprintf(
				/* translators: 1: builders drawing pages, 2: builders with data on pages, 3: pages carrying more than one. */
				__( '%1$d draw pages. %2$d have data attached to pages. %3$d pages carry markers from more than one.', 'wrenfold-triage' ),
				count( isset( $builders['primary'] ) ? $builders['primary'] : $builders['counts'] ),
				$builders['builders'],
				$builders['mixed']
			),
			'wrenfold-triage-builders',
		),
		array(
			__( 'What breaks on a phone?', 'wrenfold-triage' ),
			sprintf(
				/* translators: 1: number of items with problems, 2: number checked. */
				__( '%1$d of %2$d saved items hold something that causes trouble on a narrow screen.', 'wrenfold-triage' ),
				count( $mobile['rows'] ),
				$mobile['checked']
			),
			'wrenfold-triage-mobile',
		),
	);
	foreach ( $lines as $line ) {
		echo '<tr><th scope="row">' . esc_html( $line[0] ) . '</th><td>' . esc_html( $line[1] ) . '</td><td><a href="' . esc_url( admin_url( 'admin.php?page=' . $line[2] ) ) . '">' . esc_html__( 'Open', 'wrenfold-triage' ) . '</a></td></tr>';
	}
	echo '</tbody></table>';

	echo '<h2>' . esc_html__( 'What this tool does not do', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'It does not check core and plugin files against the copies WordPress published. That is the one check that names the file an infection actually changed, and it needs to reach wordpress.org, so it is not in this demo.', 'wrenfold-triage' ) . '</p>';
	echo '<p>' . esc_html__( 'It does not remove malware. It finds what a clean-up left behind, explains it and closes the doors. Getting rid of an active infection means restoring from a backup taken before it started, then changing every password and key. Anything else is guesswork dressed up as a fix.', 'wrenfold-triage' ) . '</p>';
	wrenfold_triage_footer();
}

/**
 * Security findings, with the evidence each one is based on.
 */
function wrenfold_triage_page_security() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'Security and hardening', 'wrenfold-triage' ),
		__( 'Every line below says what was found, where it was found, why it happens and what to do about it.', 'wrenfold-triage' ),
		'wrenfold-triage-security'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}

	$order = array( 'high' => 0, 'medium' => 1, 'low' => 2, 'ok' => 3 );
	$rows  = $scan['security'];
	usort(
		$rows,
		function ( $a, $b ) use ( $order ) {
			return $order[ $a['severity'] ] <=> $order[ $b['severity'] ];
		}
	);

	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Finding', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Evidence', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Why it happens, and what to do', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		echo '<tr>';
		echo '<th scope="row">';
		wrenfold_triage_badge( $row['severity'] );
		echo '<span class="wf-triage__title">' . esc_html( $row['title'] ) . '</span>';
		echo '</th>';
		echo '<td><code class="wf-triage__evidence">' . esc_html( $row['evidence'] ) . '</code></td>';
		echo '<td>';
		if ( $row['cause'] ) {
			echo '<p>' . esc_html( $row['cause'] ) . '</p>';
			echo '<p><strong>' . esc_html__( 'Fix:', 'wrenfold-triage' ) . '</strong> ' . esc_html( $row['fix'] ) . '</p>';
			wrenfold_triage_fix_button( $row['action'], 'wrenfold-triage-security' );
			if ( ! $row['action'] ) {
				echo '<p class="wf-triage__byhand">' . esc_html__( 'This one is done by hand. It changes a file or needs a decision, and a button would hide that.', 'wrenfold-triage' ) . '</p>';
			}
		} else {
			echo '<p class="wf-triage__byhand">' . esc_html__( 'Nothing to do.', 'wrenfold-triage' ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	wrenfold_triage_footer();
}

/**
 * The plugin audit: what each plugin adds, and whether the site uses it.
 */
function wrenfold_triage_page_plugins() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'Plugin audit', 'wrenfold-triage' ),
		__( 'Counted from the saved content of every page, post, template, pattern and menu, drafts included. A plugin is only safe to remove when nothing it adds appears anywhere.', 'wrenfold-triage' ),
		'wrenfold-triage-plugins'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}
	$data = $scan['plugins'];

	if ( $data['overlap'] ) {
		echo '<h2>' . esc_html__( 'The same feature from more than one plugin', 'wrenfold-triage' ) . '</h2>';
		echo '<p>' . esc_html__( 'This is the part that makes a plugin stack heavy. Each pack loads its own code on every page view, whether or not its version of the feature is the one in use.', 'wrenfold-triage' ) . '</p>';
		echo '<table class="widefat striped wf-triage__table"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Feature', 'wrenfold-triage' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Provided by', 'wrenfold-triage' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Times used on this site', 'wrenfold-triage' ) . '</th>';
		echo '</tr></thead><tbody>';
		foreach ( $data['overlap'] as $entry ) {
			echo '<tr><th scope="row">' . esc_html( $entry['feature'] ) . ' <span class="wf-triage__kind">' . esc_html( $entry['kind'] ) . '</span></th>';
			echo '<td>' . esc_html( implode( ', ', $entry['by'] ) ) . '</td>';
			echo '<td>' . esc_html( (string) $entry['used'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	echo '<h2>' . esc_html__( 'Every installed plugin', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'Blocks, shortcodes and Elementor widgets all count. An Elementor add-on pack registers no blocks at all: its widgets live in PHP and are stored inside each page\'s Elementor data. A tool that counted only blocks would call every one of those packs unused, which is the most expensive wrong answer it could give.', 'wrenfold-triage' ) . '</p>';
	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Plugin', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'What it adds', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Where it is used', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Loads on every page', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Verdict', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $data['plugins'] as $row ) {
		echo '<tr>';
		echo '<th scope="row">' . esc_html( $row['name'] ) . ' <span class="wf-triage__kind">' . esc_html( $row['version'] ) . '</span><br>';
		echo '<span class="wf-triage__kind">' . esc_html( $row['active'] ? __( 'active', 'wrenfold-triage' ) : __( 'switched off', 'wrenfold-triage' ) ) . '</span></th>';
		echo '<td>';
		if ( $row['uses'] ) {
			echo '<ul class="wf-triage__uses">';
			foreach ( $row['uses'] as $use ) {
				echo '<li><code>' . esc_html( $use['name'] ) . '</code> <span class="wf-triage__kind">' . esc_html( $use['kind'] ) . '</span> — ' . esc_html(
					sprintf(
						/* translators: %d: number of times used. */
						_n( 'used %d time', 'used %d times', $use['count'], 'wrenfold-triage' ),
						$use['count']
					)
				) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<span class="wf-triage__kind">' . esc_html__( 'no blocks or shortcodes of its own', 'wrenfold-triage' ) . '</span>';
		}
		echo '</td>';
		echo '<td>' . ( $row['pages'] ? esc_html( implode( ', ', $row['pages'] ) ) : '<span class="wf-triage__kind">' . esc_html__( 'nowhere', 'wrenfold-triage' ) . '</span>' ) . '</td>';
		echo '<td>';
		if ( ! empty( $row['loads'] ) ) {
			echo esc_html(
				sprintf(
					/* translators: %d: number of files. */
					_n( '%d file', '%d files', count( $row['loads'] ), 'wrenfold-triage' ),
					count( $row['loads'] )
				)
			);
			echo '<br><span class="wf-triage__kind">' . esc_html( implode( ', ', $row['loads'] ) ) . '</span>';
		} else {
			echo '<span class="wf-triage__kind">' . esc_html__( 'nothing', 'wrenfold-triage' ) . '</span>';
		}
		echo '</td>';
		echo '<td>';
		if ( ! empty( $row['truncated'] ) ) {
			wrenfold_triage_badge( 'medium' );
			echo '<span class="wf-triage__title">' . esc_html__( 'Too many files to read them all. No verdict: an unread file is not the same as an unused plugin. Check this one by hand.', 'wrenfold-triage' ) . '</span>';
		} elseif ( 0 === $row['features'] ) {
			wrenfold_triage_badge( 'ok' );
			echo '<span class="wf-triage__title">' . esc_html__( 'Not an add-on pack. Judge it on what it does, not on block counts.', 'wrenfold-triage' ) . '</span>';
		} elseif ( $row['total'] > 0 ) {
			wrenfold_triage_badge( 'ok' );
			echo '<span class="wf-triage__title">' . esc_html__( 'Keep. Removing it would empty part of a page.', 'wrenfold-triage' ) . '</span>';
		} else {
			wrenfold_triage_badge( 'medium' );
			echo '<span class="wf-triage__title">' . esc_html__( 'Nothing it adds is used anywhere. Switch it off, check the site, then delete it.', 'wrenfold-triage' ) . '</span>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';

	echo '<h2>' . esc_html__( 'Switch off the unused packs', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'This only touches plugins whose blocks and shortcodes appear nowhere in the content above. Switching off is reversible and the files stay on the server, so a page that turns out to need one can be put right in a click.', 'wrenfold-triage' ) . '</p>';
	wrenfold_triage_fix_button( 'deactivate_unused', 'wrenfold-triage-plugins' );
	wrenfold_triage_footer();
}

/**
 * Which builder made which page, and what that means.
 */
function wrenfold_triage_page_builders() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'Which builder made which page', 'wrenfold-triage' ),
		__( 'Read from the hidden fields each page builder leaves on a page, plus the markup itself. This is the list to look at before deciding whether to standardise on one builder.', 'wrenfold-triage' ),
		'wrenfold-triage-builders'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}
	$data = $scan['builders'];

	echo '<h2>' . esc_html__( 'Count', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'A page can carry one builder\'s data while another draws it. That data travels with every backup and export. It is why a later migration costs more than anyone quoted.', 'wrenfold-triage' ) . '</p>';
	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Builder', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Pages it draws', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Pages carrying its data', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $data['counts'] as $builder => $count ) {
		$draws = isset( $data['primary'][ $builder ] ) ? $data['primary'][ $builder ] : 0;
		echo '<tr><th scope="row">' . esc_html( $builder ) . '</th><td>' . esc_html( (string) $draws ) . '</td><td>' . esc_html( (string) $count ) . '</td></tr>';
	}
	echo '</tbody></table>';

	echo '<h2>' . esc_html__( 'What the numbers say', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html( wrenfold_triage_builder_advice( $data ) ) . '</p>';
	echo '<p>' . esc_html__( 'Whatever you decide, do not run two builders side by side for longer than the move takes. Two builders means two sets of CSS and JavaScript on every page view, two upgrade paths, and an editor who has to remember which page works which way.', 'wrenfold-triage' ) . '</p>';

	echo '<h2>' . esc_html__( 'Page by page', 'wrenfold-triage' ) . '</h2>';
	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Page', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Built with', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Markers found on it', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $data['rows'] as $row ) {
		echo '<tr><th scope="row">' . esc_html( $row['label'] ) . '</th>';
		echo '<td>' . esc_html( $row['primary'] ) . '</td>';
		echo '<td>';
		foreach ( $row['found'] as $builder => $markers ) {
			echo '<div>' . esc_html( $builder ) . ' <code>' . esc_html( implode( ', ', (array) $markers ) ) . '</code></div>';
		}
		if ( $row['mixed'] ) {
			echo '<p class="wf-triage__byhand">' . esc_html__( 'More than one marker. Usually leftover data from an earlier build: the old builder is not drawing this page any more, but its data is still in the database and still travels with every export and backup.', 'wrenfold-triage' ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	wrenfold_triage_footer();
}

/**
 * Turn the builder counts into a recommendation.
 *
 * @param array $data Builder scan.
 * @return string
 */
function wrenfold_triage_builder_advice( $data ) {
	$primary = isset( $data['primary'] ) ? $data['primary'] : $data['counts'];
	$all     = $data['counts'];
	$total   = array_sum( $primary );

	if ( count( $primary ) > 1 ) {
		$main  = key( $primary );
		$first = current( $primary );
		$rest  = $total - $first;
		return sprintf(
			/* translators: 1: builder name, 2: pages on it, 3: pages on everything else, 4: total pages. */
			__( '%2$d of %4$d pages are drawn by %1$s and %3$d by something else. Move the smaller group, not the larger one: that is the least rebuilding for the same result. Rebuild the pages that bring in work first, keep the old page as a draft until the new one is live, and only remove the losing builder once no page carries its data.', 'wrenfold-triage' ),
			$main,
			$first,
			$rest,
			$total
		);
	}

	$leftovers = array();
	foreach ( $all as $builder => $count ) {
		if ( ! isset( $primary[ $builder ] ) ) {
			$leftovers[] = $builder . ' (' . $count . ')';
		}
	}
	if ( $leftovers ) {
		return sprintf(
			/* translators: 1: the builder drawing every page, 2: a list of builders and page counts. */
			__( 'One builder draws every page: %1$s. Nothing to standardise. But data from other builders is still attached to pages here: %2$s. Nothing draws with it, so the site looks fine, and it still travels with every backup, every export and every migration, and it is the reason a later move takes longer than anyone quoted. Remove those fields once you are certain the pages are staying as they are.', 'wrenfold-triage' ),
			key( $primary ),
			implode( ', ', $leftovers )
		);
	}

	return __( 'One builder across the whole site, and no leftover data from any other. Nothing to standardise. Keep it that way: the cost of a second builder shows up later, not on the day it is installed.', 'wrenfold-triage' );
}

/**
 * What breaks on a narrow screen.
 */
function wrenfold_triage_page_mobile() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'What breaks on a phone', 'wrenfold-triage' ),
		__( 'This reads the saved content, not a browser. It cannot measure how big a button looks, but it finds the things that make a page scroll sideways, which is the complaint behind most "it is bad on mobile" reports.', 'wrenfold-triage' ),
		'wrenfold-triage-mobile'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}
	$data = $scan['mobile'];

	echo '<p>' . esc_html(
		sprintf(
			/* translators: 1: number of clean items, 2: number checked. */
			__( '%1$d of %2$d saved items came back clean.', 'wrenfold-triage' ),
			$data['clean'],
			$data['checked']
		)
	) . '</p>';

	if ( ! $data['rows'] ) {
		echo '<p>' . esc_html__( 'Nothing found. No fixed pixel widths, no unscrollable tables, no images without a size.', 'wrenfold-triage' ) . '</p>';
		wrenfold_triage_footer();
		return;
	}

	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Page', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'What is in it', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Why it matters', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $data['rows'] as $row ) {
		echo '<tr><th scope="row">' . esc_html( $row['label'] ) . '</th><td><ul class="wf-triage__uses">';
		foreach ( $row['issues'] as $issue ) {
			$line = $issue['label'];
			if ( $issue['worst'] ) {
				$line .= sprintf(
					/* translators: %d: a width in pixels. */
					__( ', widest %dpx', 'wrenfold-triage' ),
					$issue['worst']
				);
			}
			$line .= ' × ' . $issue['count'];
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></td><td><ul class="wf-triage__uses">';
		foreach ( $row['issues'] as $issue ) {
			echo '<li>' . esc_html( $issue['why'] ) . '</li>';
		}
		echo '</ul></td></tr>';
	}
	echo '</tbody></table>';
	echo '<p class="wf-triage__byhand">' . esc_html__( 'There is no button for these. A width fixed in pixels is a design decision that has to be replaced with another one, and only a person can decide what the page should do instead on a 390px screen.', 'wrenfold-triage' ) . '</p>';
	wrenfold_triage_footer();
}

/**
 * The unstyled-button problem: what it looks like and how to find the cause.
 */
function wrenfold_triage_page_buttons() {
	$scan = wrenfold_triage_get_scan();
	wrenfold_triage_header(
		__( 'When a button renders as a plain link', 'wrenfold-triage' ),
		__( 'The most common report after a clean-up, and almost always the same cause: the markup is fine, the stylesheet that styles it never arrives.', 'wrenfold-triage' ),
		'wrenfold-triage-buttons'
	);
	if ( wrenfold_triage_needs_scan( $scan ) ) {
		return;
	}

	echo '<h2>' . esc_html__( 'What it looks like', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'A mock-up of the two states, drawn here so you know what you are looking for. Both are styled by this screen; nothing on this site is really broken. On a real site the two look exactly like this, and the markup is identical in both.', 'wrenfold-triage' ) . '</p>';
	echo '<div class="wf-triage__ba">';
	echo '<div class="wf-triage__ba-col"><h3>' . esc_html__( 'Stylesheet missing', 'wrenfold-triage' ) . '</h3><div class="wf-demo-broken"><a class="wf-demo-button" href="#0">' . esc_html__( 'Ask for a quote', 'wrenfold-triage' ) . '</a></div></div>';
	echo '<div class="wf-triage__ba-col"><h3>' . esc_html__( 'Stylesheet loads', 'wrenfold-triage' ) . '</h3><div class="wf-demo-fixed"><a class="wf-demo-button" href="#0">' . esc_html__( 'Ask for a quote', 'wrenfold-triage' ) . '</a></div></div>';
	echo '</div>';

	echo '<h2>' . esc_html__( 'What this site is actually missing', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'This part is not a mock-up. Every registered stylesheet and script under wp-content was checked against the disk just now.', 'wrenfold-triage' ) . '</p>';
	if ( empty( $scan['assets'] ) ) {
		echo '<p>' . esc_html__( 'Nothing. Every registered stylesheet and script under wp-content is on the server.', 'wrenfold-triage' ) . '</p>';
	} else {
		echo '<table class="widefat striped wf-triage__table"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Handle', 'wrenfold-triage' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Asked for', 'wrenfold-triage' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Looked for on disk at', 'wrenfold-triage' ) . '</th>';
		echo '</tr></thead><tbody>';
		foreach ( $scan['assets'] as $asset ) {
			echo '<tr><th scope="row">' . esc_html( $asset['handle'] ) . ' <span class="wf-triage__kind">' . esc_html( $asset['kind'] ) . '</span></th>';
			echo '<td><code class="wf-triage__evidence">' . esc_html( $asset['src'] ) . '</code></td>';
			echo '<td><code class="wf-triage__evidence">' . esc_html( $asset['path'] ) . '</code></td></tr>';
		}
		echo '</tbody></table>';
	}

	echo '<h2>' . esc_html__( 'How to find the cause on a real site, in order', 'wrenfold-triage' ) . '</h2>';
	echo '<ol class="wf-triage__steps">';
	$steps = array(
		__( 'Open the page and look at the network requests. A stylesheet answering 404 is the answer, and you are finished.', 'wrenfold-triage' ),
		__( 'If every file loads, compare the class names on the broken button against a page where it works. A builder that saved while its own plugin was switched off writes markup without its classes.', 'wrenfold-triage' ),
		__( 'Check whether the styles arrive but are beaten. An add-on pack loading after the theme can override the button rules with its own.', 'wrenfold-triage' ),
		__( 'Switch add-on plugins off one at a time and reload. The pack that brings the button back is the one to look at.', 'wrenfold-triage' ),
		__( 'Only then edit the page. Rewriting markup first hides the cause and it comes back on the next page someone saves.', 'wrenfold-triage' ),
	);
	foreach ( $steps as $step ) {
		echo '<li>' . esc_html( $step ) . '</li>';
	}
	echo '</ol>';
	wrenfold_triage_footer();
}

/**
 * Everything this plugin has changed.
 */
function wrenfold_triage_page_log() {
	wrenfold_triage_header(
		__( 'Fix log', 'wrenfold-triage' ),
		__( 'Every change this plugin made, who made it and when. Each row can be put back.', 'wrenfold-triage' ),
		'wrenfold-triage-log'
	);
	$log = wrenfold_triage_log_entries();
	if ( ! $log ) {
		echo '<p>' . esc_html__( 'Nothing has been changed yet. This plugin only reads the site until you press a fix button.', 'wrenfold-triage' ) . '</p>';
		wrenfold_triage_footer();
		return;
	}
	$names = wrenfold_triage_fix_list();
	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'When', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'What', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Result', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Undo', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( array_reverse( $log, true ) as $index => $entry ) {
		echo '<tr>';
		echo '<th scope="row">' . esc_html( wp_date( 'j M Y, H:i', $entry['time'] ) ) . '<br><span class="wf-triage__kind">' . esc_html( $entry['user'] ) . '</span></th>';
		echo '<td>' . esc_html( isset( $names[ $entry['action'] ] ) ? $names[ $entry['action'] ] : $entry['action'] ) . '</td>';
		echo '<td>' . esc_html( $entry['message'] ) . '</td>';
		echo '<td>';
		if ( ! empty( $entry['undone'] ) ) {
			echo esc_html(
				sprintf(
					/* translators: %s: a date and time. */
					__( 'Undone %s', 'wrenfold-triage' ),
					wp_date( 'j M Y, H:i', $entry['undone'] )
				)
			);
		} else {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			wp_nonce_field( 'wrenfold_triage_undo' );
			echo '<input type="hidden" name="action" value="wrenfold_triage_undo">';
			echo '<input type="hidden" name="entry" value="' . esc_attr( (string) $index ) . '">';
			echo '<input type="hidden" name="page" value="wrenfold-triage-log">';
			submit_button( __( 'Undo this', 'wrenfold-triage' ), 'secondary small', 'submit', false );
			echo '</form>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	wrenfold_triage_footer();
}

/**
 * The honesty screen: what was planted here on purpose.
 */
function wrenfold_triage_page_demo() {
	echo '<div class="wrap wf-triage">';
	echo '<h1>' . esc_html__( 'How this demo was set up', 'wrenfold-triage' ) . '</h1>';
	echo '<p class="wf-triage__intro">' . esc_html__( 'The scan on the other screens is real. It reads this install and reports what is there. To give it something to find, this install was deliberately broken in the ways below when the demo content was created.', 'wrenfold-triage' ) . '</p>';
	echo '<table class="widefat striped wf-triage__table"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Planted on purpose', 'wrenfold-triage' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Which check finds it', 'wrenfold-triage' ) . '</th>';
	echo '</tr></thead><tbody>';
	$planted = array(
		array( __( 'A PHP file placed in the uploads folder. It contains a comment and nothing else: no malicious code was written for this demo, and none is needed, because the check is about where the file is, not what is in it.', 'wrenfold-triage' ), __( 'Security: runnable files in uploads', 'wrenfold-triage' ) ),
		array( __( 'A scheduled job pointing at a plugin that is not installed.', 'wrenfold-triage' ), __( 'Security: scheduled jobs with no code behind them', 'wrenfold-triage' ) ),
		array( __( 'A second administrator account, created today.', 'wrenfold-triage' ), __( 'Security: recently created administrators', 'wrenfold-triage' ) ),
		array( __( 'Visitor sign-ups switched on.', 'wrenfold-triage' ), __( 'Security: open registration', 'wrenfold-triage' ) ),
		array( __( 'A PHP file in the WordPress folder that WordPress does not ship.', 'wrenfold-triage' ), __( 'Security: unexpected files in the WordPress folder', 'wrenfold-triage' ) ),
		array( __( 'A stylesheet handle registered against a file that was never created. It is registered but never enqueued, so no visitor requests it.', 'wrenfold-triage' ), __( 'Unstyled buttons: missing stylesheets and scripts', 'wrenfold-triage' ) ),
		array( __( 'Three add-on plugins written for this demo that duplicate each other. Two of them also carry an Elementor widget, one used on the old Elementor page and one used nowhere. One of them loads a stylesheet on every page view and is used nowhere at all.', 'wrenfold-triage' ), __( 'Plugin audit', 'wrenfold-triage' ) ),
		array( __( 'Two live pages carrying leftover fields from other page builders, and two draft pages whose layout really does live in Elementor and BeBuilder data instead of in the page text. The second pair matters: that is how a real Elementor page is stored, and a check that only read page text would miss every one of them.', 'wrenfold-triage' ), __( 'Builders', 'wrenfold-triage' ) ),
		array( __( 'The old homepage kept as a draft saved section, with the fixed widths and unsized images it had before the rebuild. Draft, so it cannot be dropped onto a page by accident.', 'wrenfold-triage' ), __( 'Mobile', 'wrenfold-triage' ) ),
		array( __( 'A draft saved section holding hidden off-screen links to another domain, the shape a spam injection takes. The links point at the reserved example domain, so they go nowhere.', 'wrenfold-triage' ), __( 'Security: hidden links and scripts', 'wrenfold-triage' ) ),
	);
	foreach ( $planted as $row ) {
		echo '<tr><th scope="row">' . esc_html( $row[0] ) . '</th><td>' . esc_html( $row[1] ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<h2>' . esc_html__( 'What is not planted', 'wrenfold-triage' ) . '</h2>';
	echo '<p>' . esc_html__( 'The findings themselves. Nothing on the other screens is a stored list of results. Press "Run a scan now" after changing anything, and the numbers move.', 'wrenfold-triage' ) . '</p>';
	echo '<p>' . esc_html__( 'Wrenfold Joinery is a fictional brand built for a portfolio demo. The company, the people and the address are invented.', 'wrenfold-triage' ) . '</p>';
	echo '</div>';
}
