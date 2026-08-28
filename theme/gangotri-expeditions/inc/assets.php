<?php
/**
 * Front-end assets.
 *
 * The CSS and JS filenames carry a content hash, so they are cached for a year
 * and a change produces a new filename rather than a stale cache. The build
 * writes assets/manifest.json naming the current pair; nothing here globs the
 * directory, so a leftover file from an earlier build can never be served.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * The built asset map, read once per request.
 *
 * Returns empty strings when the theme has been deployed without running the
 * build. That case is worth failing loudly in the admin rather than silently
 * serving an unstyled site, so ge_admin_notice_missing_build() says so.
 */
function ge_manifest() {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$path     = GE_DIR . '/assets/manifest.json';
	$manifest = array( 'css' => '', 'js' => '' );

	if ( is_readable( $path ) ) {
		$decoded = json_decode( (string) file_get_contents( $path ), true );
		if ( is_array( $decoded ) ) {
			$manifest = array_merge( $manifest, $decoded );
		}
	}

	return $manifest;
}

function ge_enqueue_assets() {
	$manifest = ge_manifest();

	if ( $manifest['css'] ) {
		wp_enqueue_style( 'ge-main', GE_URI . '/assets/' . $manifest['css'], array(), null );
	}

	if ( $manifest['js'] ) {
		wp_enqueue_script( 'ge-main', GE_URI . '/assets/' . $manifest['js'], array(), null, true );

		// The bundle reads window.GE_AJAX for the enquiry endpoint. Printed
		// before the module tag, so it exists by the time the module runs.
		wp_add_inline_script(
			'ge-main',
			'window.GE_AJAX=' . wp_json_encode( array(
				'url' => admin_url( 'admin-ajax.php' ),
			) ) . ';',
			'before'
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ge_enqueue_assets' );

/**
 * The bundle is an ES module; WordPress has no API for that on a plain
 * enqueue, so the tag is rewritten. `defer` is implied by type=module.
 */
function ge_script_as_module( $tag, $handle ) {
	if ( 'ge-main' !== $handle ) {
		return $tag;
	}

	return str_replace( '<script ', '<script type="module" ', $tag );
}
add_filter( 'script_loader_tag', 'ge_script_as_module', 10, 2 );

/**
 * Preload the two faces that paint above the fold.
 *
 * Without this the browser only discovers them when the stylesheet finishes
 * parsing, which is exactly when the first text wants to paint.
 */
function ge_preload_fonts() {
	foreach ( array( 'inter-var.woff2', 'poppins-700.woff2' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( GE_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'ge_preload_fonts', 1 );

/**
 * Preload the LCP image of whichever page we are on.
 *
 * The imagesrcset has to mirror the <img> exactly: preloading a bare 1920px
 * href would make a phone fetch that file AND the 960w one the srcset picks.
 */
function ge_preload_lcp() {
	if ( is_front_page() ) {
		$base = GE_URI . '/assets/img/hero/hero-1';

		printf(
			'<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="100vw" fetchpriority="high">' . "\n",
			esc_url( $base . '.webp' ),
			esc_attr( sprintf( '%1$s-960w.webp 960w, %1$s-1440w.webp 1440w, %1$s.webp 1920w', $base ) )
		);

		return;
	}

	// On a package or post the hero is the featured image, and WordPress
	// already knows every size it generated for it.
	if ( is_singular( array( 'yatra', 'post' ) ) && has_post_thumbnail() ) {
		$id  = get_post_thumbnail_id();
		$src = wp_get_attachment_image_src( $id, 'ge-hero' );

		if ( ! $src ) {
			return;
		}

		$srcset = wp_get_attachment_image_srcset( $id, 'ge-hero' );
		$sizes  = wp_get_attachment_image_sizes( $id, 'ge-hero' );

		printf(
			'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
			esc_url( $src[0] ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			$sizes ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
		);
	}
}
add_action( 'wp_head', 'ge_preload_lcp', 2 );

/**
 * Inlines the icon sprite immediately after <body>.
 *
 * Inline rather than an external file: an external sprite costs a blocking
 * request before a single icon can paint. The static build subset it per page
 * against the finished markup, which PHP cannot do without buffering the whole
 * response - so the full sprite ships. It is 18 KB raw, about 4 KB once the
 * server compresses it, which is not worth an output buffer around every page.
 */
function ge_sprite() {
	$path = GE_DIR . '/assets/icons/sprite.svg';

	if ( ! is_readable( $path ) ) {
		return;
	}

	// Theme-owned file, not user input, and it must render as markup.
	echo file_get_contents( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
add_action( 'wp_body_open', 'ge_sprite', 1 );

/** A theme deployed without its build is broken in a way worth shouting about. */
function ge_admin_notice_missing_build() {
	$manifest = ge_manifest();

	if ( $manifest['css'] && $manifest['js'] ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__(
			'Gangotri Expeditions: assets/manifest.json is missing or empty. Run "npm run build:theme" and redeploy - the site is being served without its stylesheet.',
			'gangotri-expeditions'
		)
	);
}
add_action( 'admin_notices', 'ge_admin_notice_missing_build' );
