<?php
/**
 * Admin assets for the custom field API.
 *
 * admin/meta.js drives the repeaters - add, remove, drag to reorder - and the
 * image picker. Without it the "Add row" buttons are inert markup, so this is
 * not optional polish; the fields do not work at all until it loads.
 *
 * Loaded from the theme folder rather than the build, because it is admin-only
 * and never shares code with the front end - hashing it would only mean
 * rebuilding the theme to change an admin script.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		// Only the post editor has fields on it.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, array( 'yatra', 'page' ), true ) ) {
			return;
		}

		// The image sub-field opens the media library.
		wp_enqueue_media();

		wp_enqueue_style(
			'gangotri-admin',
			GANGOTRI_URI . '/admin/meta.css',
			array(),
			GANGOTRI_VERSION
		);

		wp_enqueue_script(
			'gangotri-admin',
			GANGOTRI_URI . '/admin/meta.js',
			array(),
			GANGOTRI_VERSION,
			true
		);
	}
);
