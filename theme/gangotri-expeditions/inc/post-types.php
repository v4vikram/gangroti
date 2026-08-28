<?php
/**
 * The `yatra` post type and its taxonomies.
 *
 * Type and destination are taxonomies rather than plain meta because the
 * listing filters on them: a taxonomy query is indexed, a meta query is not,
 * and the client gets a managed vocabulary instead of re-typing "Kedarnath"
 * three different ways.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/** The archive slug, matching the static build's /yatras.html. */
const GE_YATRA_ARCHIVE = 'yatras';

function ge_register_yatra() {
	register_post_type( 'yatra', array(
		'labels'              => array(
			'name'               => __( 'Packages', 'gangotri-expeditions' ),
			'singular_name'      => __( 'Package', 'gangotri-expeditions' ),
			'menu_name'          => __( 'Packages', 'gangotri-expeditions' ),
			'add_new'            => __( 'Add package', 'gangotri-expeditions' ),
			'add_new_item'       => __( 'Add package', 'gangotri-expeditions' ),
			'edit_item'          => __( 'Edit package', 'gangotri-expeditions' ),
			'new_item'           => __( 'New package', 'gangotri-expeditions' ),
			'view_item'          => __( 'View package', 'gangotri-expeditions' ),
			'search_items'       => __( 'Search packages', 'gangotri-expeditions' ),
			'not_found'          => __( 'No packages yet', 'gangotri-expeditions' ),
			'not_found_in_trash' => __( 'No packages in the trash', 'gangotri-expeditions' ),
			'all_items'          => __( 'All packages', 'gangotri-expeditions' ),
		),
		'public'              => true,
		'has_archive'         => GE_YATRA_ARCHIVE,
		'rewrite'             => array( 'slug' => GE_YATRA_ARCHIVE, 'with_front' => false ),
		'menu_icon'           => 'dashicons-palmtree',
		'menu_position'       => 5,
		'show_in_rest'        => true,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
		'taxonomies'          => array( 'yatra_destination', 'yatra_type' ),
		'exclude_from_search' => false,
	) );

	register_taxonomy( 'yatra_destination', 'yatra', array(
		'labels'            => array(
			'name'          => __( 'Destinations', 'gangotri-expeditions' ),
			'singular_name' => __( 'Destination', 'gangotri-expeditions' ),
			'add_new_item'  => __( 'Add destination', 'gangotri-expeditions' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'destination', 'with_front' => false ),
	) );

	register_taxonomy( 'yatra_type', 'yatra', array(
		'labels'            => array(
			'name'          => __( 'Package types', 'gangotri-expeditions' ),
			'singular_name' => __( 'Type', 'gangotri-expeditions' ),
			'add_new_item'  => __( 'Add type', 'gangotri-expeditions' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'type', 'with_front' => false ),
	) );
}
add_action( 'init', 'ge_register_yatra' );

/**
 * Rewrite rules are stored, not computed per request, so registering a post
 * type is not enough on its own - they have to be rebuilt once after the
 * theme is switched on. Doing it here rather than on every load: flushing is
 * expensive and calling it from `init` is a well-worn way to make a site slow.
 */
function ge_flush_rewrites() {
	ge_register_yatra();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'ge_flush_rewrites' );

/**
 * Gallery categories, on attachments.
 *
 * The gallery page's tabs are built from these terms, so adding a photo is
 * upload it, tick a category, done - no template edit, and no plugin. Putting
 * a taxonomy on attachments is unusual but it is the only place the data
 * belongs: the category describes the photo, not a post that wraps it.
 */
function ge_register_gallery_category() {
	register_taxonomy( 'gallery_category', 'attachment', array(
		'labels'            => array(
			'name'          => __( 'Gallery categories', 'gangotri-expeditions' ),
			'singular_name' => __( 'Gallery category', 'gangotri-expeditions' ),
			'add_new_item'  => __( 'Add gallery category', 'gangotri-expeditions' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'photos', 'with_front' => false ),
	) );
}
add_action( 'init', 'ge_register_gallery_category' );

/**
 * Enquiries are stored as posts so nothing is lost if an email bounces or the
 * host's mail is misconfigured. Private, not public, and never in search.
 */
function ge_register_enquiry() {
	register_post_type( 'ge_enquiry', array(
		'labels'              => array(
			'name'          => __( 'Enquiries', 'gangotri-expeditions' ),
			'singular_name' => __( 'Enquiry', 'gangotri-expeditions' ),
			'all_items'     => __( 'Enquiries', 'gangotri-expeditions' ),
			'edit_item'     => __( 'Enquiry', 'gangotri-expeditions' ),
			'search_items'  => __( 'Search enquiries', 'gangotri-expeditions' ),
			'not_found'     => __( 'No enquiries yet', 'gangotri-expeditions' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 6,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'supports'            => array( 'title' ),
		'capabilities'        => array(
			// Nothing should create one by hand; they arrive from the form.
			'create_posts' => 'do_not_allow',
		),
		'map_meta_cap'        => true,
	) );
}
add_action( 'init', 'ge_register_enquiry' );
