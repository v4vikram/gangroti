<?php
/**
 * Gangotri Expeditions theme bootstrap.
 *
 * Everything lives in inc/ as a single-responsibility file. Nothing is
 * registered here directly, so this file stays readable as the theme grows.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GANGOTRI_VERSION', '1.0.0' );
define( 'GANGOTRI_DIR', get_template_directory() );
define( 'GANGOTRI_URI', get_template_directory_uri() );

$gangotri_modules = array(
	'inc/setup.php',      // theme supports, menus, image sizes
	'inc/assets.php',     // enqueue the built CSS and JS
	'inc/admin-assets.php', // the repeater and image-picker scripts
	'inc/nav.php',        // menu walker and fallbacks
	'inc/cpt.php',        // the `yatra` post type and its taxonomies
	'inc/meta/framework.php', // the small custom field API
	'inc/meta/yatra-fields.php',
	'inc/meta/home-fields.php',
	'inc/meta/page-fields.php',
	'inc/options.php',    // Theme Options page (phone, email, social)
	'inc/enquiry.php',    // enquiry form handler and the enquiry record
	'inc/schema.php',     // JSON-LD output
	'inc/template-tags.php',
);

foreach ( $gangotri_modules as $gangotri_module ) {
	require_once GANGOTRI_DIR . '/' . $gangotri_module;
}
unset( $gangotri_module );
