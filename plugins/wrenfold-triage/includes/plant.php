<?php
/**
 * Setting up the demo.
 *
 * This file exists so the scanner has something real to find. It breaks this
 * install on purpose, in the ways a site that has been through a clean-up is
 * usually broken. It is only ever called once, by the demo content script.
 *
 * Two things it deliberately does not do:
 *   - It writes no malicious code. The file it drops in the uploads folder
 *     holds a comment and nothing else. The check is about a runnable file
 *     being in a folder meant for pictures, not about what the file contains.
 *   - It does not write any findings. Every screen is produced by reading the
 *     install afterwards, so the numbers change when the site changes.
 *
 * On a real project this file would not be shipped at all.
 *
 * @package WrenfoldTriage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Break this install in the ways the scan is built to find.
 *
 * @return string[] Lines describing what was done, for the setup log.
 */
function wrenfold_triage_plant_demo_problems() {
	$done = array();

	/* A runnable file where only pictures belong. -------------------------- */
	$uploads = wp_get_upload_dir();
	$folder  = trailingslashit( $uploads['basedir'] ) . '2026/08';
	if ( wp_mkdir_p( $folder ) ) {
		$file = $folder . '/wf-cache-config.php';
		if ( ! file_exists( $file ) ) {
			file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				$file,
				"<?php\n"
				. "// Planted by the Wrenfold Site Triage demo.\n"
				. "// A real one would contain code. This one contains this comment, on purpose.\n"
				. "// The point of the check is that PHP can run from the uploads folder at all.\n"
			);
			$done[] = 'Planted: a PHP file in the uploads folder.';
		}
	}

	/* A scheduled job for a plugin that is not installed. ------------------ */
	if ( ! wp_next_scheduled( 'wf_legacy_sync_worker' ) ) {
		wp_schedule_event( time() + 2 * DAY_IN_SECONDS, 'twicedaily', 'wf_legacy_sync_worker' );
		$done[] = 'Planted: a scheduled job with no code behind it.';
	}

	/* A second administrator, created today. ------------------------------- */
	if ( ! get_user_by( 'login', 'site_updater' ) ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => 'site_updater',
				'user_pass'    => wp_generate_password( 24, true, true ),
				'user_email'   => 'site.updater@example.invalid',
				'display_name' => 'Site Updater',
				'role'         => 'administrator',
			)
		);
		if ( ! is_wp_error( $user_id ) ) {
			$done[] = 'Planted: a second administrator account.';
		}
	}

	/* Sign-ups left switched on. -------------------------------------------- */
	update_option( 'users_can_register', 1 );
	$done[] = 'Planted: visitor sign-ups switched on.';

	/* A stray file in the WordPress folder. --------------------------------- */
	$stray = ABSPATH . 'wp-maintenance-mode.php';
	if ( ! file_exists( $stray ) ) {
		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$stray,
			"<?php\n// Planted by the Wrenfold Site Triage demo. WordPress does not ship a file with this name.\n"
		);
		$done[] = 'Planted: a stray PHP file in the WordPress folder.';
	}

	/* Leftover fields from other page builders. ------------------------------ */
	$about = get_page_by_path( 'about' );
	if ( $about && ! metadata_exists( 'post', $about->ID, '_elementor_data' ) ) {
		update_post_meta( $about->ID, '_elementor_data', wp_json_encode( array( array( 'elType' => 'section', 'elements' => array() ) ) ) );
		update_post_meta( $about->ID, '_elementor_edit_mode', 'builder' );
		$done[] = 'Planted: leftover Elementor data on the About page.';
	}
	$make = get_page_by_path( 'what-we-make' );
	if ( $make && ! metadata_exists( 'post', $make->ID, 'mfn-page-items' ) ) {
		update_post_meta( $make->ID, 'mfn-page-items', '[]' );
		$done[] = 'Planted: leftover BeBuilder data on the What we make page.';
	}

	/* Two pages a different builder actually draws. --------------------------
	 * An Elementor or BeBuilder page normally has an EMPTY post_content: the
	 * layout lives in post meta. Without a page like that, the builder screen
	 * only ever sees block pages, which is not the site this job is about.
	 */
	if ( ! get_page_by_path( 'old-services-page' ) ) {
		$elementor = wp_json_encode(
			array(
				array(
					'elType'   => 'section',
					'elements' => array(
						array(
							'elType'     => 'widget',
							'widgetType' => 'heading',
							'settings'   => array( 'title' => 'Our services' ),
						),
						array(
							'elType'     => 'widget',
							'widgetType' => 'wf-bench-spec',
							'settings'   => array(),
						),
					),
				),
			)
		);
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_name'    => 'old-services-page',
				'post_title'   => 'Services (old Elementor page)',
				'post_content' => '',
				'meta_input'   => array(
					'_elementor_data'      => $elementor,
					'_elementor_edit_mode' => 'builder',
				),
			)
		);
		$done[] = 'Planted: a page whose layout lives in Elementor data, not in the page text.';
	}
	if ( ! get_page_by_path( 'old-home-page' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_name'    => 'old-home-page',
				'post_title'   => 'Home (old BeBuilder page)',
				'post_content' => '',
				'meta_input'   => array(
					'mfn-page-items'      => '[{"item":"wrap","items":[{"item":"column","size":"1/1"}]}]',
					'_mfn_builder_status' => 'true',
				),
			)
		);
		$done[] = 'Planted: a page whose layout lives in BeBuilder data, not in the page text.';
	}

	/* The old homepage, kept the way a migration usually keeps it. ----------- */
	if ( ! wrenfold_triage_find_block( 'Old homepage hero (before the rebuild)' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'wp_block',
				'post_status'  => 'draft',
				'post_title'   => 'Old homepage hero (before the rebuild)',
				'post_content' => wrenfold_triage_old_hero_markup(),
			)
		);
		$done[] = 'Planted: the old homepage section, with the mobile problems it had.';
	}

	/* Content with something hidden in it. ------------------------------------ */
	if ( ! wrenfold_triage_find_block( 'Footer promo (do not use)' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'wp_block',
				'post_status'  => 'draft',
				'post_title'   => 'Footer promo (do not use)',
				'post_content' => wrenfold_triage_injected_markup(),
			)
		);
		$done[] = 'Planted: a saved section with hidden off-screen markup in it.';
	}

	return $done;
}

/**
 * Find a saved block section by title.
 *
 * @param string $title Title.
 * @return int Post ID, or 0.
 */
function wrenfold_triage_find_block( $title ) {
	$found = get_posts(
		array(
			'post_type'   => 'wp_block',
			'title'       => $title,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * The old homepage section, as a page builder left it.
 *
 * Fixed pixel widths, a table with nothing to scroll it, pictures with no size
 * and text under 13px. This is what the mobile check is looking for.
 *
 * @return string
 */
function wrenfold_triage_old_hero_markup() {
	return '<div class="legacy-hero" style="width: 1180px; min-width: 980px;">'
		. '<h2 style="font-size: 44px; white-space: nowrap;">Handmade joinery since the beginning</h2>'
		. '<img src="' . esc_url( get_theme_file_uri( 'assets/images/hero-joint.svg' ) ) . '" alt="Workshop bench">'
		. '<table style="width: 900px;"><tr><th>Service</th><th>From</th><th>Lead time</th><th>Deposit</th></tr>'
		. '<tr><td>Kitchen</td><td>on request</td><td>6 to 10 weeks</td><td>30%</td></tr>'
		. '<tr><td>Staircase</td><td>on request</td><td>4 to 6 weeks</td><td>30%</td></tr></table>'
		. '<p style="font-size: 11px;">Prices are a guide only and exclude fitting.</p>'
		. '</div>';
}

/**
 * A saved section with markup hidden out of sight.
 *
 * The links go nowhere: they point at the reserved example domain, so nothing
 * here sends anyone anywhere. The shape is what matters.
 *
 * @return string
 */
function wrenfold_triage_injected_markup() {
	return '<div class="promo"><p>Winter offer</p>'
		. '<div style="position: absolute; left: -9999px;">'
		. '<a href="https://example.com/one/">cheap loans</a> '
		. '<a href="https://example.com/two/">discount pills</a>'
		. '</div></div>';
}
