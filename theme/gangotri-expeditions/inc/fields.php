<?php
/**
 * The package field schema - one definition, three consumers.
 *
 * inc/meta-boxes.php renders and saves it, inc/import.php reads the static
 * JSON through it, and the templates read values back with ge_field(). Adding
 * a field means editing this array and nothing else.
 *
 * Four of the static build's fields are deliberately absent, because
 * WordPress already has a home for them and a second copy would only drift:
 *
 *   title    -> post title        summary  -> excerpt
 *   overview -> post content      image    -> featured image
 *
 * type and destination are taxonomies (see inc/post-types.php).
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/** Every meta key this theme writes is prefixed, so nothing collides. */
const GE_META_PREFIX = '_ge_';

/**
 * Field types:
 *   text | number | checkbox   a single value
 *   list                       repeating single lines      -> array<string>
 *   group                      repeating rows of subfields -> array<array>
 */
function ge_fields() {
	return array(
		'days'       => array(
			'label' => __( 'Days', 'gangotri-expeditions' ),
			'type'  => 'number',
			'min'   => 1,
			'max'   => 60,
			'box'   => 'facts',
		),
		'nights'     => array(
			'label' => __( 'Nights', 'gangotri-expeditions' ),
			'type'  => 'number',
			'min'   => 0,
			'max'   => 60,
			'box'   => 'facts',
		),
		'price'      => array(
			'label' => __( 'Price per person (INR)', 'gangotri-expeditions' ),
			'type'  => 'number',
			'min'   => 0,
			'max'   => 10000000,
			'box'   => 'facts',
			'help'  => __( 'Digits only - the rupee sign and commas are added by the template.', 'gangotri-expeditions' ),
		),
		'difficulty' => array(
			'label' => __( 'Difficulty', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
			'help'  => __( 'Easy, Easy to Moderate, Moderate, Challenging.', 'gangotri-expeditions' ),
		),
		'altitude'   => array(
			'label' => __( 'Maximum altitude', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
			'help'  => __( 'As displayed, e.g. 3,289 m.', 'gangotri-expeditions' ),
		),
		'season'     => array(
			'label' => __( 'Best season', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
		),
		'batch'      => array(
			'label' => __( 'Departures', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
		),
		'pickup'     => array(
			'label' => __( 'Starts from', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
		),
		'group_size' => array(
			'label' => __( 'Group size', 'gangotri-expeditions' ),
			'type'  => 'text',
			'box'   => 'facts',
		),
		'featured'   => array(
			'label' => __( 'Show this package first', 'gangotri-expeditions' ),
			'type'  => 'checkbox',
			'box'   => 'facts',
		),

		'highlights' => array(
			'label'     => __( 'Highlights', 'gangotri-expeditions' ),
			'type'      => 'list',
			'box'       => 'detail',
			'row_label' => __( 'Highlight', 'gangotri-expeditions' ),
		),
		'inclusions' => array(
			'label'     => __( 'What is included', 'gangotri-expeditions' ),
			'type'      => 'list',
			'box'       => 'detail',
			'row_label' => __( 'Inclusion', 'gangotri-expeditions' ),
		),
		'exclusions' => array(
			'label'     => __( 'What is not included', 'gangotri-expeditions' ),
			'type'      => 'list',
			'box'       => 'detail',
			'row_label' => __( 'Exclusion', 'gangotri-expeditions' ),
		),
		'itinerary'  => array(
			'label'     => __( 'Day by day itinerary', 'gangotri-expeditions' ),
			'type'      => 'group',
			'box'       => 'itinerary',
			'row_label' => __( 'Day', 'gangotri-expeditions' ),
			'subfields' => array(
				'title' => array( 'label' => __( 'Title', 'gangotri-expeditions' ), 'type' => 'text' ),
				'text'  => array( 'label' => __( 'What happens', 'gangotri-expeditions' ), 'type' => 'textarea' ),
			),
		),
		'faqs'       => array(
			'label'     => __( 'Questions about this package', 'gangotri-expeditions' ),
			'type'      => 'group',
			'box'       => 'faqs',
			'row_label' => __( 'Question', 'gangotri-expeditions' ),
			'subfields' => array(
				'q' => array( 'label' => __( 'Question', 'gangotri-expeditions' ), 'type' => 'text' ),
				'a' => array( 'label' => __( 'Answer', 'gangotri-expeditions' ), 'type' => 'textarea' ),
			),
		),
	);
}

/** Full meta key for a field name. */
function ge_meta_key( $name ) {
	return GE_META_PREFIX . $name;
}

/**
 * Reads one field off a package.
 *
 * Repeating fields always come back as an array, so a template can foreach
 * without checking first. Scalars come back as a string, or '' when unset.
 */
function ge_field( $name, $post_id = null ) {
	$fields = ge_fields();

	if ( ! isset( $fields[ $name ] ) ) {
		return '';
	}

	$post_id = $post_id ?: get_the_ID();
	$value   = get_post_meta( $post_id, ge_meta_key( $name ), true );
	$type    = $fields[ $name ]['type'];

	if ( 'list' === $type || 'group' === $type ) {
		return is_array( $value ) ? $value : array();
	}

	if ( 'checkbox' === $type ) {
		return (bool) $value;
	}

	return is_scalar( $value ) ? (string) $value : '';
}

/**
 * Cleans a submitted value to the shape its field promises.
 *
 * Everything the admin posts goes through here, so a template can trust what
 * comes back out without escaping decisions being spread across the theme.
 */
function ge_sanitize_field( $field, $raw ) {
	switch ( $field['type'] ) {
		case 'number':
			if ( '' === $raw || null === $raw ) {
				return '';
			}
			$number = (int) $raw;
			if ( isset( $field['min'] ) ) {
				$number = max( $field['min'], $number );
			}
			if ( isset( $field['max'] ) ) {
				$number = min( $field['max'], $number );
			}
			return (string) $number;

		case 'checkbox':
			return $raw ? '1' : '';

		case 'list':
			if ( ! is_array( $raw ) ) {
				return array();
			}
			$clean = array();
			foreach ( $raw as $line ) {
				$line = sanitize_text_field( (string) $line );
				if ( '' !== trim( $line ) ) {
					$clean[] = $line;
				}
			}
			return array_values( $clean );

		case 'group':
			if ( ! is_array( $raw ) ) {
				return array();
			}
			$clean = array();
			foreach ( $raw as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$out   = array();
				$empty = true;
				foreach ( $field['subfields'] as $key => $sub ) {
					$value = (string) ( $row[ $key ] ?? '' );
					$value = 'textarea' === $sub['type']
						? sanitize_textarea_field( $value )
						: sanitize_text_field( $value );
					$out[ $key ] = $value;
					if ( '' !== trim( $value ) ) {
						$empty = false;
					}
				}
				// A row the editor added and never filled in is not data.
				if ( ! $empty ) {
					$clean[] = $out;
				}
			}
			return array_values( $clean );

		default:
			return sanitize_text_field( (string) $raw );
	}
}
