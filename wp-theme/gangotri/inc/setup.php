<?php
/**
 * Theme supports, menus and image sizes.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'gangotri', GANGOTRI_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array( 'height' => 256, 'width' => 343, 'flex-width' => true ) );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );

		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);

		register_nav_menus(
			array(
				'primary' => __( 'Primary menu', 'gangotri' ),
				'footer'  => __( 'Footer - Explore', 'gangotri' ),
			)
		);

		// Matches the sizes the static build produced, so the same crops are
		// used and nothing has to be re-cut when images are swapped.
		add_image_size( 'gangotri-hero', 1920, 1080, true );
		add_image_size( 'gangotri-card', 800, 600, true );
		add_image_size( 'gangotri-square', 800, 800, true );
	}
);

/**
 * The theme has no widget areas and no sidebar by design - every region that
 * would normally be a widget area is either a menu, a Theme Option, or content
 * on the `yatra` post type. That keeps the markup identical to the approved
 * static build.
 */

add_filter(
	'body_class',
	static function ( array $classes ): array {
		$classes[] = 'min-h-screen';
		$classes[] = 'flex';
		$classes[] = 'flex-col';
		return $classes;
	}
);

/**
 * Trim the <head>. Each of these emits markup the site does not use, and two
 * of them (RSD, wlwmanifest) advertise endpoints that only exist for clients
 * nobody has run since 2012.
 */
add_action(
	'init',
	static function (): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		// Emoji support pulls in a script and an inline style on every page to
		// polyfill something every browser we build for renders natively.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
);

/**
 * Block Gutenberg's front-end stylesheet. The theme ships no block content -
 * pages are built from templates - so wp-block-library is dead weight.
 */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);
