<?php
/**
 * The enquiry endpoint.
 *
 * src/js/form.js posts here as `action=ge_enquiry` and expects WordPress's own
 * wp_send_json_success / _error envelope, which is why the JavaScript needed
 * no changes when the site moved off the static build.
 *
 * Every submission is stored as a private post before the email is attempted.
 * Shared hosting loses mail regularly, and an enquiry that only ever existed
 * in a mail queue is a lost customer; one that is in the database can be read
 * back even if nothing was delivered.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/** Both hooks: enquiries come from logged-out visitors, and from us testing. */
add_action( 'wp_ajax_ge_enquiry', 'ge_handle_enquiry' );
add_action( 'wp_ajax_nopriv_ge_enquiry', 'ge_handle_enquiry' );

/** How many submissions one IP may make in an hour. */
const GE_ENQUIRY_LIMIT  = 8;
const GE_ENQUIRY_WINDOW = HOUR_IN_SECONDS;

function ge_handle_enquiry() {
	// A referer nonce, not a plain one: it also ties the submission to the
	// page it was rendered on.
	if ( ! check_ajax_referer( 'ge_enquiry', 'ge_nonce', false ) ) {
		wp_send_json_error(
			array( 'message' => __( 'That form expired. Please reload the page and try again.', 'gangotri-expeditions' ) ),
			403
		);
	}

	// A filled honeypot is a bot. Answer as though it worked: telling a
	// scraper it was caught only teaches it to fill the field differently.
	if ( ! empty( $_POST['company'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Thank you - we will be in touch.', 'gangotri-expeditions' ) ) );
	}

	if ( ge_enquiry_rate_limited() ) {
		wp_send_json_error(
			array( 'message' => __( 'That is a lot of enquiries from one connection. Please call us instead.', 'gangotri-expeditions' ) ),
			429
		);
	}

	$fields = ge_enquiry_fields();

	if ( '' === $fields['name'] || '' === $fields['phone'] ) {
		wp_send_json_error(
			array( 'message' => __( 'Please give us a name and a phone number.', 'gangotri-expeditions' ) ),
			400
		);
	}

	if ( '' !== $fields['email'] && ! is_email( $fields['email'] ) ) {
		wp_send_json_error(
			array( 'message' => __( 'That email address does not look right.', 'gangotri-expeditions' ) ),
			400
		);
	}

	$post_id = ge_store_enquiry( $fields );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'We could not save that. Please call or WhatsApp us instead.', 'gangotri-expeditions' ) ),
			500
		);
	}

	// Delivery failing must not fail the request: the enquiry is already safe
	// in the database, and the visitor cannot do anything about our mail host.
	ge_notify_enquiry( $post_id, $fields );

	wp_send_json_success( array(
		'message' => __( 'Thank you. We have your details and will call you shortly.', 'gangotri-expeditions' ),
	) );
}

/** Reads and cleans the posted fields. Names match parts/enquiry-form.php. */
function ge_enquiry_fields() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified by check_ajax_referer above.
	$get = static function ( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	};

	$travellers = $get( 'travellers' );
	$date       = $get( 'date' );

	return array(
		'name'       => $get( 'name' ),
		'phone'      => $get( 'phone' ),
		'email'      => sanitize_email( $get( 'email' ) ),
		'yatra'      => $get( 'yatra' ),
		'travellers' => '' === $travellers ? '' : (string) max( 1, min( 60, (int) $travellers ) ),
		// An <input type=date> is always Y-m-d; anything else was not typed
		// by a browser and is not worth storing.
		'date'       => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '',
		'message'    => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		'source'     => $get( 'source' ),
	);
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}

/**
 * Simple per-IP throttle held in a transient.
 *
 * Not a defence against a determined attacker - it is there so one stuck
 * retry loop or a crude spam script cannot fill the enquiries list overnight.
 */
function ge_enquiry_rate_limited() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';

	if ( '' === $ip ) {
		return false;
	}

	$key   = 'ge_enq_' . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= GE_ENQUIRY_LIMIT ) {
		return true;
	}

	set_transient( $key, $count + 1, GE_ENQUIRY_WINDOW );

	return false;
}

/** Stores the enquiry as a private post, with the fields as meta. */
function ge_store_enquiry( array $fields ) {
	$title = sprintf(
		/* translators: 1: enquirer's name, 2: package they asked about. */
		__( '%1$s - %2$s', 'gangotri-expeditions' ),
		$fields['name'],
		$fields['yatra'] ?: __( 'General enquiry', 'gangotri-expeditions' )
	);

	$post_id = wp_insert_post( array(
		'post_type'   => 'ge_enquiry',
		'post_title'  => $title,
		'post_status' => 'private',
	), true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	foreach ( $fields as $key => $value ) {
		if ( '' !== $value ) {
			update_post_meta( $post_id, '_ge_' . $key, $value );
		}
	}

	return $post_id;
}

/** Emails the enquiry to whoever the Customizer names. */
function ge_notify_enquiry( $post_id, array $fields ) {
	$to = ge_option( 'enquiry_to' ) ?: ge_option( 'email' );

	if ( ! is_email( $to ) ) {
		return;
	}

	$labels = array(
		'name'       => __( 'Name', 'gangotri-expeditions' ),
		'phone'      => __( 'Phone', 'gangotri-expeditions' ),
		'email'      => __( 'Email', 'gangotri-expeditions' ),
		'yatra'      => __( 'Package', 'gangotri-expeditions' ),
		'travellers' => __( 'Travellers', 'gangotri-expeditions' ),
		'date'       => __( 'Preferred date', 'gangotri-expeditions' ),
		'message'    => __( 'Message', 'gangotri-expeditions' ),
		'source'     => __( 'Sent from', 'gangotri-expeditions' ),
	);

	$lines = array();

	foreach ( $labels as $key => $label ) {
		if ( '' !== ( $fields[ $key ] ?? '' ) ) {
			$lines[] = $label . ': ' . $fields[ $key ];
		}
	}

	$lines[] = '';
	$lines[] = __( 'In the admin:', 'gangotri-expeditions' ) . ' ' . get_edit_post_link( $post_id, 'raw' );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	// Reply-To, never From: sending as the visitor's address makes SPF fail
	// and the whole message land in spam.
	if ( is_email( $fields['email'] ) ) {
		$headers[] = 'Reply-To: ' . $fields['name'] . ' <' . $fields['email'] . '>';
	}

	wp_mail(
		$to,
		sprintf(
			/* translators: %s: enquirer's name. */
			__( 'Website enquiry from %s', 'gangotri-expeditions' ),
			$fields['name']
		),
		implode( "\n", $lines ),
		$headers
	);
}

/**
 * The enquiries list is only useful if it shows the details without opening
 * each one, so the columns are replaced with the fields that matter.
 */
function ge_enquiry_columns( $columns ) {
	return array(
		'cb'      => $columns['cb'] ?? '',
		'title'   => __( 'Enquiry', 'gangotri-expeditions' ),
		'phone'   => __( 'Phone', 'gangotri-expeditions' ),
		'email'   => __( 'Email', 'gangotri-expeditions' ),
		'yatra'   => __( 'Package', 'gangotri-expeditions' ),
		'date'    => __( 'Received', 'gangotri-expeditions' ),
	);
}
add_filter( 'manage_ge_enquiry_posts_columns', 'ge_enquiry_columns' );

function ge_enquiry_column( $column, $post_id ) {
	$value = get_post_meta( $post_id, '_ge_' . $column, true );

	if ( 'phone' === $column && $value ) {
		printf( '<a href="tel:%s">%s</a>', esc_attr( $value ), esc_html( $value ) );
		return;
	}

	if ( 'email' === $column && $value ) {
		printf( '<a href="mailto:%s">%s</a>', esc_attr( $value ), esc_html( $value ) );
		return;
	}

	if ( in_array( $column, array( 'phone', 'email', 'yatra' ), true ) ) {
		echo esc_html( $value ?: '-' );
	}
}
add_action( 'manage_ge_enquiry_posts_custom_column', 'ge_enquiry_column', 10, 2 );

/** The full submission, shown on the enquiry's own screen. */
function ge_enquiry_meta_box() {
	add_meta_box(
		'ge-enquiry-detail',
		__( 'Submission', 'gangotri-expeditions' ),
		static function ( $post ) {
			$labels = array(
				'name'       => __( 'Name', 'gangotri-expeditions' ),
				'phone'      => __( 'Phone', 'gangotri-expeditions' ),
				'email'      => __( 'Email', 'gangotri-expeditions' ),
				'yatra'      => __( 'Package', 'gangotri-expeditions' ),
				'travellers' => __( 'Travellers', 'gangotri-expeditions' ),
				'date'       => __( 'Preferred date', 'gangotri-expeditions' ),
				'message'    => __( 'Message', 'gangotri-expeditions' ),
				'source'     => __( 'Sent from', 'gangotri-expeditions' ),
			);

			echo '<table class="widefat striped"><tbody>';

			foreach ( $labels as $key => $label ) {
				$value = get_post_meta( $post->ID, '_ge_' . $key, true );
				printf(
					'<tr><th style="width:12rem">%s</th><td>%s</td></tr>',
					esc_html( $label ),
					nl2br( esc_html( $value ?: '-' ) )
				);
			}

			echo '</tbody></table>';
		},
		'ge_enquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_ge_enquiry', 'ge_enquiry_meta_box' );
