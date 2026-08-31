<?php
/**
 * Helpers the templates call.
 *
 * Each one exists so a template stays markup rather than logic - and so the
 * escaping happens in exactly one place per value.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formatted price, Indian digit grouping.
 *
 * @param int|null $post_id Defaults to the current post.
 */
function gangotri_price( ?int $post_id = null ): string {
	$price = (int) get_post_meta( $post_id ?? get_the_ID(), 'ge_price', true );
	return $price ? number_format_i18n( $price ) : '';
}

/**
 * A meta value, already escaped for output in text.
 *
 * @param string   $key     Meta key without the ge_ prefix.
 * @param int|null $post_id Defaults to the current post.
 */
function gangotri_meta( string $key, ?int $post_id = null ): string {
	return esc_html( (string) get_post_meta( $post_id ?? get_the_ID(), 'ge_' . $key, true ) );
}

/**
 * A repeating or list meta value.
 *
 * @param string   $key     Meta key without the ge_ prefix.
 * @param int|null $post_id Defaults to the current post.
 * @return array<int,mixed>
 */
function gangotri_rows( string $key, ?int $post_id = null ): array {
	$value = get_post_meta( $post_id ?? get_the_ID(), 'ge_' . $key, true );
	return is_array( $value ) ? $value : array();
}

/**
 * The WhatsApp deep link, optionally with the message pre-written.
 */
function gangotri_whatsapp_url( string $message = '' ): string {
	$number = preg_replace( '/\D/', '', (string) gangotri_option( 'whatsapp' ) );

	if ( ! $number ) {
		return '';
	}

	$url = 'https://wa.me/' . $number;

	if ( $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}

	return $url;
}

/**
 * Renders an inline SVG icon from the sprite.
 *
 * The sprite is inlined once per page by gangotri_sprite(), so this is only a
 * reference - it costs a few bytes rather than a request.
 */
function gangotri_icon( string $name, string $classes = 'icon' ): string {
	return sprintf(
		'<svg class="%s" aria-hidden="true"><use href="#i-%s"/></svg>',
		esc_attr( $classes ),
		esc_attr( $name )
	);
}

/**
 * Inlines the SVG sprite.
 *
 * Inline rather than an external file because <use href="file.svg#id"> is
 * blocked cross-origin in several browsers and needs a second request in the
 * rest. The sprite is a few KB and gzips to almost nothing.
 */
function gangotri_sprite(): void {
	$path = GANGOTRI_DIR . '/assets/icons/sprite.svg';

	if ( ! file_exists( $path ) ) {
		return;
	}

	echo file_get_contents( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local, build-generated markup.
}

/**
 * A responsive <img> for a package, falling back to the placeholder shipped
 * with the theme when a package has no featured image yet.
 *
 * @param string $size  Registered image size.
 * @param array<string,string> $attr Extra attributes.
 */
function gangotri_package_image( string $size = 'gangotri-card', array $attr = array() ): string {
	$attr = wp_parse_args(
		$attr,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	if ( has_post_thumbnail() ) {
		return get_the_post_thumbnail( null, $size, $attr );
	}

	return sprintf(
		'<img src="%s" alt="" width="800" height="600" loading="lazy" decoding="async">',
		esc_url( GANGOTRI_URI . '/assets/img/placeholder.webp' )
	);
}

/**
 * The site's canonical package archive URL.
 */
function gangotri_packages_url(): string {
	return (string) get_post_type_archive_link( 'yatra' );
}

/**
 * Breadcrumb trail.
 *
 * @param array<int,array{label:string, url?:string}> $trail Crumbs after Home.
 */
function gangotri_breadcrumbs( array $trail ): void {
	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'gangotri' ) . '"><ol>';
	printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'gangotri' ) );

	foreach ( $trail as $crumb ) {
		if ( ! empty( $crumb['url'] ) ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $crumb['url'] ), esc_html( $crumb['label'] ) );
		} else {
			printf( '<li aria-current="page">%s</li>', esc_html( $crumb['label'] ) );
		}
	}

	echo '</ol></nav>';
}

/**
 * Google Analytics, only when an ID has been entered and only for visitors.
 *
 * Logged-in administrators are excluded so the client's own browsing does not
 * show up as traffic - the usual reason early analytics numbers look wrong.
 */
add_action(
	'wp_head',
	static function (): void {
		$id = (string) gangotri_option( 'ga4' );

		if ( ! $id || ! preg_match( '/^G-[A-Z0-9]+$/', $id ) || current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', <?php echo wp_json_encode( $id ); ?>);
		</script>
		<?php
	},
	20
);
