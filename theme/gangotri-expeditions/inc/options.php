<?php
/**
 * Business details.
 *
 * This is scripts/site.config.mjs from the static build, moved to where the
 * client can edit it. The keys are unchanged, so a template that said
 * {{site.phone}} now says ge_option( 'phone' ) and nothing else moved.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defaults, and the definition of which fields exist at all.
 *
 * `sanitize` names the callback used both when saving from the Customizer and
 * when reading back, so a value can never reach a template unsanitised even if
 * it was written straight into the options table.
 */
function ge_option_schema() {
	return array(
		'tagline'     => array(
			'label'    => __( 'Tagline', 'gangotri-expeditions' ),
			'default'  => 'Spiritual Journeys. Timeless Memories.',
			'sanitize' => 'sanitize_text_field',
		),
		'phone'       => array(
			'label'    => __( 'Phone (as displayed)', 'gangotri-expeditions' ),
			'default'  => '+91 8076 378 331',
			'sanitize' => 'sanitize_text_field',
		),
		'phone_raw'   => array(
			'label'    => __( 'Phone (for tel: links)', 'gangotri-expeditions' ),
			'default'  => '+918076378331',
			'sanitize' => 'sanitize_text_field',
		),
		'whatsapp'    => array(
			'label'    => __( 'WhatsApp number (digits only, with country code)', 'gangotri-expeditions' ),
			'default'  => '918076378331',
			'sanitize' => 'ge_sanitize_digits',
		),
		'email'       => array(
			'label'    => __( 'Enquiry email', 'gangotri-expeditions' ),
			'default'  => 'gangotriexpeditions@gmail.com',
			'sanitize' => 'sanitize_email',
		),
		'address'     => array(
			'label'    => __( 'Address', 'gangotri-expeditions' ),
			'default'  => 'Rishikesh, Uttarakhand, India',
			'sanitize' => 'sanitize_text_field',
		),
		'locality'    => array(
			'label'    => __( 'City', 'gangotri-expeditions' ),
			'default'  => 'Rishikesh',
			'sanitize' => 'sanitize_text_field',
		),
		'region'      => array(
			'label'    => __( 'State', 'gangotri-expeditions' ),
			'default'  => 'Uttarakhand',
			'sanitize' => 'sanitize_text_field',
		),
		'postal_code' => array(
			'label'    => __( 'PIN code', 'gangotri-expeditions' ),
			'default'  => '249201',
			'sanitize' => 'sanitize_text_field',
		),
		'country'     => array(
			'label'    => __( 'Country code', 'gangotri-expeditions' ),
			'default'  => 'IN',
			'sanitize' => 'sanitize_text_field',
		),
		'instagram'   => array(
			'label'    => __( 'Instagram URL', 'gangotri-expeditions' ),
			'default'  => 'https://www.instagram.com/gangotri_expeditions',
			'sanitize' => 'esc_url_raw',
		),
		'facebook'    => array(
			'label'    => __( 'Facebook URL', 'gangotri-expeditions' ),
			'default'  => 'https://facebook.com/',
			'sanitize' => 'esc_url_raw',
		),
		'youtube'     => array(
			'label'    => __( 'YouTube URL', 'gangotri-expeditions' ),
			'default'  => 'https://youtube.com/',
			'sanitize' => 'esc_url_raw',
		),
		'enquiry_to'  => array(
			'label'    => __( 'Send enquiry notifications to', 'gangotri-expeditions' ),
			'default'  => '',
			'sanitize' => 'sanitize_email',
			'help'     => __( 'Leave blank to use the enquiry email above.', 'gangotri-expeditions' ),
		),
	);
}

/** Digits only - a WhatsApp link breaks on spaces or a leading +. */
function ge_sanitize_digits( $value ) {
	return preg_replace( '/\D/', '', (string) $value );
}

/**
 * Reads one business detail, falling back to the default above.
 *
 * Returns the raw value: escaping is the template's job, because the right
 * escape depends on where it lands (esc_url in an href, esc_attr in a title).
 */
function ge_option( $key ) {
	$schema = ge_option_schema();

	if ( ! isset( $schema[ $key ] ) ) {
		return '';
	}

	$value = get_theme_mod( 'ge_' . $key, $schema[ $key ]['default'] );

	return call_user_func( $schema[ $key ]['sanitize'], $value );
}

/** The WhatsApp deep link, optionally with a message pre-written. */
function ge_whatsapp_url( $message = '' ) {
	$url = 'https://wa.me/' . ge_option( 'whatsapp' );

	if ( '' !== $message ) {
		$url = add_query_arg( 'text', rawurlencode( $message ), $url );
	}

	return $url;
}

/** Registers the fields above as one Customizer panel. */
function ge_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'ge_business', array(
		'title'       => __( 'Business details', 'gangotri-expeditions' ),
		'priority'    => 30,
		'description' => __( 'Phone, email and social links, used across every page.', 'gangotri-expeditions' ),
	) );

	foreach ( ge_option_schema() as $key => $field ) {
		$wp_customize->add_setting( 'ge_' . $key, array(
			'default'           => $field['default'],
			'sanitize_callback' => $field['sanitize'],
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( 'ge_' . $key, array(
			'label'       => $field['label'],
			'description' => $field['help'] ?? '',
			'section'     => 'ge_business',
			'type'        => 'esc_url_raw' === $field['sanitize'] ? 'url' : 'text',
		) );
	}
}
add_action( 'customize_register', 'ge_customize_register' );
