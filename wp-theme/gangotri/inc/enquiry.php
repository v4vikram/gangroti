<?php
/**
 * The enquiry form handler.
 *
 * Two things happen on a valid submission, in this order: the enquiry is
 * written to the database, then an email is attempted. That order matters -
 * shared hosting drops mail regularly, and a lead that only ever existed in an
 * email that never arrived is a lead the business never knows it lost.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A private post type holding submitted enquiries, so nothing depends on email
 * arriving. Not public, not searchable, never rendered on the front end.
 */
add_action(
	'init',
	static function (): void {
		register_post_type(
			'ge_enquiry',
			array(
				'labels'          => array(
					'name'          => __( 'Enquiries', 'gangotri' ),
					'singular_name' => __( 'Enquiry', 'gangotri' ),
					'menu_name'     => __( 'Enquiries', 'gangotri' ),
					'not_found'     => __( 'No enquiries yet', 'gangotri' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 21,
				'supports'        => array( 'title' ),
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'delete_with_user' => false,
			)
		);
	}
);

/**
 * Fields accepted from the form, and how each is cleaned.
 *
 * Anything not in this list is ignored. The keys match the `name` attributes
 * in template-parts/enquiry-form.php, which are the same names the static
 * build used.
 *
 * @return array<string, array{label:string, sanitise:string, required?:bool}>
 */
function gangotri_enquiry_fields(): array {
	return array(
		'name'       => array( 'label' => 'Name', 'sanitise' => 'text', 'required' => true ),
		'phone'      => array( 'label' => 'Phone', 'sanitise' => 'text', 'required' => true ),
		'email'      => array( 'label' => 'Email', 'sanitise' => 'email' ),
		'yatra'      => array( 'label' => 'Package', 'sanitise' => 'text' ),
		'travellers' => array( 'label' => 'Travellers', 'sanitise' => 'int' ),
		'date'       => array( 'label' => 'Preferred date', 'sanitise' => 'text' ),
		'message'    => array( 'label' => 'Message', 'sanitise' => 'textarea' ),
	);
}

/**
 * Rate limit: how many submissions one IP may make per hour.
 *
 * Without this the honeypot is the only thing between the form and a script
 * that has learned to leave it empty.
 */
const GANGOTRI_ENQUIRY_LIMIT = 5;

/**
 * A coarse identifier for rate limiting.
 *
 * Hashed, never stored raw: it is only ever compared against itself, so there
 * is no reason to keep visitors' IP addresses lying around.
 */
function gangotri_request_key(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	return 'ge_rate_' . md5( $ip );
}

add_action( 'wp_ajax_ge_enquiry', 'gangotri_handle_enquiry' );
add_action( 'wp_ajax_nopriv_ge_enquiry', 'gangotri_handle_enquiry' );

/**
 * Receives, validates and stores an enquiry.
 */
function gangotri_handle_enquiry(): void {
	check_ajax_referer( 'ge_enquiry', 'nonce' );

	// Honeypot. A bot fills every field it finds; a person never sees this one.
	// Answer as though it worked - telling a bot why it failed only helps it.
	if ( ! empty( $_POST['company'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Thank you - we will be in touch.', 'gangotri' ) ) );
	}

	$key   = gangotri_request_key();
	$count = (int) get_transient( $key );

	if ( $count >= GANGOTRI_ENQUIRY_LIMIT ) {
		wp_send_json_error(
			array( 'message' => __( 'That is a lot of enquiries in one go. Please call us instead.', 'gangotri' ) ),
			429
		);
	}

	$values = array();
	$errors = array();

	foreach ( gangotri_enquiry_fields() as $key_name => $field ) {
		$raw = isset( $_POST[ $key_name ] ) ? wp_unslash( $_POST[ $key_name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised on the next lines.

		$values[ $key_name ] = match ( $field['sanitise'] ) {
			'email'    => sanitize_email( (string) $raw ),
			'int'      => '' === $raw ? '' : (string) absint( $raw ),
			'textarea' => sanitize_textarea_field( (string) $raw ),
			default    => sanitize_text_field( (string) $raw ),
		};

		if ( ! empty( $field['required'] ) && '' === $values[ $key_name ] ) {
			$errors[] = $field['label'];
		}
	}

	if ( '' !== $values['email'] && ! is_email( $values['email'] ) ) {
		$errors[] = 'a valid email';
	}

	if ( $errors ) {
		wp_send_json_error(
			array(
				/* translators: %s: comma separated list of missing fields. */
				'message' => sprintf( __( 'We still need: %s.', 'gangotri' ), implode( ', ', $errors ) ),
			),
			422
		);
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	// Store first. Email can fail; the database should not lose the lead.
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'ge_enquiry',
			'post_status' => 'publish',
			'post_title'  => sprintf(
				'%s - %s',
				$values['name'],
				$values['yatra'] ? $values['yatra'] : __( 'General enquiry', 'gangotri' )
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Something went wrong saving that. Please call or WhatsApp us.', 'gangotri' ) ),
			500
		);
	}

	foreach ( $values as $meta_key => $value ) {
		update_post_meta( $post_id, 'ge_' . $meta_key, $value );
	}

	$source = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : '';
	if ( $source ) {
		update_post_meta( $post_id, 'ge_source', $source );
	}

	gangotri_notify_enquiry( $values, $source );

	wp_send_json_success(
		array( 'message' => __( 'Thank you. We have your details and will call you shortly.', 'gangotri' ) )
	);
}

/**
 * Emails the enquiry to the business.
 *
 * Failure is logged, not surfaced: the enquiry is already saved, so telling the
 * visitor it failed would send them away from a form that actually worked.
 *
 * @param array<string,string> $values Clean field values.
 * @param string               $source URL the enquiry came from.
 */
function gangotri_notify_enquiry( array $values, string $source ): void {
	$to = (string) gangotri_option( 'email' );

	if ( ! is_email( $to ) ) {
		return;
	}

	$lines = array();
	foreach ( gangotri_enquiry_fields() as $key => $field ) {
		if ( '' !== $values[ $key ] ) {
			$lines[] = $field['label'] . ': ' . $values[ $key ];
		}
	}

	if ( $source ) {
		$lines[] = 'Sent from: ' . $source;
	}

	$sent = wp_mail(
		$to,
		sprintf(
			/* translators: 1: enquirer name, 2: package name. */
			__( 'New enquiry: %1$s - %2$s', 'gangotri' ),
			$values['name'],
			$values['yatra'] ? $values['yatra'] : __( 'General', 'gangotri' )
		),
		implode( "\n", $lines ),
		// Reply-To only. Setting From to the visitor's address makes the mail
		// fail SPF and land in spam, which is the usual reason "the form does
		// not work" on shared hosting.
		$values['email'] ? array( 'Reply-To: ' . $values['email'] ) : array()
	);

	if ( ! $sent ) {
		error_log( 'Gangotri: enquiry saved but wp_mail() failed. Configure SMTP.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Shows the submitted values on the enquiry edit screen - it is a record, not
 * something anyone should be editing.
 */
add_action(
	'add_meta_boxes',
	static function (): void {
		add_meta_box(
			'gangotri_enquiry_detail',
			__( 'Enquiry', 'gangotri' ),
			static function ( WP_Post $post ): void {
				echo '<table class="widefat striped"><tbody>';

				foreach ( gangotri_enquiry_fields() as $key => $field ) {
					$value = (string) get_post_meta( $post->ID, 'ge_' . $key, true );
					if ( '' === $value ) {
						continue;
					}

					printf(
						'<tr><th style="width:160px">%s</th><td>%s</td></tr>',
						esc_html( $field['label'] ),
						'email' === $key
							? '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>'
							: nl2br( esc_html( $value ) )
					);
				}

				$source = (string) get_post_meta( $post->ID, 'ge_source', true );
				if ( $source ) {
					printf(
						'<tr><th>%s</th><td><a href="%s">%s</a></td></tr>',
						esc_html__( 'Sent from', 'gangotri' ),
						esc_url( $source ),
						esc_html( $source )
					);
				}

				echo '</tbody></table>';
			},
			'ge_enquiry',
			'normal',
			'high'
		);
	}
);

/**
 * Unread enquiries as a bubble on the admin menu, the way comments work.
 * Without it nobody opens the screen.
 */
add_filter(
	'add_menu_classes',
	static function ( array $menu ): array {
		$recent = (int) ( wp_count_posts( 'ge_enquiry' )->publish ?? 0 );
		if ( ! $recent ) {
			return $menu;
		}

		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=ge_enquiry' === $item[2] ) {
				$menu[ $i ][0] .= sprintf(
					' <span class="awaiting-mod"><span class="pending-count">%d</span></span>',
					$recent
				);
				break;
			}
		}
		return $menu;
	}
);
