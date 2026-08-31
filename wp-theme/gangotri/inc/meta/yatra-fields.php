<?php
/**
 * Fields for the `yatra` post type.
 *
 * These are the keys src/data/yatras.json carried in the static build, prefixed
 * with ge_ so they cannot collide with a plugin's meta. The template reads the
 * same names, so the markup did not have to change on conversion.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

gangotri_register_meta_box(
	'gangotri_facts',
	__( 'Package facts', 'gangotri' ),
	'yatra',
	array(
		'ge_days'       => array(
			'type'  => 'number',
			'label' => __( 'Days', 'gangotri' ),
			'min'   => 1,
			'max'   => 60,
		),
		'ge_nights'     => array(
			'type'  => 'number',
			'label' => __( 'Nights', 'gangotri' ),
			'min'   => 0,
			'max'   => 60,
		),
		'ge_price'      => array(
			'type'  => 'number',
			'label' => __( 'Price from (INR, per person)', 'gangotri' ),
			'min'   => 0,
			'step'  => 100,
			'help'  => __( 'Numbers only - no commas or rupee sign.', 'gangotri' ),
		),
		'ge_difficulty' => array(
			'type'    => 'select',
			'label'   => __( 'Difficulty', 'gangotri' ),
			'options' => array(
				'Easy'      => __( 'Easy', 'gangotri' ),
				'Moderate'  => __( 'Moderate', 'gangotri' ),
				'Difficult' => __( 'Difficult', 'gangotri' ),
			),
		),
		'ge_altitude'   => array(
			'type'        => 'text',
			'label'       => __( 'Maximum altitude', 'gangotri' ),
			'placeholder' => '3,289 m',
		),
		'ge_season'     => array(
			'type'        => 'text',
			'label'       => __( 'Best season', 'gangotri' ),
			'placeholder' => 'May - Jun, Sep - Oct',
		),
		'ge_batch'      => array(
			'type'        => 'text',
			'label'       => __( 'Departures', 'gangotri' ),
			'placeholder' => 'Every weekend',
		),
		'ge_pickup'     => array(
			'type'        => 'text',
			'label'       => __( 'Starts from', 'gangotri' ),
			'placeholder' => 'Delhi',
		),
		'ge_group_size' => array(
			'type'        => 'text',
			'label'       => __( 'Group size', 'gangotri' ),
			'placeholder' => 'Max 15',
		),
	),
	'side'
);

gangotri_register_meta_box(
	'gangotri_overview',
	__( 'Overview and highlights', 'gangotri' ),
	'yatra',
	array(
		'ge_overview'   => array(
			'type'  => 'textarea',
			'label' => __( 'Overview', 'gangotri' ),
			'rows'  => 5,
			'help'  => __( 'The opening paragraph. Answer the obvious question first - this is what AI assistants and search snippets quote.', 'gangotri' ),
		),
		'ge_highlights' => array(
			'type'  => 'list',
			'label' => __( 'Highlights', 'gangotri' ),
			'help'  => __( 'Four is about right. One short line each.', 'gangotri' ),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_itinerary',
	__( 'Day by day itinerary', 'gangotri' ),
	'yatra',
	array(
		'ge_itinerary' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a day', 'gangotri' ),
			'sub_fields' => array(
				'title' => array(
					'type'        => 'text',
					'label'       => __( 'Day title', 'gangotri' ),
					'placeholder' => 'Ransi to Madmaheshwar',
				),
				'text'  => array(
					'type'  => 'textarea',
					'label' => __( 'What happens', 'gangotri' ),
					'rows'  => 3,
				),
			),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_inclusions',
	__( 'Inclusions and exclusions', 'gangotri' ),
	'yatra',
	array(
		'ge_inclusions' => array(
			'type'  => 'list',
			'label' => __( 'What is included', 'gangotri' ),
		),
		'ge_exclusions' => array(
			'type'  => 'list',
			'label' => __( 'What is not included', 'gangotri' ),
			'help'  => __( 'Be complete here. Every cost left off this list turns into an argument at Gaurikund.', 'gangotri' ),
		),
	)
);

gangotri_register_meta_box(
	'gangotri_faqs',
	__( 'Questions about this package', 'gangotri' ),
	'yatra',
	array(
		'ge_faqs' => array(
			'type'       => 'repeater',
			'add_label'  => __( 'Add a question', 'gangotri' ),
			'sub_fields' => array(
				'q' => array(
					'type'        => 'text',
					'label'       => __( 'Question', 'gangotri' ),
					'placeholder' => 'How difficult is this trek?',
				),
				'a' => array(
					'type'  => 'textarea',
					'label' => __( 'Answer', 'gangotri' ),
					'rows'  => 3,
					'help'  => __( 'These are published as FAQ schema, so answer plainly and completely in the first two sentences.', 'gangotri' ),
				),
			),
		),
	)
);
