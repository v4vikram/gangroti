<?php
/**
 * One-off import of the static build's package data.
 *
 * src/data/yatras.json is shipped inside the theme as data/yatras.json so the
 * import needs nothing but the theme itself. Run it once from
 * Tools -> Import packages, or with WP-CLI:
 *
 *   wp gangotri import
 *
 * Re-running is safe: a package is matched on its slug and updated rather than
 * duplicated, so the JSON can be re-imported after a correction.
 *
 * This file can be deleted once the client is entering packages in the admin -
 * it exists to get the first ones in, not as an ongoing sync.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imports every package in the bundled JSON.
 *
 * @return array{imported:int,updated:int,skipped:int,messages:string[]}
 */
function ge_import_yatras() {
	$result = array( 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'messages' => array() );
	$path   = GE_DIR . '/data/yatras.json';

	if ( ! is_readable( $path ) ) {
		$result['messages'][] = __( 'data/yatras.json is missing from the theme.', 'gangotri-expeditions' );
		return $result;
	}

	$items = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( ! is_array( $items ) ) {
		$result['messages'][] = __( 'data/yatras.json is not valid JSON.', 'gangotri-expeditions' );
		return $result;
	}

	foreach ( $items as $item ) {
		if ( empty( $item['slug'] ) || empty( $item['title'] ) ) {
			$result['skipped']++;
			continue;
		}

		$slug     = sanitize_title( $item['slug'] );
		$existing = get_page_by_path( $slug, OBJECT, 'yatra' );

		$postarr = array(
			'post_type'    => 'yatra',
			'post_name'    => $slug,
			'post_title'   => sanitize_text_field( $item['title'] ),
			'post_excerpt' => sanitize_textarea_field( $item['summary'] ?? '' ),
			'post_content' => wp_kses_post( $item['overview'] ?? '' ),
			'post_status'  => 'publish',
		);

		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$post_id       = wp_update_post( $postarr, true );
			$result['updated']++;
		} else {
			$post_id = wp_insert_post( $postarr, true );
			$result['imported']++;
		}

		if ( is_wp_error( $post_id ) ) {
			$result['messages'][] = sprintf(
				/* translators: 1: package slug, 2: error message. */
				__( 'Could not save %1$s: %2$s', 'gangotri-expeditions' ),
				$slug,
				$post_id->get_error_message()
			);
			continue;
		}

		// type and destination are taxonomies now, not meta.
		foreach ( array( 'type' => 'yatra_type', 'destination' => 'yatra_destination' ) as $key => $taxonomy ) {
			if ( ! empty( $item[ $key ] ) ) {
				wp_set_object_terms( $post_id, sanitize_text_field( $item[ $key ] ), $taxonomy, false );
			}
		}

		// Everything else goes through the same sanitisers the editor uses.
		$map = array(
			'days'       => 'days',
			'nights'     => 'nights',
			'price'      => 'price',
			'difficulty' => 'difficulty',
			'altitude'   => 'altitude',
			'season'     => 'season',
			'batch'      => 'batch',
			'pickup'     => 'pickup',
			'groupSize'  => 'group_size',
			'featured'   => 'featured',
			'highlights' => 'highlights',
			'inclusions' => 'inclusions',
			'exclusions' => 'exclusions',
			'itinerary'  => 'itinerary',
			'faqs'       => 'faqs',
		);

		$fields = ge_fields();

		foreach ( $map as $json_key => $field_name ) {
			if ( ! array_key_exists( $json_key, $item ) ) {
				continue;
			}

			$clean = ge_sanitize_field( $fields[ $field_name ], $item[ $json_key ] );

			if ( '' === $clean || array() === $clean ) {
				delete_post_meta( $post_id, ge_meta_key( $field_name ) );
			} else {
				update_post_meta( $post_id, ge_meta_key( $field_name ), $clean );
			}
		}

		// The image lives in the theme, not the media library. Sideload it so
		// WordPress owns it and can build the srcset the templates rely on.
		if ( ! empty( $item['image'] ) && ! has_post_thumbnail( $post_id ) ) {
			$attached = ge_sideload_package_image( $post_id, $item['image'], $item['alt'] ?? '' );

			if ( is_wp_error( $attached ) ) {
				$result['messages'][] = sprintf(
					/* translators: 1: package slug, 2: error message. */
					__( 'Imported %1$s but could not attach its image: %2$s', 'gangotri-expeditions' ),
					$slug,
					$attached->get_error_message()
				);
			}
		}
	}

	return $result;
}

/**
 * Copies one bundled package image into the media library and sets it as the
 * featured image.
 */
function ge_sideload_package_image( $post_id, $name, $alt ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$source = GE_DIR . '/assets/img/yatras/' . sanitize_file_name( $name ) . '.jpg';

	if ( ! is_readable( $source ) ) {
		return new WP_Error( 'ge_missing_image', $source );
	}

	// media_handle_sideload moves the file it is given, so it gets a copy.
	$tmp = wp_tempnam( basename( $source ) );

	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return new WP_Error( 'ge_copy_failed', __( 'Could not copy the image to a temporary file.', 'gangotri-expeditions' ) );
	}

	$attachment_id = media_handle_sideload(
		array( 'name' => basename( $source ), 'tmp_name' => $tmp ),
		$post_id,
		null
	);

	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $tmp );
		return $attachment_id;
	}

	if ( $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}

	set_post_thumbnail( $post_id, $attachment_id );

	return $attachment_id;
}

/** Tools -> Import packages. */
function ge_import_menu() {
	add_management_page(
		__( 'Import packages', 'gangotri-expeditions' ),
		__( 'Import packages', 'gangotri-expeditions' ),
		'manage_options',
		'ge-import',
		'ge_import_screen'
	);
}
add_action( 'admin_menu', 'ge_import_menu' );

function ge_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'gangotri-expeditions' ) );
	}

	echo '<div class="wrap"><h1>' . esc_html__( 'Import packages', 'gangotri-expeditions' ) . '</h1>';

	if ( isset( $_POST['ge_import'] ) && check_admin_referer( 'ge_import' ) ) {
		$result = ge_import_yatras();

		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			esc_html( sprintf(
				/* translators: 1: created count, 2: updated count, 3: skipped count. */
				__( 'Done. %1$d created, %2$d updated, %3$d skipped.', 'gangotri-expeditions' ),
				$result['imported'],
				$result['updated'],
				$result['skipped']
			) )
		);

		foreach ( $result['messages'] as $message ) {
			printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( $message ) );
		}
	}

	echo '<p>' . esc_html__(
		'Creates a package for every entry in the theme\'s data/yatras.json. Matching is by slug, so running this twice updates rather than duplicates.',
		'gangotri-expeditions'
	) . '</p>';

	echo '<form method="post">';
	wp_nonce_field( 'ge_import' );
	submit_button( __( 'Import now', 'gangotri-expeditions' ), 'primary', 'ge_import' );
	echo '</form></div>';
}

/** The same thing for a deploy script. */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'gangotri import', static function () {
		$result = ge_import_yatras();

		foreach ( $result['messages'] as $message ) {
			WP_CLI::warning( $message );
		}

		WP_CLI::success( sprintf(
			'%d created, %d updated, %d skipped.',
			$result['imported'],
			$result['updated'],
			$result['skipped']
		) );
	} );
}
