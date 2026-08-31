<?php
/**
 * Structured data.
 *
 * Output as one @graph in the footer rather than several separate blocks, so
 * the entities can reference each other by @id - a package points at the
 * organisation that runs it instead of repeating it.
 *
 * Nothing here invents a rating. AggregateRating without real, verifiable
 * reviews behind it is a manual action waiting to happen.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The organisation node, referenced by everything else.
 *
 * @return array<string,mixed>
 */
function gangotri_schema_org(): array {
	$node = array(
		'@type'       => 'TravelAgency',
		'@id'         => home_url( '/#org' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => get_bloginfo( 'description' ),
		'address'     => array_filter(
			array(
				'@type'           => 'PostalAddress',
				'addressLocality' => gangotri_option( 'locality' ),
				'addressRegion'   => gangotri_option( 'region' ),
				'postalCode'      => gangotri_option( 'postcode' ),
				'addressCountry'  => 'IN',
			)
		),
		'areaServed'  => array( '@type' => 'State', 'name' => 'Uttarakhand' ),
	);

	if ( gangotri_option( 'phone_raw' ) ) {
		$node['telephone'] = gangotri_option( 'phone_raw' );
	}

	if ( gangotri_option( 'email' ) ) {
		$node['email'] = gangotri_option( 'email' );
	}

	$logo = get_theme_mod( 'custom_logo' )
		? wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' )
		: GANGOTRI_URI . '/assets/img/logo-lockup.png';

	if ( $logo ) {
		$node['logo'] = $logo;
	}

	// Only profiles that have actually been filled in.
	$social = array_filter(
		array(
			(string) gangotri_option( 'instagram' ),
			(string) gangotri_option( 'facebook' ),
			(string) gangotri_option( 'youtube' ),
		)
	);

	if ( $social ) {
		$node['sameAs'] = array_values( $social );
	}

	// A price range helps the knowledge panel, but only if there is real data.
	$prices = get_posts(
		array(
			'post_type'      => 'yatra',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'ge_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	$values = array_filter( array_map( static fn( $id ) => (int) get_post_meta( $id, 'ge_price', true ), $prices ) );

	if ( $values ) {
		$node['priceRange'] = sprintf( 'INR %s - INR %s', min( $values ), max( $values ) );
	}

	return $node;
}

/**
 * The TouristTrip node for the package being viewed.
 *
 * @return array<string,mixed>
 */
function gangotri_schema_trip(): array {
	$node = array(
		'@type'       => 'TouristTrip',
		'@id'         => get_permalink() . '#trip',
		'name'        => get_the_title(),
		'description' => get_the_excerpt(),
		'url'         => get_permalink(),
		'touristType' => __( 'Pilgrims and trekkers', 'gangotri' ),
		'provider'    => array( '@id' => home_url( '/#org' ) ),
	);

	if ( has_post_thumbnail() ) {
		$node['image'] = get_the_post_thumbnail_url( null, 'gangotri-hero' );
	}

	$itinerary = gangotri_rows( 'itinerary' );

	if ( $itinerary ) {
		$node['itinerary'] = array(
			'@type'           => 'ItemList',
			'numberOfItems'   => count( $itinerary ),
			'itemListElement' => array_map(
				static fn( int $i, array $day ): array => array(
					'@type'       => 'ListItem',
					'position'    => $i + 1,
					'name'        => (string) ( $day['title'] ?? '' ),
					'description' => (string) ( $day['text'] ?? '' ),
				),
				array_keys( $itinerary ),
				$itinerary
			),
		);
	}

	$price = (int) get_post_meta( get_the_ID(), 'ge_price', true );

	if ( $price ) {
		$node['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (string) $price,
			'priceCurrency' => 'INR',
			'availability'  => 'https://schema.org/InStock',
			'url'           => get_permalink(),
		);
	}

	return $node;
}

/**
 * FAQPage built from a question repeater.
 *
 * The same shape is used by packages, the home page and the FAQ and Services
 * templates, so the key is a parameter rather than three near-identical
 * functions.
 *
 * @param string   $key     Meta key without the ge_ prefix.
 * @param int|null $post_id Explicit post, since this runs after the loop.
 * @return array<string,mixed>|null
 */
function gangotri_schema_faq( string $key = 'faqs', ?int $post_id = null ): ?array {
	$post_id = $post_id ?? (int) get_the_ID();

	$faqs = array_filter(
		gangotri_rows( $key, $post_id ),
		static fn( $row ) => ! empty( $row['q'] ) && ! empty( $row['a'] )
	);

	if ( ! $faqs ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink( $post_id ) . '#faq',
		'mainEntity' => array_map(
			static fn( array $faq ): array => array(
				'@type'          => 'Question',
				'name'           => (string) $faq['q'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => (string) $faq['a'] ),
			),
			array_values( $faqs )
		),
	);
}

/**
 * Breadcrumb trail matching what the templates render.
 *
 * @return array<string,mixed>|null
 */
function gangotri_schema_breadcrumbs(): ?array {
	$crumbs = array( array( 'name' => __( 'Home', 'gangotri' ), 'item' => home_url( '/' ) ) );

	if ( is_singular( 'yatra' ) ) {
		$crumbs[] = array( 'name' => __( 'Packages', 'gangotri' ), 'item' => gangotri_packages_url() );
		$crumbs[] = array( 'name' => get_the_title(), 'item' => get_permalink() );
	} elseif ( is_post_type_archive( 'yatra' ) ) {
		$crumbs[] = array( 'name' => __( 'Packages', 'gangotri' ), 'item' => gangotri_packages_url() );
	} elseif ( is_tax() ) {
		$crumbs[] = array( 'name' => __( 'Packages', 'gangotri' ), 'item' => gangotri_packages_url() );
		$crumbs[] = array( 'name' => single_term_title( '', false ), 'item' => get_term_link( get_queried_object_id() ) );
	} elseif ( is_page() ) {
		$crumbs[] = array( 'name' => get_the_title(), 'item' => get_permalink() );
	} else {
		return null;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array_map(
			static fn( int $i, array $crumb ): array => array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb['name'],
				'item'     => is_string( $crumb['item'] ) ? $crumb['item'] : '',
			),
			array_keys( $crumbs ),
			$crumbs
		),
	);
}

add_action(
	'wp_footer',
	static function (): void {
		$graph = array( gangotri_schema_org() );

		if ( is_front_page() ) {
			$graph[] = array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'publisher'       => array( '@id' => home_url( '/#org' ) ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array(
						'@type'       => 'EntryPoint',
						'urlTemplate' => home_url( '/?s={search_term_string}' ),
					),
					'query-input' => 'required name=search_term_string',
				),
			);
		}

		if ( is_singular( 'yatra' ) ) {
			$graph[] = gangotri_schema_trip();
		}

		// Whichever repeater this page's template actually renders.
		$faq_key = null;

		if ( is_singular( 'yatra' ) ) {
			$faq_key = 'faqs';
		} elseif ( is_page() ) {
			$template = (string) get_page_template_slug( get_queried_object_id() );

			if ( in_array( $template, array( 'page-faq.php', 'page-services.php' ), true ) ) {
				$faq_key = 'page_faqs';
			} elseif ( 'page-home.php' === $template ) {
				$faq_key = 'home_faqs';
			}
		}

		if ( $faq_key ) {
			$faq = gangotri_schema_faq( $faq_key, (int) get_queried_object_id() );

			if ( $faq ) {
				$graph[] = $faq;
			}
		}

		$breadcrumbs = gangotri_schema_breadcrumbs();
		if ( $breadcrumbs ) {
			$graph[] = $breadcrumbs;
		}

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode(
				array( '@context' => 'https://schema.org', '@graph' => $graph ),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	},
	5
);
