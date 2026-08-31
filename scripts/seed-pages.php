<?php
/**
 * Seeds the page templates with their starting content.
 *
 * The templates read repeaters - services, rates, FAQs, hero slides - and a
 * freshly created page has none of them, so every one of those sections is
 * simply absent until somebody fills it in. This puts the copy that already
 * exists in the static build into the database once, so the client edits real
 * content rather than starting from an empty screen.
 *
 * Safe to re-run: a field that already has a value is left alone unless
 * --force is passed.
 *
 * Usage:
 *   php scripts/seed-pages.php --wp="C:/xampp/htdocs/websites/gangroti" [--dry-run] [--force]
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$options = getopt( '', array( 'wp:', 'dry-run', 'force', 'json:' ) );

if ( empty( $options['wp'] ) ) {
	fwrite( STDERR, "Pass the WordPress root: --wp=\"C:/xampp/htdocs/websites/gangroti\"\n" );
	exit( 1 );
}

$dry   = isset( $options['dry-run'] );
$force = isset( $options['force'] );
$json  = isset( $options['json'] ) ? (string) $options['json'] : __DIR__ . '/../src/data/page-content.json';

$wp_load = rtrim( (string) $options['wp'], '/\\' ) . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "No wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

if ( ! file_exists( $json ) ) {
	fwrite( STDERR, "No content file at {$json}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_load;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$pages = json_decode( (string) file_get_contents( $json ), true );

if ( ! is_array( $pages ) ) {
	fwrite( STDERR, "Could not parse {$json}\n" );
	exit( 1 );
}

/**
 * Sideloads an image from the static build and returns its attachment ID.
 *
 * Keyed on the relative path in _gangotri_source so re-running reuses the
 * attachment instead of filling the media library with duplicates.
 *
 * @param string $relative Path under src/, e.g. img/hero/hero-1.webp.
 */
function gangotri_seed_image( string $relative, bool $dry ): int {
	$file = __DIR__ . '/../src/' . ltrim( $relative, '/' );

	if ( ! file_exists( $file ) ) {
		// The build emits .webp; the repository may only hold the .jpg source.
		$fallback = preg_replace( '/\.webp$/', '.jpg', $file );

		if ( ! $fallback || ! file_exists( $fallback ) ) {
			return 0;
		}

		$file = $fallback;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_gangotri_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $relative,          // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	if ( $dry ) {
		return -1;
	}

	// media_handle_sideload() moves the file, so hand it a copy - otherwise the
	// seed deletes the source out of the repository.
	$temp = wp_tempnam( basename( $file ) );
	copy( $file, $temp );

	$attachment_id = media_handle_sideload(
		array( 'name' => basename( $file ), 'tmp_name' => $temp ),
		0
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $temp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	update_post_meta( $attachment_id, '_gangotri_source', $relative );

	return (int) $attachment_id;
}

/**
 * Replaces image paths in a repeater with attachment IDs.
 *
 * @param array<int,array<string,mixed>> $rows Repeater rows.
 * @return array<int,array<string,mixed>>
 */
function gangotri_seed_resolve_images( array $rows, bool $dry, array &$log ): array {
	foreach ( $rows as $i => $row ) {
		if ( empty( $row['image'] ) || ! is_string( $row['image'] ) ) {
			continue;
		}

		$id = gangotri_seed_image( $row['image'], $dry );

		if ( 0 === $id ) {
			$log[] = 'missing image ' . $row['image'];
		}

		$rows[ $i ]['image'] = max( $id, 0 );
	}

	return $rows;
}

$prefix = $dry ? 'would ' : '';

echo "\nPages\n";

foreach ( $pages as $slug => $spec ) {
	$page = get_page_by_path( (string) $slug );

	if ( ! $page instanceof WP_Post ) {
		printf( "  %-14s not found - run setup-site.php first\n", $slug );
		continue;
	}

	$changes = array();
	$log     = array();

	// Excerpt and content: only written when the page is still empty, so an
	// edit the client has made is never overwritten by a re-run.
	if ( ! empty( $spec['excerpt'] ) && ( $force || '' === trim( $page->post_excerpt ) ) ) {
		$changes['post_excerpt'] = (string) $spec['excerpt'];
	}

	if ( ! empty( $spec['content'] ) && ( $force || '' === trim( $page->post_content ) ) ) {
		$changes['post_content'] = (string) $spec['content'];
	}

	if ( $changes && ! $dry ) {
		wp_update_post( array_merge( array( 'ID' => $page->ID ), $changes ) );
	}

	$written = array();

	foreach ( (array) ( $spec['meta'] ?? array() ) as $key => $value ) {
		$current = get_post_meta( $page->ID, (string) $key, true );

		if ( ! $force && ! empty( $current ) ) {
			continue;
		}

		if ( is_array( $value ) ) {
			$value = gangotri_seed_resolve_images( $value, $dry, $log );
		}

		if ( ! $dry ) {
			update_post_meta( $page->ID, (string) $key, $value );
		}

		$written[] = sprintf( '%s (%d)', str_replace( 'ge_page_', '', str_replace( 'ge_home_', '', (string) $key ) ), is_array( $value ) ? count( $value ) : 1 );
	}

	$parts = array();

	if ( isset( $changes['post_excerpt'] ) ) {
		$parts[] = 'excerpt';
	}

	if ( isset( $changes['post_content'] ) ) {
		$parts[] = 'content';
	}

	$parts = array_merge( $parts, $written );

	printf(
		"  %-14s %s\n",
		$slug,
		$parts ? $prefix . 'set ' . implode( ', ', $parts ) : 'already filled in'
	);

	foreach ( $log as $line ) {
		printf( "      %s\n", $line );
	}
}

echo $dry ? "\nDry run - nothing was written.\n\n" : "\nDone.\n\n";
