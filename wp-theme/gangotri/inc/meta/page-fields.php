<?php
/**
 * Meta boxes for the About, Services and FAQ page templates.
 *
 * These blocks are structured data, not prose: a stat is a label and a number,
 * a rate is three cells of a table. Keeping them out of the editor means the
 * client can change the figures without touching markup, and means the same
 * values can be published as schema.
 *
 * Like the home page boxes, each is registered for `page` and then hidden on
 * every page not using the matching template.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The icons available in the built sprite, as a select list.
 *
 * A free-text field here would only produce empty squares when a name is
 * misremembered, so the choice is constrained to what the sprite actually has.
 *
 * @return array<string,string>
 */
function gangotri_icon_choices(): array {
	$names = array(
		'mountain',
		'mountain-snow',
		'tent',
		'footprints',
		'backpack',
		'compass',
		'route',
		'bus',
		'bed',
		'utensils',
		'ticket',
		'calendar',
		'clock',
		'users',
		'indian-rupee',
		'shield-check',
		'badge-check',
		'award',
		'circle-check-big',
		'heart',
		'star',
		'map-pin',
		'phone-call',
		'send',
		'info',
		'trishul',
	);

	$choices = array( '' => __( 'Default', 'gangotri' ) );

	foreach ( $names as $name ) {
		$choices[ $name ] = ucwords( str_replace( '-', ' ', $name ) );
	}

	return $choices;
}

/*
 * About.
 */

gangotri_register_meta_box(
	'gangotri_page_stats',
	__( 'Numbers', 'gangotri' ),
	'page',
	array(
		'ge_page_stats' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a number', 'gangotri' ),
			'sub_fields' => array(
				'value' => array(
					'type'        => 'text',
					'label'       => __( 'Figure', 'gangotri' ),
					'placeholder' => '8,500+',
				),
				'label' => array(
					'type'        => 'text',
					'label'       => __( 'What it counts', 'gangotri' ),
					'placeholder' => 'Yatris travelled',
					'help'        => __( 'Only publish figures you can stand behind - these are the first thing a sceptical visitor checks.', 'gangotri' ),
				),
			),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_page_values',
	__( 'How we work', 'gangotri' ),
	'page',
	array(
		'ge_page_values' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a point', 'gangotri' ),
			'sub_fields' => array(
				'icon'  => array(
					'type'    => 'select',
					'label'   => __( 'Icon', 'gangotri' ),
					'options' => gangotri_icon_choices(),
				),
				'title' => array(
					'type'  => 'text',
					'label' => __( 'Heading', 'gangotri' ),
				),
				'text'  => array(
					'type'  => 'textarea',
					'label' => __( 'Detail', 'gangotri' ),
					'rows'  => 3,
				),
			),
		),
	)
);

/*
 * Services.
 */

gangotri_register_meta_box(
	'gangotri_page_services',
	__( 'Services', 'gangotri' ),
	'page',
	array(
		'ge_page_services' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a service', 'gangotri' ),
			'sub_fields' => array(
				'icon'  => array(
					'type'    => 'select',
					'label'   => __( 'Icon', 'gangotri' ),
					'options' => gangotri_icon_choices(),
				),
				'title' => array(
					'type'        => 'text',
					'label'       => __( 'Service', 'gangotri' ),
					'placeholder' => 'Private Transport',
				),
				'text'  => array(
					'type'  => 'textarea',
					'label' => __( 'What it covers', 'gangotri' ),
					'rows'  => 3,
				),
			),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_page_rates',
	__( 'Indicative add-on pricing', 'gangotri' ),
	'page',
	array(
		'ge_page_rates' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a rate', 'gangotri' ),
			'sub_fields' => array(
				'service' => array(
					'type'        => 'text',
					'label'       => __( 'Service', 'gangotri' ),
					'placeholder' => 'Helicopter transfer',
				),
				'route'   => array(
					'type'        => 'text',
					'label'       => __( 'Route or detail', 'gangotri' ),
					'placeholder' => 'Phata to Kedarnath, return',
				),
				'cost'    => array(
					'type'        => 'text',
					'label'       => __( 'Indicative cost', 'gangotri' ),
					'placeholder' => '7,500 - 8,500',
					'help'        => __( 'Write it exactly as it should appear, rupee sign included.', 'gangotri' ),
				),
			),
		),
	)
);

/*
 * FAQ - used by both the Services and the FAQ templates.
 */

gangotri_register_meta_box(
	'gangotri_page_faqs',
	__( 'Questions and answers', 'gangotri' ),
	'page',
	array(
		'ge_page_faqs' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a question', 'gangotri' ),
			'sub_fields' => array(
				'section' => array(
					'type'        => 'text',
					'label'       => __( 'Group heading', 'gangotri' ),
					'placeholder' => 'Before you book',
					'help'        => __( 'Optional. Questions sharing a heading are shown together, in the order entered. Ignored on the Services page.', 'gangotri' ),
				),
				'q'       => array(
					'type'  => 'text',
					'label' => __( 'Question', 'gangotri' ),
				),
				'a'       => array(
					'type'  => 'textarea',
					'label' => __( 'Answer', 'gangotri' ),
					'rows'  => 3,
					'help'  => __( 'Published as FAQ schema. Answer completely in the first two sentences - that is the part that gets quoted.', 'gangotri' ),
				),
			),
		),
	)
);

/**
 * Hides each box on pages not using the template that reads it.
 *
 * add_meta_box has no per-template screen, so this is the usual way round it.
 */
add_action(
	'admin_head',
	static function (): void {
		global $post;

		if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
			return;
		}

		$template = (string) get_page_template_slug( $post->ID );

		$boxes = array(
			'gangotri_page_stats'    => array( 'page-about.php' ),
			'gangotri_page_values'   => array( 'page-about.php' ),
			'gangotri_page_services' => array( 'page-services.php' ),
			'gangotri_page_rates'    => array( 'page-services.php' ),
			'gangotri_page_faqs'     => array( 'page-services.php', 'page-faq.php' ),
		);

		$hide = array();

		foreach ( $boxes as $id => $templates ) {
			if ( ! in_array( $template, $templates, true ) ) {
				$hide[] = '#' . $id;
			}
		}

		if ( $hide ) {
			echo '<style>' . esc_html( implode( ', ', $hide ) ) . ' { display: none; }</style>';
		}
	}
);
