<?php
/**
 * One-time WordPress setup: pages, front page, permalinks and menus.
 *
 *   php scripts/setup-site.php --wp="C:/xampp/htdocs/websites/gangroti"
 *   php scripts/setup-site.php --wp="..." --dry-run
 *
 * Safe to re-run. Pages are matched by slug and updated rather than duplicated,
 * and existing content is left alone unless --force is passed - so a page the
 * client has edited will not be overwritten by a second run.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( 'cli' !== PHP_SAPI ) {
	exit( "Run this from the command line.\n" );
}

$options = getopt( '', array( 'wp:', 'dry-run', 'force' ) );

$wp_path = $options['wp'] ?? '';
$dry_run = isset( $options['dry-run'] );
$force   = isset( $options['force'] );

if ( ! $wp_path ) {
	exit( "Missing --wp=\"path/to/wordpress\"\n" );
}

$loader = rtrim( str_replace( '\\', '/', $wp_path ), '/' ) . '/wp-load.php';

if ( ! file_exists( $loader ) ) {
	exit( "No wp-load.php at {$loader}\n" );
}

define( 'WP_USE_THEMES', false );
require_once $loader;
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$json = __DIR__ . '/../src/data/pages.json';

if ( ! file_exists( $json ) ) {
	exit( "No pages.json - run `node scripts/extract-pages.mjs` first.\n" );
}

$pages = json_decode( (string) file_get_contents( $json ), true );

if ( ! is_array( $pages ) ) {
	exit( "Could not parse {$json}\n" );
}

$say = static function ( string $line ) use ( $dry_run ): void {
	echo ( $dry_run ? '  would ' : '  ' ) . $line . "\n";
};

/* ------------------------------------------------------------------ pages -- */

echo "Pages\n";

$ids = array();

// The home page is a real page so it can be set as the front page and appear
// in menus; page-home.php owns everything it renders.
$wanted = array_merge(
	array( array( 'slug' => 'home', 'title' => 'Home', 'template' => 'page-home.php', 'content' => '' ) ),
	$pages
);

foreach ( $wanted as $page ) {
	$existing = get_page_by_path( $page['slug'] );

	if ( $existing ) {
		$ids[ $page['slug'] ] = $existing->ID;

		// Only the template is enforced on an existing page. Content is the
		// client's to own once the page exists.
		$changes = array();

		if ( $page['template'] && get_page_template_slug( $existing->ID ) !== $page['template'] ) {
			$changes[] = 'template';
		}

		if ( $force && $page['content'] ) {
			$changes[] = 'content';
		}

		if ( ! $changes ) {
			$say( sprintf( '%-14s exists, unchanged', $page['slug'] ) );
			continue;
		}

		if ( ! $dry_run ) {
			if ( in_array( 'template', $changes, true ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
			}
			if ( in_array( 'content', $changes, true ) ) {
				wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $page['content'] ) );
			}
		}

		$say( sprintf( '%-14s updated (%s)', $page['slug'], implode( ', ', $changes ) ) );
		continue;
	}

	if ( $dry_run ) {
		$say( sprintf( '%-14s create', $page['slug'] ) );
		continue;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['content'],
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		echo '  ! ' . $page['slug'] . ': ' . $id->get_error_message() . "\n";
		continue;
	}

	if ( $page['template'] ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}

	$ids[ $page['slug'] ] = (int) $id;
	$say( sprintf( '%-14s created #%d', $page['slug'], $id ) );
}

/* --------------------------------------------------------------- settings -- */

echo "\nSettings\n";

// Date-based permalinks make no sense for a site with no blog, and they push
// the useful part of every URL three segments deep.
if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
	if ( ! $dry_run ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}
	$say( 'permalinks set to /%postname%/' );
} else {
	$say( 'permalinks already /%postname%/' );
}

if ( isset( $ids['home'] ) && (int) get_option( 'page_on_front' ) !== $ids['home'] ) {
	if ( ! $dry_run ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	$say( 'front page set to Home' );
} else {
	$say( 'front page already set' );
}

// WordPress needs a privacy page nominated for its own privacy tools.
if ( isset( $ids['privacy'] ) && (int) get_option( 'wp_page_for_privacy_policy' ) !== $ids['privacy'] ) {
	if ( ! $dry_run ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
	}
	$say( 'privacy policy page nominated' );
}

// The stock Sample Page and the draft Privacy Policy WordPress ships with.
foreach ( array( 'sample-page', 'privacy-policy' ) as $stock ) {
	$post = get_page_by_path( $stock, OBJECT, 'page' );

	if ( $post && ! in_array( $post->ID, $ids, true ) ) {
		if ( ! $dry_run ) {
			wp_trash_post( $post->ID );
		}
		$say( sprintf( 'trashed stock page "%s"', $stock ) );
	}
}

/* ------------------------------------------------------------------ menus -- */

echo "\nMenus\n";

/**
 * Builds a menu from a list of page slugs, plus the package archive.
 *
 * Rebuilt from scratch each run: a menu is derived from the pages, so keeping
 * hand edits would only let it drift out of step with them.
 *
 * @param string             $name     Menu name.
 * @param string             $location Theme location.
 * @param array<int,string>  $items    Page slugs, or 'packages' for the archive.
 * @param array<string,int>  $ids      Page slug => post ID.
 * @param bool               $dry_run  Whether to write.
 * @param callable           $say      Logger.
 */
function gangotri_build_menu( string $name, string $location, array $items, array $ids, bool $dry_run, callable $say ): void {
	$existing = wp_get_nav_menu_object( $name );

	if ( $dry_run ) {
		$say( sprintf( '%-8s %s (%d items)', $location, $existing ? 'rebuild' : 'create', count( $items ) ) );
		return;
	}

	if ( $existing ) {
		wp_delete_nav_menu( $existing->term_id );
	}

	$menu_id = wp_create_nav_menu( $name );

	if ( is_wp_error( $menu_id ) ) {
		echo '  ! ' . $menu_id->get_error_message() . "\n";
		return;
	}

	$position = 0;

	foreach ( $items as $item ) {
		$position++;

		if ( 'packages' === $item ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => 'Packages',
					'menu-item-url'       => get_post_type_archive_link( 'yatra' ),
					'menu-item-type'      => 'custom',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $position,
				)
			);
			continue;
		}

		if ( ! isset( $ids[ $item ] ) ) {
			continue;
		}

		// A page title is written for search results; a nav label has to fit
		// in a header. Where the two differ, the short one goes in the menu.
		$labels = array(
			'faq'          => 'FAQ',
			'cancellation' => 'Cancellation',
			'privacy'      => 'Privacy',
			'terms'        => 'Terms',
		);

		$args = array(
			'menu-item-object-id' => $ids[ $item ],
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position,
		);

		if ( isset( $labels[ $item ] ) ) {
			$args['menu-item-title'] = $labels[ $item ];
		}

		wp_update_nav_menu_item( $menu_id, 0, $args );
	}

	$locations              = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$say( sprintf( '%-8s built with %d items', $location, count( $items ) ) );
}

gangotri_build_menu(
	'Primary',
	'primary',
	array( 'home', 'packages', 'about', 'services', 'gallery', 'contact' ),
	$ids,
	$dry_run,
	$say
);

gangotri_build_menu(
	'Footer',
	'footer',
	array( 'packages', 'about', 'services', 'gallery', 'faq' ),
	$ids,
	$dry_run,
	$say
);

echo $dry_run ? "\nDry run - nothing was written.\n" : "\nDone.\n";
