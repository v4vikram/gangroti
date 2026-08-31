<?php
/**
 * Imports src/data/yatras.json into the `yatra` post type.
 *
 *   php scripts/import-yatras.php --wp="C:/xampp/htdocs/websites/gangroti"
 *   php scripts/import-yatras.php --wp="..." --with-images
 *   php scripts/import-yatras.php --wp="..." --dry-run
 *
 * The JSON stays the source of truth while the catalogue is being built: edit
 * it, re-run this, and WordPress matches. Matching is by slug, so re-running
 * updates the existing package rather than creating a second copy - which
 * means this is safe to run as often as you like.
 *
 * Edits made in wp-admin are overwritten on the next run. Once the client
 * starts editing packages themselves, stop running this and let WordPress be
 * the source of truth.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( 'cli' !== PHP_SAPI ) {
	exit( "Run this from the command line.\n" );
}

/* ------------------------------------------------------------------ args -- */

$options = getopt( '', array( 'wp:', 'json::', 'with-images', 'dry-run' ) );

$wp_path = $options['wp'] ?? '';
$json    = $options['json'] ?? __DIR__ . '/../src/data/yatras.json';
$images  = isset( $options['with-images'] );
$dry_run = isset( $options['dry-run'] );

if ( ! $wp_path ) {
	exit( "Missing --wp=\"path/to/wordpress\"\n" );
}

$loader = rtrim( str_replace( '\\', '/', $wp_path ), '/' ) . '/wp-load.php';

if ( ! file_exists( $loader ) ) {
	exit( "No wp-load.php at {$loader}\n" );
}

if ( ! file_exists( $json ) ) {
	exit( "No JSON at {$json}\n" );
}

/* -------------------------------------------------------------- bootstrap -- */

// Tells WordPress not to try and serve a request while we borrow its API.
define( 'WP_USE_THEMES', false );
require_once $loader;

if ( ! post_type_exists( 'yatra' ) ) {
	exit( "The `yatra` post type is not registered - activate the Gangotri theme first.\n" );
}

if ( $images ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

$packages = json_decode( (string) file_get_contents( $json ), true );

if ( ! is_array( $packages ) ) {
	exit( "Could not parse {$json}\n" );
}

/* ----------------------------------------------------------------- import -- */

/**
 * Meta keys written from each JSON entry, mapped JSON key => meta key.
 *
 * Anything not listed here is ignored, so adding a stray key to the JSON
 * cannot quietly create orphan meta.
 */
const META_MAP = array(
	'days'       => 'ge_days',
	'nights'     => 'ge_nights',
	'price'      => 'ge_price',
	'difficulty' => 'ge_difficulty',
	'altitude'   => 'ge_altitude',
	'season'     => 'ge_season',
	'batch'      => 'ge_batch',
	'pickup'     => 'ge_pickup',
	'groupSize'  => 'ge_group_size',
	'overview'   => 'ge_overview',
	'highlights' => 'ge_highlights',
	'itinerary'  => 'ge_itinerary',
	'inclusions' => 'ge_inclusions',
	'exclusions' => 'ge_exclusions',
	'faqs'       => 'ge_faqs',
);

/**
 * Attaches an image from src/img/yatras as the featured image.
 *
 * Skips the work entirely if an attachment with the same source filename is
 * already in the library, so re-running does not fill the uploads folder with
 * copies of the same photograph.
 */
function gangotri_import_image( int $post_id, string $slug, string $image ): string {
	$file = __DIR__ . '/../src/img/yatras/' . $image . '.jpg';

	if ( ! file_exists( $file ) ) {
		return 'no source image';
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_gangotri_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $image,             // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	if ( $existing ) {
		set_post_thumbnail( $post_id, (int) $existing[0] );
		return 'image reused';
	}

	// media_handle_sideload() moves the file, so hand it a copy - otherwise the
	// import deletes the source out of the repository.
	$temp = wp_tempnam( $image );
	copy( $file, $temp );

	$attachment_id = media_handle_sideload(
		array( 'name' => $image . '.jpg', 'tmp_name' => $temp ),
		$post_id
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $temp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 'image failed: ' . $attachment_id->get_error_message();
	}

	update_post_meta( $attachment_id, '_gangotri_source', $image );
	set_post_thumbnail( $post_id, $attachment_id );

	return 'image added';
}

$created = 0;
$updated = 0;

foreach ( $packages as $index => $package ) {
	$slug = sanitize_title( (string) ( $package['slug'] ?? '' ) );

	if ( ! $slug ) {
		echo "  ! entry {$index} has no slug, skipped\n";
		continue;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'yatra',
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	$post_id = $existing ? (int) $existing[0] : 0;
	$action  = $post_id ? 'update' : 'create';

	if ( $dry_run ) {
		printf( "  %-8s %s\n", $action, $slug );
		continue;
	}

	$post_data = array(
		'post_type'    => 'yatra',
		'post_status'  => 'publish',
		'post_title'   => (string) ( $package['title'] ?? $slug ),
		'post_name'    => $slug,
		'post_excerpt' => (string) ( $package['summary'] ?? '' ),
		// Ordered as the JSON is, so the archive can be reordered by editing
		// the file rather than dragging rows in wp-admin.
		'menu_order'   => (int) $index,
	);

	if ( $post_id ) {
		$post_data['ID'] = $post_id;
		$result          = wp_update_post( $post_data, true );
	} else {
		$result = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $result ) ) {
		echo "  ! {$slug}: " . $result->get_error_message() . "\n";
		continue;
	}

	$post_id = (int) $result;

	foreach ( META_MAP as $json_key => $meta_key ) {
		if ( ! array_key_exists( $json_key, $package ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}
		update_post_meta( $post_id, $meta_key, $package[ $json_key ] );
	}

	// `false` replaces the terms rather than appending, so removing a value
	// from the JSON actually removes it here too.
	if ( ! empty( $package['destination'] ) ) {
		wp_set_object_terms( $post_id, (string) $package['destination'], 'destination', false );
	}

	if ( ! empty( $package['type'] ) ) {
		wp_set_object_terms( $post_id, (string) $package['type'], 'trip_type', false );
	}

	$note = '';
	if ( $images && ! empty( $package['image'] ) ) {
		$note = ' - ' . gangotri_import_image( $post_id, $slug, (string) $package['image'] );
	}

	printf( "  %-8s %-26s #%d%s\n", $action, $slug, $post_id, $note );
	'create' === $action ? $created++ : $updated++;
}

if ( $dry_run ) {
	echo "\nDry run - nothing was written.\n";
} else {
	printf( "\n%d created, %d updated.%s\n", $created, $updated, $images ? '' : ' Images skipped - pass --with-images to attach them.' );
}
