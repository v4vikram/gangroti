<?php
/**
 * The package editing UI, built from ge_fields().
 *
 * This is the part a plugin would normally do. It stays small because the
 * schema drives it: the boxes below render whatever ge_fields() declares, and
 * the repeater is one <template> element cloned by 40 lines of JavaScript
 * rather than a framework.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/** Boxes, in the order they appear beside the editor. */
function ge_meta_boxes() {
	return array(
		'facts'     => __( 'Package facts', 'gangotri-expeditions' ),
		'detail'    => __( 'Highlights, inclusions and exclusions', 'gangotri-expeditions' ),
		'itinerary' => __( 'Itinerary', 'gangotri-expeditions' ),
		'faqs'      => __( 'FAQs', 'gangotri-expeditions' ),
	);
}

function ge_add_meta_boxes() {
	foreach ( ge_meta_boxes() as $id => $title ) {
		add_meta_box(
			'ge-' . $id,
			$title,
			'ge_render_meta_box',
			'yatra',
			'facts' === $id ? 'side' : 'normal',
			'facts' === $id ? 'default' : 'high',
			array( 'box' => $id )
		);
	}
}
add_action( 'add_meta_boxes_yatra', 'ge_add_meta_boxes' );

/**
 * The FAQ page gets the same repeater the packages use.
 *
 * Reusing the box rather than writing a second one means the FAQ editing
 * experience, the sanitising and the JSON-LD all stay identical to a package's
 * - and there is one repeater in the codebase, not two.
 */
function ge_add_page_meta_boxes( $post ) {
	if ( 'page-templates/faq.php' !== get_page_template_slug( $post ) ) {
		return;
	}

	add_meta_box(
		'ge-faqs',
		__( 'Questions and answers', 'gangotri-expeditions' ),
		'ge_render_meta_box',
		'page',
		'normal',
		'high',
		array( 'box' => 'faqs' )
	);
}
add_action( 'add_meta_boxes_page', 'ge_add_page_meta_boxes' );

/** Renders every field that declares itself part of this box. */
function ge_render_meta_box( $post, $meta_box ) {
	$box = $meta_box['args']['box'];

	// One nonce per box; each box posts independently of the others.
	wp_nonce_field( 'ge_save_' . $box, 'ge_nonce_' . $box );

	echo '<div class="ge-fields">';

	foreach ( ge_fields() as $name => $field ) {
		if ( ( $field['box'] ?? '' ) !== $box ) {
			continue;
		}
		ge_render_field( $name, $field, $post->ID );
	}

	echo '</div>';
}

function ge_render_field( $name, $field, $post_id ) {
	$key   = ge_meta_key( $name );
	$value = ge_field( $name, $post_id );
	$id    = 'ge-field-' . $name;

	echo '<div class="ge-field ge-field--' . esc_attr( $field['type'] ) . '">';

	if ( 'checkbox' === $field['type'] ) {
		printf(
			'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $key ),
			checked( $value, true, false ),
			esc_html( $field['label'] )
		);
	} else {
		printf(
			'<label class="ge-label" for="%s">%s</label>',
			esc_attr( $id ),
			esc_html( $field['label'] )
		);

		switch ( $field['type'] ) {
			case 'number':
				printf(
					'<input type="number" class="widefat" id="%s" name="%s" value="%s" min="%s" max="%s">',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $value ),
					esc_attr( $field['min'] ?? '' ),
					esc_attr( $field['max'] ?? '' )
				);
				break;

			case 'list':
				ge_render_list( $key, $field, is_array( $value ) ? $value : array() );
				break;

			case 'group':
				ge_render_group( $key, $field, is_array( $value ) ? $value : array() );
				break;

			default:
				printf(
					'<input type="text" class="widefat" id="%s" name="%s" value="%s">',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $value )
				);
		}
	}

	if ( ! empty( $field['help'] ) ) {
		printf( '<p class="ge-help description">%s</p>', esc_html( $field['help'] ) );
	}

	echo '</div>';
}

/** Repeating single lines: highlights, inclusions, exclusions. */
function ge_render_list( $key, $field, $rows ) {
	echo '<div class="ge-repeater" data-ge-repeater>';
	echo '<div class="ge-rows" data-ge-rows>';

	foreach ( $rows as $row ) {
		ge_list_row( $key, $row );
	}

	echo '</div>';

	// The blank row the Add button clones. Inside <template> so its inputs are
	// inert and never posted.
	echo '<template data-ge-template>';
	ge_list_row( $key, '' );
	echo '</template>';

	printf(
		'<button type="button" class="button ge-add" data-ge-add>%s</button>',
		/* translators: %s: what one row holds, e.g. "Highlight". */
		esc_html( sprintf( __( '+ Add %s', 'gangotri-expeditions' ), $field['row_label'] ) )
	);

	echo '</div>';
}

function ge_list_row( $key, $value ) {
	printf(
		'<div class="ge-row" data-ge-row>
			<span class="ge-handle dashicons dashicons-menu" aria-hidden="true"></span>
			<input type="text" class="widefat" name="%s[]" value="%s">
			<button type="button" class="button-link ge-remove" data-ge-remove aria-label="%s">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>',
		esc_attr( $key ),
		esc_attr( $value ),
		esc_attr__( 'Remove this row', 'gangotri-expeditions' )
	);
}

/** Repeating rows of subfields: itinerary days, FAQs. */
function ge_render_group( $key, $field, $rows ) {
	echo '<div class="ge-repeater" data-ge-repeater>';
	echo '<div class="ge-rows" data-ge-rows>';

	foreach ( $rows as $i => $row ) {
		ge_group_row( $key, $field, $row, $i );
	}

	echo '</div>';

	// __INDEX__ is swapped for a real number by the JS when a row is added.
	echo '<template data-ge-template>';
	ge_group_row( $key, $field, array(), '__INDEX__' );
	echo '</template>';

	printf(
		'<button type="button" class="button ge-add" data-ge-add>%s</button>',
		/* translators: %s: what one row holds, e.g. "Day". */
		esc_html( sprintf( __( '+ Add %s', 'gangotri-expeditions' ), $field['row_label'] ) )
	);

	echo '</div>';
}

function ge_group_row( $key, $field, $row, $index ) {
	echo '<div class="ge-row ge-row--group" data-ge-row>';
	echo '<span class="ge-handle dashicons dashicons-menu" aria-hidden="true"></span>';
	echo '<div class="ge-row-fields">';

	foreach ( $field['subfields'] as $sub_key => $sub ) {
		$name  = sprintf( '%s[%s][%s]', $key, $index, $sub_key );
		$value = (string) ( $row[ $sub_key ] ?? '' );

		printf( '<label class="ge-sublabel">%s</label>', esc_html( $sub['label'] ) );

		if ( 'textarea' === $sub['type'] ) {
			printf(
				'<textarea class="widefat" rows="3" name="%s">%s</textarea>',
				esc_attr( $name ),
				esc_textarea( $value )
			);
		} else {
			printf(
				'<input type="text" class="widefat" name="%s" value="%s">',
				esc_attr( $name ),
				esc_attr( $value )
			);
		}
	}

	echo '</div>';
	printf(
		'<button type="button" class="button-link ge-remove" data-ge-remove aria-label="%s"><span class="dashicons dashicons-no-alt"></span></button>',
		esc_attr__( 'Remove this row', 'gangotri-expeditions' )
	);
	echo '</div>';
}

/**
 * Saves every box that was actually posted.
 *
 * Each box is checked separately: WordPress can save a post without rendering
 * all of them (Quick Edit, the REST autosave the block editor uses), and
 * treating a missing box as "the editor cleared it" would wipe good data.
 */
function ge_save_fields( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Pages are here for the FAQ template's shared repeater.
	if ( ! in_array( $post->post_type, array( 'yatra', 'page' ), true ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( ge_meta_boxes() as $box => $title ) {
		$nonce = 'ge_nonce_' . $box;

		// Box was not on screen for this save - leave its fields alone.
		if ( ! isset( $_POST[ $nonce ] ) ) {
			continue;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'ge_save_' . $box ) ) {
			continue;
		}

		foreach ( ge_fields() as $name => $field ) {
			if ( ( $field['box'] ?? '' ) !== $box ) {
				continue;
			}

			$key = ge_meta_key( $name );

			// wp_unslash before sanitising: WordPress slashes all of $_POST.
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field type below.
			$clean = ge_sanitize_field( $field, $raw );

			if ( '' === $clean || array() === $clean ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}
	}
}
add_action( 'save_post', 'ge_save_fields', 10, 2 );

/** Styles and the repeater script, on the package editor only. */
function ge_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	// Packages always, and the FAQ page because it borrows the same repeater.
	$ge_uses_repeater = 'yatra' === get_post_type()
		|| 'page-templates/faq.php' === get_page_template_slug( get_post() );

	if ( ! $ge_uses_repeater ) {
		return;
	}

	wp_enqueue_style(
		'ge-admin',
		GE_URI . '/assets/admin/meta-boxes.css',
		array(),
		GE_VERSION
	);
	wp_enqueue_script(
		'ge-admin',
		GE_URI . '/assets/admin/meta-boxes.js',
		array(),
		GE_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'ge_admin_assets' );
