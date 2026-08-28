<?php
/**
 * Theme bootstrap.
 *
 * Nothing is defined in this file on purpose - it only wires up inc/. Each
 * include owns one concern, so a change has one obvious home.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

define( 'GE_VERSION', '1.0.0' );
define( 'GE_DIR', get_template_directory() );
define( 'GE_URI', get_template_directory_uri() );

require_once GE_DIR . '/inc/setup.php';          // theme supports, menus, image sizes
require_once GE_DIR . '/inc/assets.php';         // built CSS/JS, fonts, icon sprite
require_once GE_DIR . '/inc/options.php';        // business details (Customizer)
require_once GE_DIR . '/inc/post-types.php';     // yatra CPT + its taxonomies
require_once GE_DIR . '/inc/fields.php';         // the package field schema
require_once GE_DIR . '/inc/meta-boxes.php';     // admin UI for that schema
require_once GE_DIR . '/inc/template-tags.php';  // helpers used by templates
require_once GE_DIR . '/inc/nav-walker.php';     // menu markup + fallbacks
require_once GE_DIR . '/inc/query.php';          // archive filtering and sorting
require_once GE_DIR . '/inc/schema.php';         // JSON-LD
require_once GE_DIR . '/inc/enquiry.php';        // enquiry form endpoint + storage
require_once GE_DIR . '/inc/import.php';         // one-off import of the static JSON
