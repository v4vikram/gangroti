<?php
/**
 * Enqueues the built CSS and JS.
 *
 * Filenames carry a content hash, so the real names live in
 * assets/manifest.json, written by `npm run build:theme`. Hashing means the
 * files can be cached for a year and a deploy still takes effect immediately -
 * a changed file is a different filename, never a stale one.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads the build manifest.
 *
 * @return array{css?:string, js?:string, fonts?:array<int,string>}
 */
function gangotri_manifest(): array {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$path = GANGOTRI_DIR . '/assets/manifest.json';

	if ( ! file_exists( $path ) ) {
		$manifest = array();
		return $manifest;
	}

	$decoded  = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$manifest = is_array( $decoded ) ? $decoded : array();

	return $manifest;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$manifest = gangotri_manifest();

		if ( empty( $manifest['css'] ) || empty( $manifest['js'] ) ) {
			// Nothing built yet. Say so where a developer will see it, rather
			// than serving an unstyled site with no explanation.
			if ( current_user_can( 'manage_options' ) ) {
				wp_add_inline_style( 'wp-admin', '' );
				error_log( 'Gangotri theme: assets/manifest.json missing - run `npm run build:theme`.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			return;
		}

		wp_enqueue_style(
			'gangotri',
			GANGOTRI_URI . '/assets/' . $manifest['css'],
			array(),
			null // Hashed filename already busts the cache; a ?ver= would be noise.
		);

		wp_enqueue_script(
			'gangotri',
			GANGOTRI_URI . '/assets/' . $manifest['js'],
			array(),
			null,
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);

		// The form posts here. Localising it keeps the endpoint out of the
		// bundle, so the same JS works on any install without a rebuild.
		wp_localize_script(
			'gangotri',
			'gangotriData',
			array(
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ge_enquiry' ),
			)
		);
	}
);

/**
 * The bundle is an ES module: it uses `import`, so a plain <script> would throw
 * on the first line.
 */
add_filter(
	'script_loader_tag',
	static function ( string $tag, string $handle ): string {
		if ( 'gangotri' !== $handle ) {
			return $tag;
		}
		return str_replace( '<script ', '<script type="module" ', $tag );
	},
	10,
	2
);

/**
 * Preloads the two font files the first paint needs, and the fonts stylesheet
 * itself. Without this the browser only discovers them after the CSS parses,
 * which shows as a flash of fallback text on a cold load.
 */
add_action(
	'wp_head',
	static function (): void {
		foreach ( gangotri_manifest()['fonts'] ?? array() as $font ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( GANGOTRI_URI . '/assets/fonts/' . $font )
			);
		}
	},
	1
);
