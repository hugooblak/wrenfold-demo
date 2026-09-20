<?php
/**
 * Demo content for the Wrenfold Joinery site.
 *
 * Creates the pages, posts, FAQ library, the quote form and the settings, then
 * breaks the install on purpose so the Site Triage scan has something real to
 * find, and runs one scan so the public report is not empty.
 *
 * Run once on a fresh install. WordPress Playground does this automatically
 * through blueprint.json. Safe to run again: it skips anything that exists.
 *
 * Local run:  wp eval-file content/setup.php
 *
 * This is demo scaffolding only. On a real project, content is entered by the
 * team in WordPress, not by a script.
 *
 * @package WrenfoldJoinery
 */

defined( 'ABSPATH' ) || exit;

// Act as the admin, so WordPress keeps the block markup exactly as written.
wp_set_current_user( 1 );

/* --------------------------------------------------------------------------
 * Helpers
 * ----------------------------------------------------------------------- */

/**
 * Get a theme pattern's markup, with nested pattern references expanded,
 * so saved pages contain plain, editable blocks.
 *
 * @param string $slug Pattern slug.
 * @return string
 */
function wf_setup_pattern( $slug ) {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	if ( ! $pattern ) {
		wf_setup_log( "Missing pattern: $slug" );
		return '';
	}
	return preg_replace_callback(
		'#<!-- wp:pattern \{"slug":"([^"]+)"\} /-->#',
		function ( $m ) {
			return wf_setup_pattern( $m[1] );
		},
		$pattern['content']
	);
}

function wf_setup_log( $msg ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $msg );
	}
}

function wf_setup_page( $slug, $title, $content, $excerpt, $template = '' ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'meta_input'   => $template ? array( '_wp_page_template' => $template ) : array(),
		)
	);
	wf_setup_log( "Page: $title" );
	return $id;
}

function wf_setup_find( $title, $post_type ) {
	$found = get_posts(
		array(
			'post_type'   => $post_type,
			'title'       => $title,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $found ? (int) $found[0] : 0;
}

function wf_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>$text</p>\n<!-- /wp:paragraph -->\n\n";
}
function wf_h( $text, $level = 2 ) {
	$attr = 2 === $level ? '' : ' {"level":' . $level . '}';
	return "<!-- wp:heading$attr -->\n<h$level class=\"wp-block-heading\">$text</h$level>\n<!-- /wp:heading -->\n\n";
}
function wf_ul( $items ) {
	$out = "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
	foreach ( $items as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>$item</li>\n<!-- /wp:list-item -->";
	}
	return $out . "</ul>\n<!-- /wp:list -->\n\n";
}
function wf_ol( $items ) {
	$out = "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">";
	foreach ( $items as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>$item</li>\n<!-- /wp:list-item -->";
	}
	return $out . "</ol>\n<!-- /wp:list -->\n\n";
}

/* --------------------------------------------------------------------------
 * Settings
 * ----------------------------------------------------------------------- */

update_option( 'blogname', 'Wrenfold Joinery' );
update_option( 'blogdescription', 'Fitted kitchens, staircases and shopfitting' );
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' ); // Pretty links like /what-we-make/.
update_option( 'default_comment_status', 'closed' );
update_option( 'timezone_string', 'Europe/London' );

// Remove WordPress's sample content, and the empty privacy page it drafts on
// install, so this script can write a real one.
foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $wf_slug => $wf_type ) {
	$wf_sample = get_page_by_path( $wf_slug, OBJECT, $wf_type );
	if ( $wf_sample ) {
		wp_delete_post( $wf_sample->ID, true );
	}
}

/* --------------------------------------------------------------------------
 * Blog categories and posts
 * ----------------------------------------------------------------------- */

wp_update_term( 1, 'category', array( 'name' => 'Workshop', 'slug' => 'workshop' ) );
$wf_cat_advice = term_exists( 'before-you-buy', 'category' ) ?: wp_insert_term( 'Before you buy', 'category', array( 'slug' => 'before-you-buy', 'description' => 'What to ask, and what to watch out for, before ordering fitted furniture.' ) );

$wf_posts = array(
	array(
		'slug'    => 'how-we-price-a-fitted-kitchen',
		'title'   => 'How we price a fitted kitchen',
		'cat'     => $wf_cat_advice['term_id'],
		'date'    => '-4 days',
		'excerpt' => 'Four things move the price of a kitchen, and only one of them is the size of the room.',
		'content' => wf_p( 'People expect a kitchen to be priced by the metre. It never works out that way, because two rooms of the same size can be a week apart in workshop time.' )
			. wf_h( 'What actually moves the price' )
			. wf_ol(
				array(
					'<strong>The number of doors and drawers.</strong> Each one is a separate piece of work: made, fitted, painted, hung and adjusted. A run of tall cupboards is cheaper than the same length in drawers.',
					'<strong>The timber.</strong> Painted tulipwood is the cheapest way to get a solid door. Oak costs more and takes longer to finish.',
					'<strong>The state of the room.</strong> A level floor and square walls save a day. An old house rarely gives you either.',
					'<strong>The worktop.</strong> Ours is priced separately, because stone is bought in and wood is not.',
				)
			)
			. wf_h( 'What does not move it' )
			. wf_p( 'How soon you want it. We do not charge more for a rush, we just tell you honestly whether the date is possible.' )
			. wf_p( 'If you want a number before anyone visits, send the room measurements through the <a href="' . esc_url( home_url( '/contact/' ) ) . '">quote form</a>. A range takes us about ten minutes to work out.' ),
	),
	array(
		'slug'    => 'why-we-template-stairs-on-site',
		'title'   => 'Why we template stairs on site',
		'cat'     => 1,
		'date'    => '-12 days',
		'excerpt' => 'A staircase that is measured with a tape rarely fits. One made against a full-size template does.',
		'content' => wf_p( 'A stairwell is almost never square. The walls lean, the floor slopes, and the opening at the top is a few millimetres out from the one at the bottom. A tape measure cannot record any of that.' )
			. wf_h( 'What a template is' )
			. wf_p( 'Thin strips of ply, pinned together in the stairwell until they copy the exact shape of the opening. The template comes back to the workshop and the strings are cut against it.' )
			. wf_h( 'What it saves' )
			. wf_ul(
				array(
					'No packing pieces behind the treads, so nothing creaks in a year.',
					'No filler along the wall, so the paint line stays straight.',
					'One fitting visit instead of two.',
				)
			)
			. wf_p( 'It costs us half a day. It has never once cost more than getting it wrong.' ),
	),
	array(
		'slug'    => 'what-six-weeks-really-means',
		'title'   => 'What a six week lead time really means',
		'cat'     => 1,
		'date'    => '-25 days',
		'excerpt' => 'Two of those weeks are paint drying. Here is where the rest of the time goes.',
		'content' => wf_p( 'Lead times sound like padding. They are mostly drying.' )
			. wf_h( 'Where six weeks goes' )
			. wf_ul(
				array(
					'Week 1: timber ordered, cut and left to settle in the workshop.',
					'Weeks 2 and 3: carcasses and doors made.',
					'Weeks 4 and 5: paint. Three coats, each one sanded, each one needing a day.',
					'Week 6: hardware fitted, everything test-built on the bench, then loaded.',
				)
			)
			. wf_p( 'Rushing the paint is the one shortcut that always shows. It stays soft, marks with a fingernail, and then it is your kitchen that looks cheap, not our schedule.' ),
	),
);

foreach ( $wf_posts as $wf_post ) {
	if ( get_page_by_path( $wf_post['slug'], OBJECT, 'post' ) ) {
		continue;
	}
	wp_insert_post(
		array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_name'     => $wf_post['slug'],
			'post_title'    => $wf_post['title'],
			'post_content'  => $wf_post['content'],
			'post_excerpt'  => $wf_post['excerpt'],
			'post_category' => array( (int) $wf_post['cat'] ),
			'post_date'     => wp_date( 'Y-m-d H:i:s', strtotime( $wf_post['date'] ) ),
		)
	);
	wf_setup_log( 'Post: ' . $wf_post['title'] );
}

/* --------------------------------------------------------------------------
 * FAQ library
 * ----------------------------------------------------------------------- */

$wf_faqs = array(
	'quotes-and-prices'  => array(
		'name'  => 'Quotes and prices',
		'items' => array(
			array( 'Do you charge for a quote?', 'No. The visit, the measuring and the drawing are free, and you keep the drawing whether or not you book us.' ),
			array( 'Will the price change once you start?', 'Only if you change something, and only after you have seen the new price in writing. Rot under a floor is the one exception, and we stop and show you before doing anything about it.' ),
			array( 'Can you work to a budget?', 'Yes, if you tell us the number early. Most of the saving comes from fewer drawers and painted doors instead of oak, not from thinner timber.' ),
			array( 'What deposit do you take?', 'Thirty per cent when you sign the drawing, the rest on the day we finish fitting. Nothing in between.' ),
		),
	),
	'timings'            => array(
		'name'  => 'Timings',
		'items' => array(
			array( 'How long does a kitchen take?', 'Six to ten weeks from the day you sign the drawing. Two of those weeks are paint drying, which is the part that cannot be shortened.' ),
			array( 'How long are you in the house?', 'Three to five days for a kitchen, usually two for a staircase. We work 8am to 4pm and clear up at the end of each day.' ),
			array( 'How far ahead are you booked?', 'Normally six to eight weeks. Ask when you send the measurements and we will tell you the honest date, not the one you want to hear.' ),
		),
	),
	'materials-and-care' => array(
		'name'  => 'Materials and looking after them',
		'items' => array(
			array( 'What timber do you use?', 'Oak and ash where the grain is going to be seen, tulipwood where the piece is being painted, and birch ply for drawer boxes. We buy from two mills and keep the delivery notes.' ),
			array( 'How do I look after a painted door?', 'Warm water and a cloth. Not a kitchen spray: the ones that cut grease also soften paint over a few years.' ),
			array( 'Will a solid door warp?', 'A frame-and-panel door moves with the seasons by design, which is why it does not split. A flat slab of solid timber does warp, so we do not make doors that way.' ),
			array( 'What if something goes wrong later?', 'Ring us. Hinges and runners are standard sizes on purpose, so a worn part is a small job and not a new door.' ),
		),
	),
);

foreach ( $wf_faqs as $wf_topic_slug => $wf_topic ) {
	$wf_term = term_exists( $wf_topic_slug, 'wf_faq_topic' ) ?: wp_insert_term( $wf_topic['name'], 'wf_faq_topic', array( 'slug' => $wf_topic_slug ) );
	foreach ( $wf_topic['items'] as $wf_order => $wf_item ) {
		$wf_exists = get_posts(
			array(
				'post_type'   => 'wf_faq',
				'title'       => $wf_item[0],
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		if ( $wf_exists ) {
			continue;
		}
		$wf_faq_id = wp_insert_post(
			array(
				'post_type'    => 'wf_faq',
				'post_status'  => 'publish',
				'post_title'   => $wf_item[0],
				'post_content' => wf_p( $wf_item[1] ),
				'menu_order'   => ( $wf_order + 1 ) * 10, // 10, 20, 30: leaves room to slot new FAQs in between.
			)
		);
		wp_set_object_terms( $wf_faq_id, (int) $wf_term['term_id'], 'wf_faq_topic' );
	}
	wf_setup_log( 'FAQ topic: ' . $wf_topic['name'] );
}

/* --------------------------------------------------------------------------
 * The quote form (Contact Form 7)
 * ----------------------------------------------------------------------- */

/**
 * Create a form once and return the block that shows it.
 *
 * @param string $title   Form name in the admin.
 * @param string $form    Form markup.
 * @param string $subject Email subject line.
 * @return string Block markup.
 */
function wf_setup_form( $title, $form, $subject ) {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return wf_p( '<em>Form plugin not active.</em>' );
	}
	$existing = wf_setup_find( $title, 'wpcf7_contact_form' );
	if ( $existing ) {
		$contact_form = wpcf7_contact_form( $existing );
	} else {
		$contact_form      = WPCF7_ContactForm::get_template( array( 'title' => $title ) );
		$mail              = $contact_form->prop( 'mail' );
		$mail['subject']   = $subject;
		$mail['recipient'] = '[_site_admin_email]';
		$contact_form->set_properties(
			array(
				'form'                => $form,
				'mail'                => $mail,
				'mail_2'              => array_merge( $contact_form->prop( 'mail_2' ), array( 'active' => false ) ),
				// Demo mode: the form validates and shows the thank-you message, but sends no email.
				'additional_settings' => 'demo_mode: on',
				'messages'            => array_merge(
					$contact_form->prop( 'messages' ),
					array( 'mail_sent_ok' => 'Thanks. We read every one of these ourselves and reply within two working days.' )
				),
			)
		);
		$contact_form->save();
		$contact_form = wpcf7_contact_form( $contact_form->id() ); // Reload to get the saved hash.
		wf_setup_log( "Form: $title" );
	}
	return sprintf(
		"<!-- wp:contact-form-7/contact-form-selector {\"id\":%d,\"hash\":\"%s\",\"title\":\"%s\"} -->\n<div class=\"wp-block-contact-form-7-contact-form-selector\">[contact-form-7 id=\"%s\" title=\"%s\"]</div>\n<!-- /wp:contact-form-7/contact-form-selector -->\n\n",
		$contact_form->id(),
		$contact_form->hash(),
		esc_attr( $title ),
		$contact_form->hash(),
		esc_attr( $title )
	);
}

// Remove the sample form Contact Form 7 creates on activation.
$wf_default_form = wf_setup_find( 'Contact form 1', 'wpcf7_contact_form' );
if ( $wf_default_form ) {
	wp_delete_post( $wf_default_form, true );
}

$wf_quote_form = wf_setup_form(
	'Quote request',
	'<p><label for="wf-quote-name">Your name</label> [text* your-name id:wf-quote-name autocomplete:name]</p>

<p><label for="wf-quote-email">Email</label> [email* your-email id:wf-quote-email autocomplete:email]</p>

<p><label for="wf-quote-phone">Phone <span class="wf-optional">(optional)</span></label> [tel your-phone id:wf-quote-phone autocomplete:tel]</p>

<p><label for="wf-quote-work">What do you need making?</label> [select* your-work id:wf-quote-work first_as_label "Choose one" "A fitted kitchen" "A staircase or handrail" "Shopfitting" "Something else"]</p>

<p><label for="wf-quote-when">When would you like it fitted?</label> [select* your-when id:wf-quote-when first_as_label "Choose one" "As soon as you can" "In the next three months" "Later this year" "Just getting prices for now"]</p>

<p><label for="wf-quote-room">Rough measurements, and anything we should know</label> [textarea* your-room id:wf-quote-room]</p>

<p>[submit "Send this to the workshop"]</p>',
	'Quote request: [your-work]'
);

/* --------------------------------------------------------------------------
 * Pages
 * ----------------------------------------------------------------------- */

$wf_home = wf_setup_page(
	'home',
	'Home',
	wf_setup_pattern( 'wrenfold/page-home' ),
	'Wrenfold Joinery makes fitted kitchens, staircases and shop interiors. Measured, drawn, made and fitted by the same small team.'
);

wf_setup_page(
	'what-we-make',
	'What we make',
	wf_setup_pattern( 'wrenfold/page-what-we-make' ),
	'Fitted kitchens, staircases and handrails, and counters for small shops. What is included in every quote, and how long each takes.',
	'page-landing'
);

wf_setup_page(
	'about',
	'The workshop',
	wf_setup_pattern( 'wrenfold/page-about' ),
	'A four-bench joinery workshop. How we got here, what you can come and see, and how a job runs from first visit to final check.',
	'page-landing'
);

wf_setup_page(
	'faqs',
	'Questions',
	wf_setup_pattern( 'wrenfold/page-faqs' ),
	'Answers about quotes and prices, how long a job takes, and the timber we use.',
	'page-landing'
);

wf_setup_page(
	'site-health-report',
	'Site health report',
	wf_setup_pattern( 'wrenfold/page-health-report' ),
	'The result of the last Site Triage scan of this WordPress install, read live and written in plain language.',
	'page-landing'
);

wf_setup_page(
	'contact',
	'Ask for a quote',
	wf_p( 'Send the room measurements and anything you already know about what you want. You get a price range back within two working days, and nobody rings you unless you ask.' )
		. wf_p( 'If you would rather come to us, the workshop is open Tuesday to Friday, 8am to 4pm. There is usually a kitchen on the bench you can open and shut.' )
		. $wf_quote_form,
	'Send Wrenfold Joinery your room measurements and get a price range back within two working days.'
);

$wf_privacy = wf_setup_page(
	'privacy-policy',
	'Privacy',
	wf_p( 'Wrenfold Joinery is a fictional brand built for a portfolio demo. Nothing is sent anywhere and no real data is stored. This page is here because a real small business site needs one, and because the tests check that it exists.' )
		. wf_h( 'What a real version of this page would say' )
		. wf_ul(
			array(
				'What the quote form collects: your name, your email address, your phone number if you give it, and what you wrote about the room.',
				'Why: to send you a price and to answer your question. Nothing else.',
				'How long it is kept: until the job is finished, or twelve months if you never book.',
				'Who else sees it: nobody outside the workshop.',
				'How to get a copy or have it deleted: one email, answered within a month.',
			)
		)
		. wf_p( 'On this demo the form is in test mode. It checks what you type and shows the thank-you message, and it sends no email to anyone.' ),
	'How a real version of this site would handle the details you send through the quote form.'
);
update_option( 'wp_page_for_privacy_policy', $wf_privacy );

$wf_blog = wf_setup_page( 'blog', 'Workshop notes', '', 'What we are making, what went wrong and what we changed because of it.' );

/* --------------------------------------------------------------------------
 * Main menu (edited in Appearance > Editor > Navigation)
 * ----------------------------------------------------------------------- */

// WordPress may have auto-created an empty fallback menu called "Navigation".
// Remove it so the team only sees the one menu the header actually uses.
$wf_fallback_menu = get_page_by_path( 'navigation', OBJECT, 'wp_navigation' );
if ( $wf_fallback_menu ) {
	wp_delete_post( $wf_fallback_menu->ID, true );
}

if ( ! get_page_by_path( 'wrenfold-main-menu', OBJECT, 'wp_navigation' ) ) {
	$wf_menu  = '';
	$wf_links = array(
		'what-we-make'       => 'What we make',
		'about'              => 'The workshop',
		'faqs'               => 'Questions',
		'site-health-report' => 'Site health',
		'contact'            => 'Ask for a quote',
	);
	foreach ( $wf_links as $wf_slug => $wf_label ) {
		$wf_page = get_page_by_path( $wf_slug );
		// The quote link only shows on small screens, where the header button is hidden.
		$wf_class = 'contact' === $wf_slug ? ',"className":"wf-nav-quote-link"' : '';
		$wf_menu .= sprintf(
			'<!-- wp:navigation-link {"label":"%s","type":"page","id":%d,"url":"%s","kind":"post-type"%s} /-->',
			esc_attr( $wf_label ),
			$wf_page->ID,
			esc_url( get_permalink( $wf_page ) ),
			$wf_class
		);
	}
	wp_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_name'    => 'wrenfold-main-menu',
			'post_title'   => 'Main menu',
			'post_content' => $wf_menu,
		)
	);
	wf_setup_log( 'Menu: Main menu' );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $wf_home );
update_option( 'page_for_posts', $wf_blog );

// Clear the saved link rules. WordPress rebuilds them on the next page load,
// with every post type and category registered (flushing here would miss categories).
delete_option( 'rewrite_rules' );

/* --------------------------------------------------------------------------
 * Break the install on purpose, then scan it
 * ----------------------------------------------------------------------- */

if ( function_exists( 'wrenfold_triage_plant_demo_problems' ) ) {
	foreach ( wrenfold_triage_plant_demo_problems() as $wf_line ) {
		wf_setup_log( $wf_line );
	}
	// One scan now, so the public report page is not empty on a fresh install.
	wrenfold_triage_run_scan();
	wf_setup_log( 'Site Triage: first scan stored.' );
}

wf_setup_log( 'Done.' );
