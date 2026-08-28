<?php
/**
 * Theme supports, menus and image sizes.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * The widths the layouts actually ask for.
 *
 * Registered so WordPress puts them in the srcset it builds for every uploaded
 * image. The static build hand-generated these with sharp; here the media
 * library does it, which is why scripts/optimize-images.mjs only covers the
 * theme's own bundled assets now.
 */
function ge_setup() {
	load_theme_textdomain( 'gangotri-expeditions', GE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 256,
		'width'       => 343,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// The card is 4:3 and the detail hero 16:9; both are capped at 2x the
	// largest CSS size they are ever painted at.
	add_image_size( 'ge-card', 800, 600, true );
	add_image_size( 'ge-card-2x', 1200, 900, true );
	add_image_size( 'ge-hero', 1920, 1080, true );
	add_image_size( 'ge-hero-md', 1440, 810, true );
	add_image_size( 'ge-hero-sm', 960, 540, true );

	register_nav_menus( array(
		'primary' => __( 'Primary menu', 'gangotri-expeditions' ),
		'explore' => __( 'Footer - Explore', 'gangotri-expeditions' ),
		'legal'   => __( 'Footer - Legal', 'gangotri-expeditions' ),
	) );
}
add_action( 'after_setup_theme', 'ge_setup' );

/**
 * Offer the custom sizes in the editor's size dropdown too, so an editor
 * inserting an image by hand gets the same crops the templates use.
 */
function ge_image_size_names( $sizes ) {
	return array_merge( $sizes, array(
		'ge-card' => __( 'Package card (800x600)', 'gangotri-expeditions' ),
		'ge-hero' => __( 'Hero (1920x1080)', 'gangotri-expeditions' ),
	) );
}
add_filter( 'image_size_names_choose', 'ge_image_size_names' );

/**
 * The content width WordPress hands to oEmbeds, matching --container-page
 * minus its padding.
 */
function ge_content_width() {
	$GLOBALS['content_width'] = 1216;
}
add_action( 'after_setup_theme', 'ge_content_width', 0 );

/**
 * Trim the <head> back to what the site needs.
 *
 * The static build shipped a deliberately small head; WordPress adds a pile of
 * legacy discovery tags on top. Emoji detection is the expensive one - it is a
 * script and a stylesheet on every page for a feature nothing here uses.
 */
function ge_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'ge_clean_head' );

/**
 * Drop the block editor's front-end stylesheets.
 *
 * Every layout here is Tailwind; wp-block-library only adds bytes the pages
 * never use. Kept in the admin so the editor itself still looks right.
 */
function ge_dequeue_block_styles() {
	if ( is_admin() ) {
		return;
	}
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'ge_dequeue_block_styles', 100 );
