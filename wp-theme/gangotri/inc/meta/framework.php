<?php
/**
 * A small custom field API.
 *
 * This exists because the project deliberately avoids ACF: no plugin licence,
 * no extra tables, no lock-in, and nothing to migrate if the plugin goes away.
 * The cost is this file - roughly what one repeater field is worth.
 *
 * Supported types: text, textarea, number, select, date, image, list, repeater.
 *
 * Every value is stored as ordinary post meta under its own key, so anything
 * can read it with get_post_meta() and no code here is load-bearing at runtime.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders one field control.
 *
 * @param array<string,mixed> $field Field definition.
 * @param mixed               $value Current value.
 * @param string              $name  Input name attribute.
 */
function gangotri_render_field( array $field, $value, string $name ): void {
	$type = $field['type'] ?? 'text';
	$id   = 'ge-' . md5( $name );

	echo '<div class="ge-field ge-field--' . esc_attr( $type ) . '">';

	if ( ! empty( $field['label'] ) ) {
		printf( '<label class="ge-field__label" for="%s">%s</label>', esc_attr( $id ), esc_html( $field['label'] ) );
	}

	switch ( $type ) {
		case 'textarea':
			printf(
				'<textarea id="%s" name="%s" rows="%d" class="widefat">%s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				(int) ( $field['rows'] ?? 3 ),
				esc_textarea( (string) $value )
			);
			break;

		case 'number':
			printf(
				'<input type="number" id="%s" name="%s" value="%s" class="small-text" min="%s" max="%s" step="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_attr( (string) ( $field['min'] ?? '' ) ),
				esc_attr( (string) ( $field['max'] ?? '' ) ),
				esc_attr( (string) ( $field['step'] ?? 1 ) )
			);
			break;

		case 'date':
			printf(
				'<input type="date" id="%s" name="%s" value="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
			break;

		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( (array) ( $field['options'] ?? array() ) as $key => $label ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( (string) $key ),
					selected( (string) $value, (string) $key, false ),
					esc_html( (string) $label )
				);
			}
			echo '</select>';
			break;

		case 'image':
			$attachment_id = (int) $value;
			$preview       = $attachment_id ? wp_get_attachment_image( $attachment_id, 'medium' ) : '';

			printf( '<div class="ge-image" data-ge-image><div class="ge-image__preview">%s</div>', $preview ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image returns safe markup.
			printf( '<input type="hidden" name="%s" value="%s" data-ge-image-input>', esc_attr( $name ), esc_attr( (string) $attachment_id ) );
			printf(
				'<button type="button" class="button" data-ge-image-pick>%s</button> <button type="button" class="button-link ge-image__clear" data-ge-image-clear>%s</button></div>',
				esc_html__( 'Choose image', 'gangotri' ),
				esc_html__( 'Remove', 'gangotri' )
			);
			break;

		case 'list':
			// A plain repeating text row - inclusions, highlights and the like.
			$items = is_array( $value ) ? $value : array();
			$items = $items ? $items : array( '' );

			echo '<div class="ge-list" data-ge-repeater>';
			echo '<div class="ge-list__rows" data-ge-rows>';
			foreach ( $items as $item ) {
				gangotri_render_list_row( $name, (string) $item );
			}
			echo '</div>';
			printf(
				'<template data-ge-template>%s</template>',
				esc_html( gangotri_capture( static fn() => gangotri_render_list_row( $name, '' ) ) )
			);
			printf( '<button type="button" class="button" data-ge-add>%s</button>', esc_html__( 'Add row', 'gangotri' ) );
			echo '</div>';
			break;

		case 'repeater':
			$rows = is_array( $value ) ? $value : array();

			echo '<div class="ge-repeater" data-ge-repeater>';
			echo '<div class="ge-repeater__rows" data-ge-rows>';
			foreach ( $rows as $index => $row ) {
				gangotri_render_repeater_row( $field, $name, (int) $index, (array) $row );
			}
			echo '</div>';
			printf(
				'<template data-ge-template>%s</template>',
				esc_html( gangotri_capture( static fn() => gangotri_render_repeater_row( $field, $name, 0, array(), true ) ) )
			);
			printf(
				'<button type="button" class="button" data-ge-add>%s</button>',
				esc_html( $field['add_label'] ?? __( 'Add row', 'gangotri' ) )
			);
			echo '</div>';
			break;

		case 'text':
		default:
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="widefat" placeholder="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_attr( (string) ( $field['placeholder'] ?? '' ) )
			);
			break;
	}

	if ( ! empty( $field['help'] ) ) {
		printf( '<p class="ge-field__help description">%s</p>', esc_html( $field['help'] ) );
	}

	echo '</div>';
}

/**
 * Buffers a render callback into a string.
 *
 * Used for the <template> blank row: the row markup is written once, and the
 * template is the same function called with empty values - so the blank row
 * can never drift from the real one.
 *
 * @param callable $render Callback that echoes markup.
 */
function gangotri_capture( callable $render ): string {
	ob_start();
	$render();
	return (string) ob_get_clean();
}

/**
 * One row of a `list` field.
 */
function gangotri_render_list_row( string $name, string $value ): void {
	echo '<div class="ge-row" data-ge-row>';
	printf(
		'<input type="text" name="%s[]" value="%s" class="widefat">',
		esc_attr( $name ),
		esc_attr( $value )
	);
	printf(
		'<button type="button" class="button-link ge-row__remove" data-ge-remove aria-label="%s">&times;</button>',
		esc_attr__( 'Remove row', 'gangotri' )
	);
	echo '</div>';
}

/**
 * One row of a `repeater` field.
 *
 * @param array<string,mixed> $field    Field definition, carrying `sub_fields`.
 * @param string              $name     Base input name.
 * @param int                 $index    Row index.
 * @param array<string,mixed> $row      Stored row values.
 * @param bool                $template Whether this is the blank template row.
 */
function gangotri_render_repeater_row( array $field, string $name, int $index, array $row, bool $template = false ): void {
	// The template row uses __i__ so the JS can swap in a real index on add.
	$key = $template ? '__i__' : (string) $index;

	echo '<div class="ge-row ge-row--block" data-ge-row>';
	echo '<span class="ge-row__handle dashicons dashicons-menu" data-ge-handle></span>';
	echo '<div class="ge-row__body">';

	foreach ( (array) ( $field['sub_fields'] ?? array() ) as $sub_key => $sub ) {
		gangotri_render_field(
			$sub,
			$row[ $sub_key ] ?? '',
			sprintf( '%s[%s][%s]', $name, $key, $sub_key )
		);
	}

	echo '</div>';
	printf(
		'<button type="button" class="button-link ge-row__remove" data-ge-remove aria-label="%s">&times;</button>',
		esc_attr__( 'Remove row', 'gangotri' )
	);
	echo '</div>';
}

/**
 * Sanitises a submitted value according to its field definition.
 *
 * Everything written to the database goes through here. Nothing is trusted,
 * including values from an editor who is logged in.
 *
 * @param array<string,mixed> $field Field definition.
 * @param mixed               $raw   Raw submitted value.
 * @return mixed Clean value ready for update_post_meta().
 */
function gangotri_sanitise_field( array $field, $raw ) {
	switch ( $field['type'] ?? 'text' ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );

		case 'number':
			return '' === $raw ? '' : (int) $raw;

		case 'image':
			return (int) $raw;

		case 'date':
			$date = sanitize_text_field( (string) $raw );
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';

		case 'select':
			$value   = sanitize_text_field( (string) $raw );
			$allowed = array_map( 'strval', array_keys( (array) ( $field['options'] ?? array() ) ) );
			return in_array( $value, $allowed, true ) ? $value : '';

		case 'list':
			$items = array_map( 'sanitize_text_field', (array) $raw );
			// Drop blanks: an empty row is the editor not filling one in, not
			// an intentional empty bullet.
			return array_values( array_filter( $items, static fn( $i ) => '' !== trim( $i ) ) );

		case 'repeater':
			$rows = array();

			foreach ( (array) $raw as $row_key => $row ) {
				if ( '__i__' === $row_key ) {
					continue; // the blank <template> row, never real data
				}

				$clean = array();
				foreach ( (array) ( $field['sub_fields'] ?? array() ) as $sub_key => $sub ) {
					$clean[ $sub_key ] = gangotri_sanitise_field( $sub, $row[ $sub_key ] ?? '' );
				}

				// Skip rows where the editor added a row and typed nothing.
				if ( array_filter( $clean, static fn( $v ) => '' !== $v && array() !== $v ) ) {
					$rows[] = $clean;
				}
			}
			return $rows;

		case 'text':
		default:
			return sanitize_text_field( (string) $raw );
	}
}

/**
 * Registers a meta box from a field definition array.
 *
 * @param string                           $id      Meta box id.
 * @param string                           $title   Meta box title.
 * @param string                           $screen  Post type.
 * @param array<string,array<string,mixed>> $fields Field definitions keyed by meta key.
 * @param string                           $context Meta box context.
 */
function gangotri_register_meta_box( string $id, string $title, string $screen, array $fields, string $context = 'normal' ): void {
	add_action(
		'add_meta_boxes',
		static function () use ( $id, $title, $screen, $fields, $context ): void {
			add_meta_box(
				$id,
				$title,
				static function ( WP_Post $post ) use ( $id, $fields ): void {
					wp_nonce_field( $id, $id . '_nonce' );

					foreach ( $fields as $key => $field ) {
						// Always single. A list or repeater is stored as one
						// serialised array under one key, so asking for all
						// values returns that array wrapped in another one -
						// which renders as the string "Array" in the first row
						// and loses every row after it.
						gangotri_render_field( $field, get_post_meta( $post->ID, $key, true ), $key );
					}
				},
				$screen,
				$context,
				'default'
			);
		}
	);

	add_action(
		'save_post_' . $screen,
		static function ( int $post_id ) use ( $id, $fields ): void {
			// Autosave fires with an incomplete $_POST; writing then would wipe
			// every field the editor had not touched.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$nonce = isset( $_POST[ $id . '_nonce' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id . '_nonce' ] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, $id ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			foreach ( $fields as $key => $field ) {
				if ( ! isset( $_POST[ $key ] ) ) {
					delete_post_meta( $post_id, $key );
					continue;
				}

				// Raw on purpose - gangotri_sanitise_field() is the gate, and it
				// needs the unslashed structure to walk nested rows.
				$raw   = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$clean = gangotri_sanitise_field( $field, $raw );

				if ( '' === $clean || array() === $clean ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, $clean );
				}
			}
		}
	);
}

/**
 * Admin assets for the repeater and image controls.
 *
 * Not part of the Tailwind build: this is admin-only, tiny, and has no reason
 * to be cache-busted with the front-end bundle.
 */
add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'toplevel_page_gangotri-options' ), true ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style( 'gangotri-meta', GANGOTRI_URI . '/admin/meta.css', array(), GANGOTRI_VERSION );
		wp_enqueue_script( 'gangotri-meta', GANGOTRI_URI . '/admin/meta.js', array(), GANGOTRI_VERSION, true );
	}
);
