<?php
/**
 * The fixes.
 *
 * Three rules here, and they are why these buttons can be used on a live site
 * without crossing your fingers:
 *   1. Nothing runs on its own. Every fix is one button, pressed on purpose.
 *   2. Every fix writes a log row saying what it changed.
 *   3. Every fix can be undone from that same log row.
 *
 * Anything that cannot be undone safely is not offered as a button. It is
 * written out as an instruction instead.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep XML-RPC off once the fix has been applied.
 */
function wrenfold_triage_filter_xmlrpc( $enabled ) {
	return get_option( 'wrenfold_triage_xmlrpc_off' ) ? false : $enabled;
}
add_filter( 'xmlrpc_enabled', 'wrenfold_triage_filter_xmlrpc' );

/**
 * The fixes this plugin can carry out, in the order they appear on screen.
 *
 * @return array
 */
function wrenfold_triage_fix_list() {
	return array(
		'close_registration'     => __( 'Stop visitors creating their own accounts', 'wrenfold-triage' ),
		'disable_xmlrpc'         => __( 'Switch off the XML-RPC endpoint', 'wrenfold-triage' ),
		'unschedule_orphan_cron' => __( 'Remove scheduled jobs with no code behind them', 'wrenfold-triage' ),
		'add_uploads_index'      => __( 'Stop the uploads folder being listed', 'wrenfold-triage' ),
		'quarantine_uploads_php' => __( 'Move runnable files out of the uploads folder', 'wrenfold-triage' ),
		'demote_new_admins'      => __( 'Take the administrator role off recent accounts', 'wrenfold-triage' ),
		'deactivate_unused'      => __( 'Switch off add-on plugins that nothing uses', 'wrenfold-triage' ),
	);
}

/**
 * Carry out one fix.
 *
 * @param string $slug Fix name.
 * @return array{ok:bool,message:string}
 */
function wrenfold_triage_apply_fix( $slug ) {
	$undo    = array();
	$message = '';

	switch ( $slug ) {
		case 'close_registration':
			$undo = array(
				'users_can_register' => get_option( 'users_can_register' ),
				'default_role'       => get_option( 'default_role' ),
			);
			update_option( 'users_can_register', 0 );
			update_option( 'default_role', 'subscriber' );
			$message = __( 'Membership is off. New accounts would be subscribers if it is ever turned back on.', 'wrenfold-triage' );
			break;

		case 'disable_xmlrpc':
			$undo = array( 'was' => (int) get_option( 'wrenfold_triage_xmlrpc_off' ) );
			update_option( 'wrenfold_triage_xmlrpc_off', 1 );
			$message = __( 'XML-RPC now answers every request with a refusal.', 'wrenfold-triage' );
			break;

		case 'unschedule_orphan_cron':
			// Record everything first. wp_unschedule_hook() removes every timestamp
			// for a hook at once, so calling it mid-loop would leave the rest of the
			// stale list looking real and get the same hook re-added twice on undo.
			$removed = array();
			$hooks   = array();
			foreach ( (array) _get_cron_array() as $timestamp => $due ) {
				foreach ( $due as $hook => $events ) {
					if ( has_action( $hook ) ) {
						continue;
					}
					$hooks[ $hook ] = true;
					foreach ( $events as $event ) {
						$removed[] = array(
							'hook'      => $hook,
							'timestamp' => $timestamp,
							'schedule'  => isset( $event['schedule'] ) ? $event['schedule'] : false,
							'args'      => isset( $event['args'] ) ? $event['args'] : array(),
						);
					}
				}
			}
			foreach ( array_keys( $hooks ) as $hook ) {
				wp_unschedule_hook( $hook );
			}
			if ( ! $removed ) {
				return array(
					'ok'      => false,
					'message' => __( 'Nothing to remove: every scheduled job has code listening for it.', 'wrenfold-triage' ),
				);
			}
			$undo    = array( 'events' => $removed );
			$message = sprintf(
				/* translators: %d: number of jobs. */
				_n( '%d scheduled job removed.', '%d scheduled jobs removed.', count( $removed ), 'wrenfold-triage' ),
				count( $removed )
			);
			break;

		case 'add_uploads_index':
			$uploads = wp_get_upload_dir();
			$target  = trailingslashit( $uploads['basedir'] ) . 'index.php';
			if ( file_exists( $target ) ) {
				return array( 'ok' => false, 'message' => __( 'There is already an index file in the uploads folder.', 'wrenfold-triage' ) );
			}
			if ( false === file_put_contents( $target, "<?php\n// Nothing to see here. This file stops the folder being listed.\n" ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				return array( 'ok' => false, 'message' => __( 'Could not write to the uploads folder. Check its permissions.', 'wrenfold-triage' ) );
			}
			$undo    = array( 'created' => $target );
			$message = __( 'A blank index file is now in the uploads folder.', 'wrenfold-triage' );
			break;

		case 'quarantine_uploads_php':
			$uploads = wp_get_upload_dir();
			$found   = wrenfold_triage_find_php( $uploads['basedir'] );
			if ( ! $found ) {
				return array( 'ok' => false, 'message' => __( 'Nothing to move: there are no runnable files in the uploads folder.', 'wrenfold-triage' ) );
			}
			$store = WP_CONTENT_DIR . '/wrenfold-quarantine';
			if ( ! is_dir( $store ) && ! wp_mkdir_p( $store ) ) {
				return array( 'ok' => false, 'message' => __( 'Could not create the quarantine folder.', 'wrenfold-triage' ) );
			}
			$moved = array();
			foreach ( $found as $relative ) {
				$from = trailingslashit( $uploads['basedir'] ) . $relative;
				$to   = $store . '/' . str_replace( array( '/', '\\' ), '__', $relative ) . '.quarantined';
				if ( @rename( $from, $to ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.rename_rename
					$moved[] = array( 'from' => $from, 'to' => $to );
				}
			}
			if ( ! $moved ) {
				return array( 'ok' => false, 'message' => __( 'The files could not be moved. Check folder permissions.', 'wrenfold-triage' ) );
			}
			$undo    = array( 'moved' => $moved );
			$message = sprintf(
				/* translators: %d: number of files. */
				_n( '%d file moved out of uploads. It is kept, not deleted, so you can still read it.', '%d files moved out of uploads. They are kept, not deleted, so you can still read them.', count( $moved ), 'wrenfold-triage' ),
				count( $moved )
			);
			break;

		case 'demote_new_admins':
			$changed = array();
			foreach ( get_users( array( 'role' => 'administrator' ) ) as $user ) {
				// Never the first account, and never the person pressing the button:
				// locking yourself out of your own site is not a security fix.
				if ( (int) $user->ID === 1 || (int) $user->ID === get_current_user_id() ) {
					continue;
				}
				if ( strtotime( $user->user_registered ) <= ( time() - 60 * DAY_IN_SECONDS ) ) {
					continue;
				}
				$changed[] = array( 'id' => $user->ID, 'login' => $user->user_login, 'roles' => $user->roles );
				$user->set_role( 'subscriber' );
			}
			if ( ! $changed ) {
				return array( 'ok' => false, 'message' => __( 'No recently created administrator accounts to change.', 'wrenfold-triage' ) );
			}
			$undo    = array( 'users' => $changed );
			$message = sprintf(
				/* translators: %d: number of accounts. */
				_n( '%d account is now a subscriber. Nothing was deleted.', '%d accounts are now subscribers. Nothing was deleted.', count( $changed ), 'wrenfold-triage' ),
				count( $changed )
			);
			break;

		case 'deactivate_unused':
			$scan = wrenfold_triage_get_scan();
			if ( empty( $scan['plugins']['plugins'] ) ) {
				return array( 'ok' => false, 'message' => __( 'Run a scan first, so the decision is made on current numbers.', 'wrenfold-triage' ) );
			}
			$off = array();
			foreach ( $scan['plugins']['plugins'] as $row ) {
				if ( ! $row['active'] || $row['features'] < 1 || $row['total'] > 0 ) {
					continue;
				}
				// A pack too large to read all of may register something the audit
				// never saw. An unread file is not the same as an unused plugin.
				if ( ! empty( $row['truncated'] ) ) {
					continue;
				}
				if ( 0 === strpos( $row['file'], 'wrenfold-' ) ) {
					continue; // Never switch off this plugin or the FAQ library.
				}
				$off[] = $row['file'];
			}
			if ( ! $off ) {
				return array( 'ok' => false, 'message' => __( 'Every add-on plugin registers something that is used somewhere. Nothing to switch off.', 'wrenfold-triage' ) );
			}
			deactivate_plugins( $off );
			$undo    = array( 'plugins' => $off );
			$message = sprintf(
				/* translators: %d: number of plugins. */
				_n( '%d plugin switched off. Its files are still on the server until you delete it.', '%d plugins switched off. Their files are still on the server until you delete them.', count( $off ), 'wrenfold-triage' ),
				count( $off )
			);
			break;

		default:
			return array( 'ok' => false, 'message' => __( 'Unknown fix.', 'wrenfold-triage' ) );
	}

	wrenfold_triage_log( $slug, $message, $undo );
	return array( 'ok' => true, 'message' => $message );
}

/**
 * Put a fix back the way it was.
 *
 * @param int $index Position in the log.
 * @return array{ok:bool,message:string}
 */
function wrenfold_triage_undo_fix( $index ) {
	$log = wrenfold_triage_log_entries();
	if ( ! isset( $log[ $index ] ) || ! empty( $log[ $index ]['undone'] ) ) {
		return array( 'ok' => false, 'message' => __( 'That entry cannot be undone.', 'wrenfold-triage' ) );
	}
	$entry = $log[ $index ];
	$undo  = $entry['undo'];

	switch ( $entry['action'] ) {
		case 'close_registration':
			update_option( 'users_can_register', $undo['users_can_register'] );
			update_option( 'default_role', $undo['default_role'] );
			break;
		case 'disable_xmlrpc':
			update_option( 'wrenfold_triage_xmlrpc_off', $undo['was'] );
			break;
		case 'unschedule_orphan_cron':
			foreach ( $undo['events'] as $event ) {
				if ( $event['schedule'] ) {
					wp_schedule_event( $event['timestamp'], $event['schedule'], $event['hook'], $event['args'] );
				} else {
					wp_schedule_single_event( $event['timestamp'], $event['hook'], $event['args'] );
				}
			}
			break;
		case 'add_uploads_index':
			if ( file_exists( $undo['created'] ) ) {
				wp_delete_file( $undo['created'] );
			}
			break;
		case 'quarantine_uploads_php':
			$failed = array();
			foreach ( $undo['moved'] as $move ) {
				if ( ! file_exists( $move['to'] ) ) {
					$failed[] = basename( $move['from'] );
					continue;
				}
				wp_mkdir_p( dirname( $move['from'] ) ); // The dated folder may have been tidied away since.
				if ( ! @rename( $move['to'], $move['from'] ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.rename_rename
					$failed[] = basename( $move['from'] );
				}
			}
			if ( $failed ) {
				return array(
					'ok'      => false,
					'message' => sprintf(
						/* translators: %s: a list of file names. */
						__( 'Could not put these back: %s. They are still in wp-content/wrenfold-quarantine.', 'wrenfold-triage' ),
						implode( ', ', $failed )
					),
				);
			}
			break;
		case 'demote_new_admins':
			foreach ( $undo['users'] as $stored ) {
				$user = get_user_by( 'id', $stored['id'] );
				if ( ! $user ) {
					continue;
				}
				$roles = ( is_array( $stored['roles'] ) && $stored['roles'] ) ? $stored['roles'] : array( 'administrator' );
				$user->set_role( array_shift( $roles ) );
				foreach ( $roles as $extra ) {
					$user->add_role( $extra ); // A user can hold more than one role.
				}
			}
			break;
		case 'deactivate_unused':
			activate_plugins( $undo['plugins'] );
			break;
		default:
			return array( 'ok' => false, 'message' => __( 'That fix has no undo.', 'wrenfold-triage' ) );
	}

	$log[ $index ]['undone'] = time();
	update_option( WRENFOLD_TRIAGE_LOG, $log, false );
	return array( 'ok' => true, 'message' => __( 'Put back the way it was.', 'wrenfold-triage' ) );
}

/**
 * Write a log row.
 *
 * @param string $action  Fix slug.
 * @param string $message What happened, in words.
 * @param array  $undo    Everything needed to reverse it.
 */
function wrenfold_triage_log( $action, $message, $undo ) {
	$log   = wrenfold_triage_log_entries();
	$log[] = array(
		'time'    => time(),
		'user'    => wp_get_current_user()->user_login,
		'action'  => $action,
		'message' => $message,
		'undo'    => $undo,
		'undone'  => 0,
	);
	update_option( WRENFOLD_TRIAGE_LOG, $log, false );
}

/**
 * Every log row, oldest first.
 *
 * @return array
 */
function wrenfold_triage_log_entries() {
	$log = get_option( WRENFOLD_TRIAGE_LOG );
	return is_array( $log ) ? $log : array();
}
