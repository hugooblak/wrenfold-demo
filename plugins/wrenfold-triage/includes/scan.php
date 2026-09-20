<?php
/**
 * The scanner.
 *
 * Everything here reads the live install. Nothing is hardcoded, and no finding
 * is written down in advance. If a check has nothing to report it says so, and
 * that "nothing to report" is itself shown, so a clean result is visible rather
 * than silent.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build one finding.
 *
 * @param string $id       Short code for the check.
 * @param string $severity high, medium, low or ok.
 * @param string $title    One line: what was found.
 * @param string $evidence What it was found in. Facts only.
 * @param string $cause    Why this happens.
 * @param string $fix      What to do about it.
 * @param string $action   Fix slug this plugin can apply, or '' for by hand.
 * @return array
 */
function wrenfold_triage_finding( $id, $severity, $title, $evidence, $cause, $fix, $action = '' ) {
	return compact( 'id', 'severity', 'title', 'evidence', 'cause', 'fix', 'action' );
}

/**
 * Every post, page, template, pattern and menu on the site, in one list.
 *
 * Drafts and private items are included on purpose: a hacked or half-migrated
 * site hides most of its mess outside the published pages.
 *
 * @return WP_Post[]
 */
function wrenfold_triage_all_content() {
	static $posts = null;
	if ( null !== $posts ) {
		return $posts;
	}
	$posts = get_posts(
		array(
			'post_type'   => array( 'page', 'post', 'wp_template', 'wp_template_part', 'wp_block', 'wp_navigation' ),
			'post_status' => 'any',
			'numberposts' => 300,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);
	return $posts;
}

/**
 * A readable name for a post, for use in evidence.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function wrenfold_triage_label( $post ) {
	$title = $post->post_title ? $post->post_title : $post->post_name;
	if ( 'publish' === $post->post_status ) {
		return $title;
	}
	return $title . ' (' . $post->post_status . ')';
}

/* ==========================================================================
 * 1. Security and hardening
 * ======================================================================= */

/**
 * Run the security checks.
 *
 * @return array List of findings.
 */
function wrenfold_triage_scan_security() {
	$out = array();

	/* PHP files sitting in the uploads folder. ---------------------------- */
	$uploads = wp_get_upload_dir();
	$php     = wrenfold_triage_find_php( $uploads['basedir'] );
	if ( $php ) {
		$out[] = wrenfold_triage_finding(
			'uploads_php',
			'high',
			sprintf(
				/* translators: %d: number of files. */
				_n( '%d file that PHP can run is sitting in the uploads folder', '%d files that PHP can run are sitting in the uploads folder', count( $php ), 'wrenfold-triage' ),
				count( $php )
			),
			implode( ', ', $php ),
			__( 'Uploads is for pictures and documents. A file there that the server will execute almost always arrives through an upload form or a compromised account, and it survives a theme or plugin clean-up because nobody looks in that folder.', 'wrenfold-triage' ),
			__( 'Move the file out of the web root and read it before deleting it: it often says how it got there. Then stop the folder running code at all, with a server rule that refuses PHP under uploads.', 'wrenfold-triage' ),
			'quarantine_uploads_php'
		);
	} else {
		$out[] = wrenfold_triage_finding( 'uploads_php', 'ok', __( 'No runnable PHP files in the uploads folder', 'wrenfold-triage' ), $uploads['basedir'], '', '' );
	}

	/* Scheduled jobs whose code no longer exists. ------------------------- */
	$orphans = wrenfold_triage_orphan_cron();
	if ( $orphans ) {
		$out[] = wrenfold_triage_finding(
			'orphan_cron',
			'medium',
			sprintf(
				/* translators: %d: number of jobs. */
				_n( '%d scheduled job has no code listening for it', '%d scheduled jobs have no code listening for them', count( $orphans ), 'wrenfold-triage' ),
				count( $orphans )
			),
			implode( ', ', $orphans ),
			__( 'WordPress keeps a list of jobs to run on a timer. Removing a plugin, or cleaning malicious files off the disk, does not remove its entries from that list. The job keeps being due, and nothing answers it.', 'wrenfold-triage' ),
			__( 'Check the job name first. If it belongs to a plugin that is meant to be gone, unschedule it. If the name is unfamiliar, treat it as a leftover from the infection and look for what created it.', 'wrenfold-triage' ),
			'unschedule_orphan_cron'
		);
	} else {
		$out[] = wrenfold_triage_finding( 'orphan_cron', 'ok', __( 'Every scheduled job has code listening for it', 'wrenfold-triage' ), __( 'checked the whole schedule', 'wrenfold-triage' ), '', '' );
	}

	/* Administrator accounts. --------------------------------------------- */
	$admins = get_users( array( 'role' => 'administrator', 'orderby' => 'registered' ) );
	$recent = array();
	foreach ( $admins as $admin ) {
		if ( strtotime( $admin->user_registered ) > ( time() - 60 * DAY_IN_SECONDS ) && (int) $admin->ID !== 1 ) {
			$recent[] = $admin->user_login . ' (' . gmdate( 'j M Y', strtotime( $admin->user_registered ) ) . ', ' . $admin->user_email . ')';
		}
	}
	if ( $recent ) {
		$out[] = wrenfold_triage_finding(
			'new_admins',
			'high',
			sprintf(
				/* translators: %d: number of accounts. */
				_n( '%d administrator account was created in the last 60 days', '%d administrator accounts were created in the last 60 days', count( $recent ), 'wrenfold-triage' ),
				count( $recent )
			),
			implode( '; ', $recent ),
			__( 'A second administrator account is the usual way back in after the files are cleaned. It looks ordinary in the user list, so it is easy to miss.', 'wrenfold-triage' ),
			__( 'Ask the owner whether they created it. If nobody recognises it, take the administrator role away first rather than deleting it, so the account can still be looked at, then reset every other password.', 'wrenfold-triage' ),
			'demote_new_admins'
		);
	} else {
		$out[] = wrenfold_triage_finding(
			'new_admins',
			'ok',
			sprintf(
				/* translators: %d: number of accounts. */
				_n( '%d administrator account, none created recently', '%d administrator accounts, none created recently', count( $admins ), 'wrenfold-triage' ),
				count( $admins )
			),
			__( 'checked the registration date on every administrator', 'wrenfold-triage' ),
			'',
			''
		);
	}

	/* Anyone can sign up. -------------------------------------------------- */
	if ( get_option( 'users_can_register' ) ) {
		$out[] = wrenfold_triage_finding(
			'open_registration',
			'high',
			__( 'Anyone visiting the site can create an account', 'wrenfold-triage' ),
			sprintf(
				/* translators: %s: the default role given to new accounts. */
				__( 'new accounts are given the role: %s', 'wrenfold-triage' ),
				get_option( 'default_role' )
			),
			__( 'A site that does not sell anything or run a members area has no reason to accept sign-ups. It is switched on quietly during an attack so that a way in survives a password reset.', 'wrenfold-triage' ),
			__( 'Turn membership off in Settings, then look at every account created while it was on.', 'wrenfold-triage' ),
			'close_registration'
		);
	} else {
		$out[] = wrenfold_triage_finding( 'open_registration', 'ok', __( 'Visitors cannot create their own accounts', 'wrenfold-triage' ), __( 'Settings: membership is off', 'wrenfold-triage' ), '', '' );
	}

	/* Editing plugin and theme files from the admin. ----------------------- */
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
		$out[] = wrenfold_triage_finding(
			'file_edit',
			'medium',
			__( 'Plugin and theme files can be edited from inside the admin', 'wrenfold-triage' ),
			__( 'DISALLOW_FILE_EDIT is not set in wp-config.php', 'wrenfold-triage' ),
			__( 'Anyone who gets into one administrator account can write code straight onto the server through the built-in file editor. No upload and no file access needed.', 'wrenfold-triage' ),
			__( "Add define( 'DISALLOW_FILE_EDIT', true ); to wp-config.php. Real changes belong in version control, not in a text box in the browser.", 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding( 'file_edit', 'ok', __( 'The built-in file editor is switched off', 'wrenfold-triage' ), 'DISALLOW_FILE_EDIT', '', '' );
	}

	/* XML-RPC. ------------------------------------------------------------- */
	if ( apply_filters( 'xmlrpc_enabled', true ) && ! get_option( 'wrenfold_triage_xmlrpc_off' ) ) {
		$out[] = wrenfold_triage_finding(
			'xmlrpc',
			'low',
			__( 'The old XML-RPC endpoint still answers', 'wrenfold-triage' ),
			'xmlrpc.php',
			__( 'It is the door most password-guessing scripts knock on, because one request there can carry many guesses. Most sites stopped needing it years ago.', 'wrenfold-triage' ),
			__( 'Switch it off unless something still uses it: the old WordPress mobile app, Jetpack, or a desktop blog editor. Check before turning it off.', 'wrenfold-triage' ),
			'disable_xmlrpc'
		);
	} else {
		$out[] = wrenfold_triage_finding( 'xmlrpc', 'ok', __( 'The XML-RPC endpoint is switched off', 'wrenfold-triage' ), 'xmlrpc.php', '', '' );
	}

	/* Uploads folder listing. ---------------------------------------------- */
	if ( ! file_exists( trailingslashit( $uploads['basedir'] ) . 'index.php' ) ) {
		$out[] = wrenfold_triage_finding(
			'uploads_index',
			'low',
			__( 'The uploads folder has no blank index file', 'wrenfold-triage' ),
			trailingslashit( $uploads['basedir'] ) . 'index.php',
			__( 'On a server that lists folder contents when there is no index file, anyone can read the whole uploads folder. That includes documents nobody meant to publish.', 'wrenfold-triage' ),
			__( 'Drop a blank index.php in the folder. It costs nothing and it closes the listing whatever the server is set to.', 'wrenfold-triage' ),
			'add_uploads_index'
		);
	} else {
		$out[] = wrenfold_triage_finding( 'uploads_index', 'ok', __( 'The uploads folder cannot be listed', 'wrenfold-triage' ), __( 'a blank index.php is in place', 'wrenfold-triage' ), '', '' );
	}

	/* Unexpected PHP files in the WordPress folder. ------------------------ */
	$strays = wrenfold_triage_stray_root_files();
	if ( $strays ) {
		$out[] = wrenfold_triage_finding(
			'root_files',
			'high',
			sprintf(
				/* translators: %d: number of files. */
				_n( '%d PHP file in the WordPress folder is not part of WordPress', '%d PHP files in the WordPress folder are not part of WordPress', count( $strays ), 'wrenfold-triage' ),
				count( $strays )
			),
			implode( ', ', $strays ),
			__( 'The top WordPress folder holds a fixed set of files. Anything else there was put there by a person, an installer, or an attacker, and the third is the most common.', 'wrenfold-triage' ),
			__( 'Read each file before touching it. Some are harmless leftovers from a host migration. Move them out of the web root rather than deleting them, so you can still look at them afterwards.', 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding( 'root_files', 'ok', __( 'Only WordPress files in the WordPress folder', 'wrenfold-triage' ), __( 'compared against the list of files WordPress ships', 'wrenfold-triage' ), '', '' );
	}

	/* Hidden links and scripts in content. --------------------------------- */
	$injected = wrenfold_triage_injected_content();
	if ( $injected ) {
		$out[] = wrenfold_triage_finding(
			'injected',
			'high',
			sprintf(
				/* translators: %d: number of pages or posts. */
				_n( 'Hidden markup found in %d page or post', 'Hidden markup found in %d pages or posts', count( $injected ), 'wrenfold-triage' ),
				count( $injected )
			),
			implode( '; ', $injected ),
			__( 'Spam injections hide links inside content that nobody reads, using off-screen positioning or a script tag. The page looks normal to a visitor and sells pills to a search engine.', 'wrenfold-triage' ),
			__( 'Compare the page against an earlier revision and remove the hidden block. Then find the way in, because the content will come back on its own if you only clean the text.', 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding( 'injected', 'ok', __( 'No hidden links or scripts in content', 'wrenfold-triage' ), __( 'searched every page, post, template and pattern, drafts included', 'wrenfold-triage' ), '', '' );
	}

	/* Options loaded on every single request. ------------------------------ */
	$alloptions = wp_load_alloptions();
	$bytes      = 0;
	$biggest    = array();
	foreach ( $alloptions as $name => $value ) {
		$size    = strlen( (string) $value );
		$bytes  += $size;
		$biggest[ $name ] = $size;
	}
	arsort( $biggest );
	$top = array_slice( $biggest, 0, 3, true );
	$top_text = array();
	foreach ( $top as $name => $size ) {
		$top_text[] = $name . ' ' . size_format( $size );
	}
	if ( $bytes > 800 * KB_IN_BYTES ) {
		$out[] = wrenfold_triage_finding(
			'autoload',
			'medium',
			sprintf(
				/* translators: %s: a file size, e.g. 1.2 MB. */
				__( '%s of settings is loaded on every single page view', 'wrenfold-triage' ),
				size_format( $bytes )
			),
			implode( ', ', $top_text ),
			__( 'Some plugins park logs, caches or licence data in the settings table and mark it to load every time. Removing the plugin often leaves the data behind.', 'wrenfold-triage' ),
			__( 'Look at the biggest entries by name, work out what wrote them, and either stop it loading every time or delete it. This is usually the cheapest speed win on an old site.', 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding(
			'autoload',
			'ok',
			sprintf(
				/* translators: %s: a file size, e.g. 210 KB. */
				__( 'Settings loaded on every page view: %s', 'wrenfold-triage' ),
				size_format( $bytes )
			),
			implode( ', ', $top_text ),
			'',
			''
		);
	}

	/* Installed but switched off. ------------------------------------------ */
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$inactive = array();
	foreach ( get_plugins() as $file => $data ) {
		if ( ! is_plugin_active( $file ) ) {
			$inactive[] = $data['Name'];
		}
	}
	$themes = wp_get_themes();
	$spare  = array();
	foreach ( $themes as $slug => $theme ) {
		if ( get_stylesheet() !== $slug && get_template() !== $slug ) {
			$spare[] = $theme->get( 'Name' );
		}
	}
	if ( $inactive || $spare ) {
		$parts = array();
		if ( $inactive ) {
			$parts[] = sprintf(
				/* translators: %d: number of plugins. */
				_n( '%d switched-off plugin', '%d switched-off plugins', count( $inactive ), 'wrenfold-triage' ),
				count( $inactive )
			);
		}
		if ( $spare ) {
			$parts[] = sprintf(
				/* translators: %d: number of themes. */
				_n( '%d unused theme', '%d unused themes', count( $spare ), 'wrenfold-triage' ),
				count( $spare )
			);
		}
		$out[] = wrenfold_triage_finding(
			'dormant_code',
			'medium',
			sprintf(
				/* translators: %s: a list such as "2 switched-off plugins and 3 unused themes". */
				__( '%s still on the server', 'wrenfold-triage' ),
				implode( __( ' and ', 'wrenfold-triage' ), $parts )
			),
			trim( implode( ', ', $inactive ) . ' | ' . implode( ', ', $spare ), ' |' ),
			__( 'Switched off is not gone. The files are still there, they still stop getting security updates once abandoned, and a known hole in them can often be reached directly by its file address.', 'wrenfold-triage' ),
			__( 'Delete anything the site does not use. Keep one spare default theme so WordPress has something to fall back on if the main theme breaks.', 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding( 'dormant_code', 'ok', __( 'No dormant plugins or spare themes on the server', 'wrenfold-triage' ), __( 'checked the plugins and themes folders', 'wrenfold-triage' ), '', '' );
	}

	/* Debug output. --------------------------------------------------------- */
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$shown = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		$out[] = wrenfold_triage_finding(
			'debug',
			$shown ? 'medium' : 'low',
			$shown ? __( 'Error messages are switched on and printed to the page', 'wrenfold-triage' ) : __( 'Error logging is switched on', 'wrenfold-triage' ),
			'WP_DEBUG' . ( $shown ? ', WP_DEBUG_DISPLAY' : '' ),
			__( 'Error messages name file paths, database details and plugin versions. That is useful while building and useful to somebody looking for a way in.', 'wrenfold-triage' ),
			__( 'Leave it on while working and off on the live site. If you need it on live, log to a file instead of printing to the page.', 'wrenfold-triage' ),
			''
		);
	} else {
		$out[] = wrenfold_triage_finding( 'debug', 'ok', __( 'Debug output is off', 'wrenfold-triage' ), 'WP_DEBUG', '', '' );
	}

	return $out;
}

/**
 * Find PHP files under a folder.
 *
 * @param string $dir Folder to search.
 * @return string[] Paths relative to that folder.
 */
function wrenfold_triage_find_php( $dir ) {
	$found = array();
	if ( ! is_dir( $dir ) ) {
		return $found;
	}
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$name = $file->getFilename();
		// A blank index.php is the standard way of stopping folder listing, not a problem.
		if ( 'index.php' === $name && $file->getSize() < 100 ) {
			continue;
		}
		if ( preg_match( '/\.(php|phtml|php5|php7|phar)$/i', $name ) ) {
			$found[] = ltrim( str_replace( $dir, '', $file->getPathname() ), '/\\' );
		}
		if ( count( $found ) >= 20 ) {
			break;
		}
	}
	sort( $found );
	return $found;
}

/**
 * Scheduled jobs with nothing listening for them.
 *
 * @return string[] Hook names with how often they run.
 */
function wrenfold_triage_orphan_cron() {
	$cron = _get_cron_array();
	if ( ! is_array( $cron ) ) {
		return array();
	}
	$orphans = array();
	foreach ( $cron as $timestamp => $hooks ) {
		foreach ( $hooks as $hook => $events ) {
			if ( has_action( $hook ) ) {
				continue;
			}
			$schedule = '';
			foreach ( $events as $event ) {
				$schedule = empty( $event['schedule'] ) ? __( 'once', 'wrenfold-triage' ) : $event['schedule'];
			}
			$orphans[ $hook ] = $hook . ' (' . $schedule . ', ' . __( 'next due', 'wrenfold-triage' ) . ' ' . gmdate( 'j M H:i', $timestamp ) . ')';
		}
	}
	return array_values( $orphans );
}

/**
 * PHP files in the WordPress folder that WordPress does not ship.
 *
 * @return string[] File names.
 */
function wrenfold_triage_stray_root_files() {
	$known = array(
		'index.php', 'wp-activate.php', 'wp-blog-header.php', 'wp-comments-post.php',
		'wp-config.php', 'wp-config-sample.php', 'wp-cron.php', 'wp-links-opml.php',
		'wp-load.php', 'wp-login.php', 'wp-mail.php', 'wp-settings.php',
		'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php',
	);
	$strays = array();
	foreach ( (array) glob( ABSPATH . '*.{php,phtml,php5,php7,phar}', GLOB_BRACE ) as $path ) {
		$name = basename( $path );
		if ( ! in_array( $name, $known, true ) ) {
			$strays[] = $name . ' (' . size_format( (int) filesize( $path ) ) . ', ' . __( 'changed', 'wrenfold-triage' ) . ' ' . gmdate( 'j M Y', (int) filemtime( $path ) ) . ')';
		}
	}
	return $strays;
}

/**
 * Content holding hidden links or script tags.
 *
 * @return string[] One line per page or post.
 */
function wrenfold_triage_injected_content() {
	$hits = array();
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	foreach ( wrenfold_triage_all_content() as $post ) {
		$reasons = array();

		// Hiding something is normal. Hiding a link to another site is not, and
		// that pair is what a spam injection looks like. Checking for hidden
		// markup on its own would flag every accordion and every menu.
		if ( preg_match_all( '/(?:display\s*:\s*none|left\s*:\s*-\s*\d{3,}px|text-indent\s*:\s*-\s*\d{3,}px)(.{0,400})/is', $post->post_content, $m ) ) {
			foreach ( $m[1] as $after ) {
				if ( preg_match_all( '#<a\b[^>]*href=["\']https?://([^/"\']+)#i', $after, $links ) ) {
					foreach ( $links[1] as $host ) {
						if ( $host !== $home ) {
							$reasons[] = sprintf(
								/* translators: %s: a domain name. */
								__( 'a hidden link to %s', 'wrenfold-triage' ),
								$host
							);
						}
					}
				}
			}
		}
		if ( false !== stripos( $post->post_content, '<scr' . 'ipt' ) ) {
			$reasons[] = __( 'a script tag saved in the page text', 'wrenfold-triage' );
		}
		if ( $reasons ) {
			$hits[] = wrenfold_triage_label( $post ) . ': ' . implode( ', ', array_unique( $reasons ) );
		}
	}
	return $hits;
}

/* ==========================================================================
 * 2. Add-on plugins: what each one registers, and where it is actually used
 * ======================================================================= */

/**
 * For every installed plugin: the blocks, shortcodes and Elementor widgets it
 * registers, how many times each one appears in the site's content, and whether
 * it puts a stylesheet or script on the front page regardless.
 *
 * Elementor widgets matter here. An Elementor add-on pack adds no blocks and no
 * shortcodes at all: its widgets are registered in PHP and stored inside the
 * _elementor_data field on each page. Counting only blocks would report every
 * Elementor add-on pack as "adds nothing, safe to remove", which is the most
 * expensive wrong answer this tool could give.
 *
 * @return array
 */
function wrenfold_triage_scan_plugins() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$rows      = array();
	$content   = wrenfold_triage_all_content();
	$elementor = wrenfold_triage_elementor_data();
	$assets    = wrenfold_triage_front_assets_by_plugin();

	foreach ( get_plugins() as $file => $data ) {
		// A one-file plugin such as Hello Dolly has no folder of its own. Reading
		// WP_PLUGIN_DIR for it would credit it with every other plugin's blocks.
		$folder   = dirname( $file );
		$target   = ( '.' === $folder ) ? WP_PLUGIN_DIR . '/' . $file : WP_PLUGIN_DIR . '/' . $folder;
		$features = wrenfold_triage_plugin_features( $target );
		$uses     = array();
		$total    = 0;
		$pages    = array();

		foreach ( $features['blocks'] as $block ) {
			$count = 0;
			foreach ( $content as $post ) {
				// One expression, so a self-closing block is not counted twice.
				$found = preg_match_all( '/<!-- wp:' . preg_quote( $block, '/' ) . '(?=[\s\/])/', $post->post_content );
				if ( $found ) {
					$pages[ wrenfold_triage_label( $post ) ] = true;
					$count += $found;
				}
			}
			$uses[] = array( 'kind' => 'block', 'name' => $block, 'count' => $count );
			$total += $count;
		}
		foreach ( $features['shortcodes'] as $shortcode ) {
			$count = 0;
			foreach ( $content as $post ) {
				// Match the whole name. Without this, [contact-form-7 ...] would also
				// be counted as a use of a shortcode called contact-form.
				$found = preg_match_all( '/\[' . preg_quote( $shortcode, '/' ) . '(?=[\s\]\/])/', $post->post_content );
				if ( $found ) {
					$pages[ wrenfold_triage_label( $post ) ] = true;
					$count += $found;
				}
			}
			$uses[] = array( 'kind' => 'shortcode', 'name' => $shortcode, 'count' => $count );
			$total += $count;
		}
		foreach ( $features['widgets'] as $widget ) {
			$count = 0;
			foreach ( $elementor as $label => $json ) {
				$found = preg_match_all( '/"widgetType"\s*:\s*"' . preg_quote( $widget, '/' ) . '"/', $json );
				if ( $found ) {
					$pages[ $label ] = true;
					$count += $found;
				}
			}
			$uses[] = array( 'kind' => 'Elementor widget', 'name' => $widget, 'count' => $count );
			$total += $count;
		}

		$loads = isset( $assets[ $folder ] ) ? $assets[ $folder ] : array();

		$rows[] = array(
			'file'      => $file,
			'name'      => $data['Name'],
			'version'   => $data['Version'],
			'active'    => is_plugin_active( $file ),
			'uses'      => $uses,
			'total'     => $total,
			'pages'     => array_keys( $pages ),
			'features'  => count( $uses ),
			'loads'     => $loads,
			'truncated' => $features['truncated'],
		);
	}

	usort(
		$rows,
		function ( $a, $b ) {
			return $b['features'] <=> $a['features'];
		}
	);

	return array(
		'plugins' => $rows,
		'overlap' => wrenfold_triage_overlap( $rows ),
	);
}

/**
 * Every page's stored Elementor layout, keyed by a readable page name.
 *
 * @return array<string,string>
 */
function wrenfold_triage_elementor_data() {
	$out = array();
	foreach ( wrenfold_triage_all_content() as $post ) {
		$data = get_post_meta( $post->ID, '_elementor_data', true );
		if ( is_array( $data ) ) {
			$data = wp_json_encode( $data );
		}
		if ( is_string( $data ) && '' !== trim( $data ) ) {
			$out[ wrenfold_triage_label( $post ) ] = $data;
		}
	}
	return $out;
}

/**
 * Which plugins put a stylesheet or script on the front page, whatever the page.
 *
 * This is the question behind "we have a slider plugin we do not really need":
 * a plugin used on one page, or on none, that still costs every visitor a file.
 *
 * @return array<string,string[]> Plugin folder name to the handles it asks for.
 */
function wrenfold_triage_front_assets_by_plugin() {
	// A scan started from the command line or from a form post has never drawn a
	// page, so nothing has registered its stylesheets yet. Ask for them now.
	// A badly written front-end callback can throw here; that is worth knowing
	// about, but it is not a reason to lose the rest of the scan.
	if ( ! did_action( 'wp_enqueue_scripts' ) ) {
		try {
			do_action( 'wp_enqueue_scripts' );
		} catch ( Throwable $e ) {
			return array();
		}
	}
	$base = trailingslashit( plugins_url() );
	$map  = array();
	foreach ( array( wp_styles(), wp_scripts() ) as $collection ) {
		foreach ( $collection->queue as $handle ) {
			if ( empty( $collection->registered[ $handle ]->src ) ) {
				continue;
			}
			$src = $collection->registered[ $handle ]->src;
			if ( 0 !== strpos( $src, $base ) ) {
				continue;
			}
			$folder = strtok( substr( $src, strlen( $base ) ), '/' );
			if ( $folder ) {
				$map[ $folder ][] = $handle;
			}
		}
	}
	return $map;
}

/**
 * Read a plugin folder and list what it adds to the site.
 *
 * This reads the plugin's own files rather than asking WordPress, so it also
 * covers plugins that are installed but switched off. Large packs run to
 * thousands of files, so PHP reading stops at a limit and says so: a truncated
 * list must never be turned into a "safe to remove" verdict.
 *
 * @param string $dir Plugin folder, or the file itself for a one-file plugin.
 * @return array{blocks:string[],shortcodes:string[],widgets:string[],truncated:bool}
 */
function wrenfold_triage_plugin_features( $dir ) {
	$blocks     = array();
	$shortcodes = array();
	$widgets    = array();
	$truncated  = false;
	$limit      = 2000;

	if ( is_file( $dir ) ) {
		$files = array( new SplFileInfo( $dir ) );
	} elseif ( is_dir( $dir ) ) {
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	} else {
		return compact( 'blocks', 'shortcodes', 'widgets', 'truncated' );
	}

	$read = 0;
	foreach ( $files as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$name = $file->getFilename();
		// block.json files are always read: they are small, and they are the
		// whole answer for a block plugin.
		if ( 'block.json' === $name ) {
			$json = json_decode( (string) file_get_contents( $file->getPathname() ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( ! empty( $json['name'] ) ) {
				$blocks[ $json['name'] ] = true;
			}
			continue;
		}
		if ( ! preg_match( '/\.php$/i', $name ) ) {
			continue;
		}
		if ( $read >= $limit ) {
			$truncated = true;
			continue;
		}
		++$read;
		$code = (string) file_get_contents( $file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( preg_match_all( '/register_block_type\(\s*[\'"]([a-z0-9-]+\/[a-z0-9-]+)[\'"]/i', $code, $m ) ) {
			foreach ( $m[1] as $found ) {
				$blocks[ $found ] = true;
			}
		}
		if ( preg_match_all( '/add_shortcode\(\s*[\'"]([a-z0-9_-]+)[\'"]/i', $code, $m ) ) {
			foreach ( $m[1] as $found ) {
				$shortcodes[ $found ] = true;
			}
		}
		// An Elementor widget: a class extending Widget_Base, whose get_name()
		// returns the name that ends up in the page's stored layout.
		if ( false !== strpos( $code, 'Widget_Base' ) && preg_match_all( '/function\s+get_name\s*\(\s*\)[^{]*\{\s*return\s*[\'"]([a-z0-9_-]+)[\'"]/i', $code, $m ) ) {
			foreach ( $m[1] as $found ) {
				$widgets[ $found ] = true;
			}
		}
	}

	$blocks     = array_keys( $blocks );
	$shortcodes = array_keys( $shortcodes );
	$widgets    = array_keys( $widgets );
	sort( $blocks );
	sort( $shortcodes );
	sort( $widgets );
	return compact( 'blocks', 'shortcodes', 'widgets', 'truncated' );
}

/**
 * Group features by what they do, so packs that duplicate each other show up.
 *
 * Two features count as the same when the last word of the name matches, and
 * that word is long enough to mean something. "contact-form-7" ends in "7",
 * which means nothing, so its full name is used instead.
 *
 * @param array $rows Plugin rows from the audit.
 * @return array
 */
function wrenfold_triage_overlap( $rows ) {
	$map = array();
	foreach ( $rows as $row ) {
		foreach ( $row['uses'] as $use ) {
			$parts = ( 'block' === $use['kind'] ) ? explode( '/', $use['name'] ) : preg_split( '/[_-]/', $use['name'] );
			$key   = end( $parts );
			if ( strlen( $key ) < 4 ) {
				$key = $use['name'];
			}
			if ( ! isset( $map[ $key ] ) ) {
				$map[ $key ] = array(
					'feature' => $key,
					'kind'    => $use['kind'],
					'by'      => array(),
					'used'    => 0,
				);
			}
			$map[ $key ]['by'][ $row['name'] ] = true;
			$map[ $key ]['used']              += $use['count'];
		}
	}
	$overlap = array();
	foreach ( $map as $entry ) {
		if ( count( $entry['by'] ) > 1 ) {
			$entry['by'] = array_keys( $entry['by'] );
			$overlap[]   = $entry;
		}
	}
	return $overlap;
}

/* ==========================================================================
 * 3. Which builder made which page
 * ======================================================================= */

/**
 * Work out what built each page, from the markup and the hidden fields every
 * builder leaves behind.
 *
 * Two things this has to get right, because both are easy to get wrong:
 *
 *   - An Elementor or BeBuilder page usually has an EMPTY post_content. The
 *     layout lives in post meta. Skipping empty pages would therefore skip
 *     exactly the pages this report is about.
 *   - A page can carry one builder's data while a different one draws it. A
 *     marker with a layout in it beats a marker that is just a leftover flag.
 *
 * @return array
 */
function wrenfold_triage_scan_builders() {
	// 'layout' keys hold the page itself. 'flag' keys only say a builder was
	// once switched on, which is not the same thing.
	$signatures = array(
		'Elementor'           => array(
			'meta'    => array( '_elementor_data' => 'layout', '_elementor_edit_mode' => 'flag' ),
			'content' => '',
		),
		'BeBuilder (BeTheme)' => array(
			'meta'    => array( 'mfn-page-items' => 'layout', 'mfn_page_items' => 'layout', '_mfn_builder_status' => 'flag' ),
			'content' => '',
		),
		'Divi Builder'        => array(
			'meta'    => array( '_et_pb_use_builder' => 'flag' ),
			'content' => '\[et_pb_section',
		),
		'WPBakery'            => array(
			'meta'    => array( '_wpb_vc_js_status' => 'flag' ),
			'content' => '\[vc_row',
		),
		'SiteOrigin'          => array(
			'meta'    => array( 'panels_data' => 'layout' ),
			'content' => '',
		),
		'Beaver Builder'      => array(
			'meta'    => array( '_fl_builder_data' => 'layout', '_fl_builder_enabled' => 'flag' ),
			'content' => '',
		),
	);
	// A page builder draws the page even when block markup is also stored, so
	// the builders come first and the block editor last.
	$order = array_merge( array_keys( $signatures ), array( 'Block editor', 'Classic editor' ) );

	$rows           = array();
	$counts         = array();
	$primary_counts = array();

	foreach ( wrenfold_triage_all_content() as $post ) {
		if ( ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
			continue;
		}
		$found  = array();
		$layout = array();
		$flag   = array();

		foreach ( $signatures as $builder => $sig ) {
			foreach ( $sig['meta'] as $key => $level ) {
				if ( ! metadata_exists( 'post', $post->ID, $key ) ) {
					continue;
				}
				$value = trim( (string) get_post_meta( $post->ID, $key, true ) );
				$empty = ( '' === $value || '[]' === $value || '{}' === $value );
				if ( 'layout' === $level && ! $empty ) {
					$layout[ $builder ] = true;
					$found[ $builder ][] = $key;
				} else {
					$flag[ $builder ] = true;
					$found[ $builder ][] = $key . ' ' . __( '(leftover, no layout in it)', 'wrenfold-triage' );
				}
			}
			if ( $sig['content'] && preg_match( '/' . $sig['content'] . '/i', $post->post_content ) ) {
				$layout[ $builder ]  = true;
				$found[ $builder ][] = __( 'shortcodes in the page text', 'wrenfold-triage' );
			}
		}

		if ( false !== strpos( $post->post_content, '<!-- wp:' ) ) {
			$layout['Block editor']  = true;
			$found['Block editor'][] = __( 'block markup', 'wrenfold-triage' );
		}

		if ( ! $found && '' === trim( $post->post_content ) ) {
			continue; // Nothing stored at all: a template draws this, not a builder.
		}
		if ( ! $found ) {
			$layout['Classic editor']  = true;
			$found['Classic editor'][] = __( 'plain HTML, no builder markers', 'wrenfold-triage' );
		}

		// Whichever builder actually holds a layout, in preference order.
		$primary = '';
		foreach ( $order as $candidate ) {
			if ( isset( $layout[ $candidate ] ) ) {
				$primary = $candidate;
				break;
			}
		}
		if ( ! $primary ) {
			foreach ( $order as $candidate ) {
				if ( isset( $flag[ $candidate ] ) ) {
					$primary = $candidate;
					break;
				}
			}
		}

		$primary_counts[ $primary ] = isset( $primary_counts[ $primary ] ) ? $primary_counts[ $primary ] + 1 : 1;
		foreach ( array_keys( $found ) as $builder ) {
			$counts[ $builder ] = isset( $counts[ $builder ] ) ? $counts[ $builder ] + 1 : 1;
		}

		$rows[] = array(
			'id'      => $post->ID,
			'label'   => wrenfold_triage_label( $post ),
			'type'    => $post->post_type,
			'primary' => $primary,
			'found'   => $found,
			'mixed'   => count( $found ) > 1,
		);
	}

	arsort( $counts );
	arsort( $primary_counts );
	$mixed = 0;
	foreach ( $rows as $row ) {
		if ( $row['mixed'] ) {
			++$mixed;
		}
	}

	return array(
		'rows'     => $rows,
		'counts'   => $counts,
		'primary'  => $primary_counts,
		'mixed'    => $mixed,
		'builders' => count( $counts ),
	);
}

/* ==========================================================================
 * 4. What breaks on a phone
 * ======================================================================= */

/**
 * Look through stored content and the active theme for the things that make a
 * page unusable on a narrow screen.
 *
 * This reads what is saved, not what a browser draws, so it cannot measure a
 * tap target. It finds the causes, which is what you need in order to fix them.
 *
 * @return array
 */
function wrenfold_triage_scan_mobile() {
	$tests = array(
		'fixed_width' => array(
			// A leading dash or letter means this is max-width or min-width, which
			// are handled separately. Only a plain width is a problem on its own.
			'pattern' => '/(?<![-a-z])width\s*:\s*(\d{3,})\s*px/i',
			'label'   => __( 'a width fixed in pixels', 'wrenfold-triage' ),
			'why'     => __( 'A box 900 pixels wide cannot fit a 390 pixel screen, so the page scrolls sideways.', 'wrenfold-triage' ),
			'min'     => 360,
		),
		'min_width'   => array(
			'pattern' => '/min-width\s*:\s*(\d{3,})\s*px/i',
			'label'   => __( 'a minimum width in pixels', 'wrenfold-triage' ),
			'why'     => __( 'A minimum width stops a box shrinking, which is the most common cause of sideways scrolling.', 'wrenfold-triage' ),
			'min'     => 360,
		),
		'nowrap'      => array(
			'pattern' => '/white-space\s*:\s*nowrap/i',
			'label'   => __( 'text told never to wrap', 'wrenfold-triage' ),
			'why'     => __( 'A long line that cannot wrap pushes the whole page wider than the screen.', 'wrenfold-triage' ),
			'min'     => 0,
		),
		'tiny_text'   => array(
			'pattern' => '/font-size\s*:\s*(\d|1[0-2])(\.\d+)?\s*px/i',
			'label'   => __( 'text under 13 pixels', 'wrenfold-triage' ),
			'why'     => __( 'Phone browsers zoom in on small text, and zooming often breaks the layout around it.', 'wrenfold-triage' ),
			'min'     => 0,
		),
	);

	$rows = array();
	foreach ( wrenfold_triage_all_content() as $post ) {
		$issues = array();
		foreach ( $tests as $key => $test ) {
			if ( ! preg_match_all( $test['pattern'], $post->post_content, $m ) ) {
				continue;
			}
			$worst = 0;
			if ( $test['min'] ) {
				foreach ( $m[1] as $value ) {
					$worst = max( $worst, (int) $value );
				}
				if ( $worst < $test['min'] ) {
					continue;
				}
			}
			$issues[] = array(
				'key'   => $key,
				'label' => $test['label'],
				'why'   => $test['why'],
				'count' => count( $m[0] ),
				'worst' => $worst,
			);
		}
		// Images with no size on them shift the page around as they load.
		if ( preg_match_all( '/<img\b(?![^>]*(?:\bwidth=|width\s*:|aspect-ratio\s*:))[^>]*>/i', $post->post_content, $m ) ) {
			$issues[] = array(
				'key'   => 'unsized_image',
				'label' => __( 'images with no width and height', 'wrenfold-triage' ),
				'why'   => __( 'The browser does not know how much room to leave, so the text jumps once the picture arrives.', 'wrenfold-triage' ),
				'count' => count( $m[0] ),
				'worst' => 0,
			);
		}
		// A wide table with nothing around it to let it scroll on its own.
		$bare_tables = 0;
		if ( preg_match_all( '/<table\b/i', $post->post_content, $m, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $m[0] as $hit ) {
				$before = substr( $post->post_content, max( 0, $hit[1] - 300 ), min( 300, $hit[1] ) );
				if ( ! preg_match( '/overflow-x|role=["\']region["\']|is-style-stacked-on-mobile/i', $before ) ) {
					++$bare_tables;
				}
			}
		}
		if ( $bare_tables ) {
			$issues[] = array(
				'key'   => 'bare_table',
				'label' => __( 'a table with no way to scroll it', 'wrenfold-triage' ),
				'why'   => __( 'A four-column table is wider than a phone. Without a scrolling box around it, the whole page moves instead.', 'wrenfold-triage' ),
				'count' => $bare_tables,
				'worst' => 0,
			);
		}
		if ( $issues ) {
			$rows[] = array(
				'id'     => $post->ID,
				'label'  => wrenfold_triage_label( $post ),
				'status' => $post->post_status,
				'issues' => $issues,
			);
		}
	}

	$checked = count( wrenfold_triage_all_content() );
	return array(
		'rows'    => $rows,
		'checked' => $checked,
		'clean'   => $checked - count( $rows ),
	);
}

/* ==========================================================================
 * 5. Stylesheets and scripts that point at files which are not there
 * ======================================================================= */

/**
 * Check every registered stylesheet and script against the disk.
 *
 * A handle whose file is missing is the usual reason a button suddenly renders
 * as a plain link: the markup is fine, the rule that styles it never arrives.
 *
 * @return array
 */
function wrenfold_triage_scan_assets() {
	$rows = array();
	// Make sure the front end has had its chance to register things, or this
	// check would pass by having looked at an empty list.
	wrenfold_triage_front_assets_by_plugin();
	foreach ( array( 'styles' => wp_styles(), 'scripts' => wp_scripts() ) as $kind => $collection ) {
		foreach ( $collection->registered as $handle => $asset ) {
			if ( empty( $asset->src ) || 0 === strpos( $asset->src, '//' ) ) {
				continue;
			}
			$src = $asset->src;
			if ( ! preg_match( '#^https?://#i', $src ) ) {
				continue;
			}
			$base = content_url();
			if ( 0 !== strpos( $src, $base ) ) {
				continue; // Only files under wp-content can be checked against this disk.
			}
			$path   = WP_CONTENT_DIR . substr( strtok( $src, '?' ), strlen( $base ) );
			$exists = file_exists( $path );
			if ( ! $exists ) {
				$rows[] = array(
					'kind'   => $kind,
					'handle' => $handle,
					'src'    => $src,
					'path'   => $path,
				);
			}
		}
	}
	return $rows;
}

/* ==========================================================================
 * Putting it together
 * ======================================================================= */

/**
 * Run every check and store the result.
 *
 * @return array The scan.
 */
function wrenfold_triage_run_scan() {
	$security = wrenfold_triage_scan_security();
	$plugins  = wrenfold_triage_scan_plugins();
	$assets   = wrenfold_triage_scan_assets();

	/* Plugins that cost every visitor a file and are barely used. ---------- */
	$always_on = array();
	foreach ( $plugins['plugins'] as $row ) {
		if ( ! $row['active'] || ! $row['loads'] || $row['truncated'] || $row['features'] < 1 ) {
			continue;
		}
		if ( $row['total'] > 2 ) {
			continue; // Used across the site. Its files are earning their place.
		}
		$always_on[] = sprintf(
			/* translators: 1: plugin name, 2: number of files, 3: number of uses. */
			__( '%1$s asks for %2$d file on every page view and is used %3$d times', 'wrenfold-triage' ),
			$row['name'],
			count( $row['loads'] ),
			$row['total']
		);
	}
	if ( $always_on ) {
		$security[] = wrenfold_triage_finding(
			'always_on',
			'medium',
			sprintf(
				/* translators: %d: number of plugins. */
				_n( '%d plugin loads a file on every page view but is barely used', '%d plugins load a file on every page view but are barely used', count( $always_on ), 'wrenfold-triage' ),
				count( $always_on )
			),
			implode( '; ', $always_on ),
			__( 'Most add-on packs and slider plugins register their stylesheet on every page rather than working out which pages actually need it. Every visitor then pays for a file that does nothing on the page they are looking at, and phones pay the most.', 'wrenfold-triage' ),
			__( 'If it is used nowhere, remove it. If it is used on one page, load it only on that page: a few lines in a small plugin of your own, which survives the next update of theirs.', 'wrenfold-triage' ),
			''
		);
	} else {
		$security[] = wrenfold_triage_finding( 'always_on', 'ok', __( 'No plugin loads files on every page view without being used', 'wrenfold-triage' ), __( 'checked what the front page asks for against where each plugin is used', 'wrenfold-triage' ), '', '' );
	}

	if ( $assets ) {
		$names = array();
		foreach ( $assets as $asset ) {
			$names[] = $asset['handle'];
		}
		$security[] = wrenfold_triage_finding(
			'missing_asset',
			'high',
			sprintf(
				/* translators: %d: number of stylesheets or scripts. */
				_n( '%d registered stylesheet or script points at a file that is not on the server', '%d registered stylesheets or scripts point at files that are not on the server', count( $assets ), 'wrenfold-triage' ),
				count( $assets )
			),
			implode( ', ', $names ),
			__( 'A file gets removed during a clean-up or a plugin change, but the line of code that asks for it stays. The page still loads, the rules in that file never arrive, and anything they styled falls back to plain browser defaults. A styled button becomes an underlined link.', 'wrenfold-triage' ),
			__( 'Either put the file back or stop asking for it. Do not paste the old rules into the theme: the next update overwrites them and the problem returns.', 'wrenfold-triage' ),
			''
		);
	} else {
		$security[] = wrenfold_triage_finding( 'missing_asset', 'ok', __( 'Every registered stylesheet and script is on the server', 'wrenfold-triage' ), __( 'checked each one under wp-content against the disk', 'wrenfold-triage' ), '', '' );
	}

	$counts = array( 'high' => 0, 'medium' => 0, 'low' => 0, 'ok' => 0 );
	foreach ( $security as $finding ) {
		++$counts[ $finding['severity'] ];
	}

	$scan = array(
		'time'     => time(),
		'security' => $security,
		'counts'   => $counts,
		'plugins'  => $plugins,
		'builders' => wrenfold_triage_scan_builders(),
		'mobile'   => wrenfold_triage_scan_mobile(),
		'assets'   => $assets,
	);

	update_option( WRENFOLD_TRIAGE_OPTION, $scan, false );
	return $scan;
}

/**
 * The stored scan, or an empty one if nothing has been run yet.
 *
 * @return array
 */
function wrenfold_triage_get_scan() {
	$scan = get_option( WRENFOLD_TRIAGE_OPTION );
	return is_array( $scan ) ? $scan : array();
}

/**
 * Open a box that can scroll sideways on a narrow screen.
 *
 * The box is focusable and has a name, so somebody using a keyboard can reach
 * it and somebody using a screen reader is told what is inside. A scrolling
 * box without those two things is unreachable without a mouse.
 *
 * @param string $label Accessible name for the box.
 */
function wrenfold_report_open_scroll( $label ) {
	printf(
		'<div class="wf-report__scroll" role="region" tabindex="0" aria-label="%s">',
		esc_attr( $label )
	);
}

/**
 * Hide the server's folder layout in anything shown to a visitor.
 *
 * A findings list is useful to the owner and useful to somebody probing the
 * site. The public page gets the file name, not the road to it.
 *
 * @param string $text Any text that may hold a path.
 * @return string
 */
function wrenfold_triage_shorten_paths( $text ) {
	$text = str_replace( array( WP_CONTENT_DIR, untrailingslashit( ABSPATH ) ), '', (string) $text );
	return ltrim( $text, '/\\' );
}
