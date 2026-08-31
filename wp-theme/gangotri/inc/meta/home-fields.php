<?php
/**
 * Meta boxes for the Home page template.
 *
 * Only the parts of the home page that are genuinely content live here - the
 * hero slides and the FAQ. The trust bar and section headings stay in the
 * template: they are brand statements rather than content, and exposing them
 * to the editor only creates a way to break the layout.
 *
 * The boxes are registered for `page` and hidden on every page except the one
 * using page-home.php, so the rest of the pages stay clean.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

gangotri_register_meta_box(
	'gangotri_home_slides',
	__( 'Hero slider', 'gangotri' ),
	'page',
	array(
		'ge_home_slides' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a slide', 'gangotri' ),
			'sub_fields' => array(
				'image'     => array(
					'type'  => 'image',
					'label' => __( 'Background image', 'gangotri' ),
					'help'  => __( 'Landscape, at least 1920px wide. The first slide is the largest thing on the page - keep it under about 250 KB.', 'gangotri' ),
				),
				'eyebrow'   => array(
					'type'        => 'text',
					'label'       => __( 'Small label above the heading', 'gangotri' ),
					'placeholder' => 'Char Dham 2026',
				),
				'title'     => array(
					'type'  => 'textarea',
					'label' => __( 'Heading', 'gangotri' ),
					'rows'  => 2,
					'help'  => __( 'Line breaks are kept. The first slide becomes the page H1.', 'gangotri' ),
				),
				'text'      => array(
					'type'  => 'textarea',
					'label' => __( 'Supporting line', 'gangotri' ),
					'rows'  => 2,
				),
				'link'      => array(
					'type'        => 'text',
					'label'       => __( 'Button link', 'gangotri' ),
					'placeholder' => '/yatras/',
				),
				'link_text' => array(
					'type'        => 'text',
					'label'       => __( 'Button text', 'gangotri' ),
					'placeholder' => 'Explore Packages',
				),
			),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_home_faqs',
	__( 'Home page FAQ', 'gangotri' ),
	'page',
	array(
		'ge_home_faqs' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a question', 'gangotri' ),
			'sub_fields' => array(
				'q' => array(
					'type'        => 'text',
					'label'       => __( 'Question', 'gangotri' ),
					'placeholder' => 'When does the Char Dham Yatra open?',
				),
				'a' => array(
					'type'  => 'textarea',
					'label' => __( 'Answer', 'gangotri' ),
					'rows'  => 3,
				),
			),
		),
	)
);

/**
 * Hides both boxes on pages that are not using the Home template.
 *
 * Registering per-template is not something add_meta_box supports, so this is
 * the standard way round it.
 */
add_action(
	'admin_head',
	static function (): void {
		global $post;

		if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
			return;
		}

		if ( 'page-home.php' === get_page_template_slug( $post->ID ) ) {
			return;
		}

		echo '<style>#gangotri_home_slides, #gangotri_home_faqs { display: none; }</style>';
	}
);
