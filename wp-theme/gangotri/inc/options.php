<?php
/**
 * Theme Options - the business details that appear across the site.
 *
 * This is scripts/site.config.mjs from the static build, moved somewhere the
 * client can edit it. Every value is read through gangotri_option(), so a
 * template never reaches for get_option() directly and a missing value falls
 * back to something sane rather than printing an empty tag.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GANGOTRI_OPTION_KEY = 'gangotri_options';

/**
 * Option definitions, grouped into the sections shown on the settings page.
 *
 * @return array<string, array{title:string, fields:array<string,array<string,mixed>>}>
 */
function gangotri_option_schema(): array {
	return array(
		'contact' => array(
			'title'  => __( 'Contact details', 'gangotri' ),
			'fields' => array(
				'phone'     => array(
					'type'    => 'text',
					'label'   => __( 'Phone (as displayed)', 'gangotri' ),
					'default' => '+91 8076 378 331',
				),
				'phone_raw' => array(
					'type'    => 'text',
					'label'   => __( 'Phone (for tel: links)', 'gangotri' ),
					'default' => '+918076378331',
					'help'    => __( 'Digits and a leading + only - no spaces or brackets.', 'gangotri' ),
				),
				'whatsapp'  => array(
					'type'    => 'text',
					'label'   => __( 'WhatsApp number', 'gangotri' ),
					'default' => '918076378331',
					'help'    => __( 'Country code first, no + and no spaces.', 'gangotri' ),
				),
				'email'     => array(
					'type'    => 'text',
					'label'   => __( 'Email', 'gangotri' ),
					'default' => 'gangotriexpeditions@gmail.com',
				),
				'address'   => array(
					'type'    => 'text',
					'label'   => __( 'Address', 'gangotri' ),
					'default' => 'Rishikesh, Uttarakhand, India',
				),
				'locality'  => array(
					'type'    => 'text',
					'label'   => __( 'City', 'gangotri' ),
					'default' => 'Rishikesh',
				),
				'region'    => array(
					'type'    => 'text',
					'label'   => __( 'State', 'gangotri' ),
					'default' => 'Uttarakhand',
				),
				'postcode'  => array(
					'type'    => 'text',
					'label'   => __( 'PIN code', 'gangotri' ),
					'default' => '249201',
				),
			),
		),

		'social'  => array(
			'title'  => __( 'Social profiles', 'gangotri' ),
			'fields' => array(
				'instagram' => array( 'type' => 'text', 'label' => __( 'Instagram URL', 'gangotri' ), 'default' => '' ),
				'facebook'  => array( 'type' => 'text', 'label' => __( 'Facebook URL', 'gangotri' ), 'default' => '' ),
				'youtube'   => array( 'type' => 'text', 'label' => __( 'YouTube URL', 'gangotri' ), 'default' => '' ),
			),
		),

		'popup'   => array(
			'title'  => __( 'Enquiry popup', 'gangotri' ),
			'fields' => array(
				'popup_enabled' => array(
					'type'    => 'select',
					'label'   => __( 'Open automatically', 'gangotri' ),
					'default' => '1',
					'options' => array(
						'1' => __( 'Yes', 'gangotri' ),
						'0' => __( 'No - only when a button is clicked', 'gangotri' ),
					),
				),
				'popup_delay'   => array(
					'type'    => 'number',
					'label'   => __( 'Open after (seconds)', 'gangotri' ),
					'default' => 25,
					'min'     => 5,
					'max'     => 300,
					'help'    => __( 'Never set this near zero. Google demotes pages on mobile when a popup covers the content on arrival from search.', 'gangotri' ),
				),
				'popup_scroll'  => array(
					'type'    => 'number',
					'label'   => __( 'Or after scrolling (%)', 'gangotri' ),
					'default' => 45,
					'min'     => 10,
					'max'     => 100,
				),
			),
		),

		'seo'     => array(
			'title'  => __( 'Analytics', 'gangotri' ),
			'fields' => array(
				'ga4' => array(
					'type'    => 'text',
					'label'   => __( 'Google Analytics 4 ID', 'gangotri' ),
					'default' => '',
					'help'    => __( 'Looks like G-XXXXXXXXXX. Leave empty to load no analytics at all.', 'gangotri' ),
				),
			),
		),
	);
}

/**
 * Reads a single theme option, falling back to its declared default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function gangotri_option( string $key ) {
	static $values = null;

	if ( null === $values ) {
		$values = (array) get_option( GANGOTRI_OPTION_KEY, array() );
	}

	if ( isset( $values[ $key ] ) && '' !== $values[ $key ] ) {
		return $values[ $key ];
	}

	foreach ( gangotri_option_schema() as $section ) {
		if ( isset( $section['fields'][ $key ] ) ) {
			return $section['fields'][ $key ]['default'] ?? '';
		}
	}

	return '';
}

add_action(
	'admin_menu',
	static function (): void {
		add_menu_page(
			__( 'Site Details', 'gangotri' ),
			__( 'Site Details', 'gangotri' ),
			'manage_options',
			'gangotri-options',
			'gangotri_render_options_page',
			'dashicons-admin-generic',
			59
		);
	}
);

add_action(
	'admin_init',
	static function (): void {
		register_setting(
			'gangotri_options_group',
			GANGOTRI_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'gangotri_sanitise_options',
				'default'           => array(),
			)
		);
	}
);

/**
 * Sanitises the whole options array against the schema.
 *
 * Anything not declared in the schema is dropped rather than stored - a
 * settings array is a tempting place to smuggle values into.
 *
 * @param mixed $raw Submitted values.
 * @return array<string,mixed>
 */
function gangotri_sanitise_options( $raw ): array {
	$clean = array();

	foreach ( gangotri_option_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( ! isset( $raw[ $key ] ) ) {
				continue;
			}

			// URLs get url_raw rather than the generic text path, so a typo'd
			// scheme is dropped instead of rendered into an href.
			if ( in_array( $key, array( 'instagram', 'facebook', 'youtube' ), true ) ) {
				$clean[ $key ] = esc_url_raw( trim( (string) $raw[ $key ] ) );
				continue;
			}

			if ( 'email' === $key ) {
				$clean[ $key ] = sanitize_email( (string) $raw[ $key ] );
				continue;
			}

			$clean[ $key ] = gangotri_sanitise_field( $field, $raw[ $key ] );
		}
	}

	return $clean;
}

/**
 * Renders the settings page.
 */
function gangotri_render_options_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'These details appear in the header, footer, contact page and structured data. Changing one here changes it everywhere.', 'gangotri' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'gangotri_options_group' ); ?>

			<?php foreach ( gangotri_option_schema() as $section ) : ?>
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( $section['fields'] as $key => $field ) : ?>
						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( 'ge-opt-' . $key ); ?>">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
							</th>
							<td>
								<?php
								gangotri_render_field(
									$field + array( 'label' => '' ),
									gangotri_option( $key ),
									GANGOTRI_OPTION_KEY . '[' . $key . ']'
								);
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
