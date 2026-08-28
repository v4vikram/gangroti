<?php
/**
 * JSON-LD.
 *
 * Built as PHP arrays and handed to wp_json_encode rather than written as a
 * string in the template. The static build had to assemble the itinerary array
 * separately because a repeated block cannot emit comma-separated JSON without
 * a trailing comma - and invalid JSON-LD is silently dropped by search
 * engines, so it fails without ever telling you.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

function ge_json_ld() {
	$graph = array( ge_schema_organisation() );

	if ( is_singular( 'yatra' ) ) {
		$graph[] = ge_schema_trip();
		$graph[] = ge_schema_breadcrumb( array(
			__( 'Home', 'gangotri-expeditions' )   => home_url( '/' ),
			__( 'Yatras', 'gangotri-expeditions' ) => ge_yatras_url(),
			get_the_title()                        => get_permalink(),
		) );

		$faqs = ge_field( 'faqs' );
		if ( $faqs ) {
			$graph[] = ge_schema_faq( $faqs );
		}
	} elseif ( is_page_template( 'page-templates/faq.php' ) ) {
		$faqs = ge_field( 'faqs' );
		if ( $faqs ) {
			$graph[] = ge_schema_faq( $faqs );
		}
		$graph[] = ge_schema_breadcrumb( array(
			__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
			get_the_title()                      => get_permalink(),
		) );
	} elseif ( is_post_type_archive( 'yatra' ) ) {
		$graph[] = ge_schema_breadcrumb( array(
			__( 'Home', 'gangotri-expeditions' )     => home_url( '/' ),
			__( 'Packages', 'gangotri-expeditions' ) => ge_yatras_url(),
		) );
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array( '@context' => 'https://schema.org', '@graph' => $graph ),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'ge_json_ld', 20 );

function ge_schema_organisation() {
	$org = array(
		'@type'    => 'TravelAgency',
		'@id'      => home_url( '/#organisation' ),
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'email'    => ge_option( 'email' ),
		'telephone' => ge_option( 'phone' ),
		'address'  => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => ge_option( 'locality' ),
			'addressRegion'   => ge_option( 'region' ),
			'postalCode'      => ge_option( 'postal_code' ),
			'addressCountry'  => ge_option( 'country' ),
		),
	);

	$social = array_filter( array(
		ge_option( 'instagram' ),
		ge_option( 'facebook' ),
		ge_option( 'youtube' ),
	) );

	if ( $social ) {
		$org['sameAs'] = array_values( $social );
	}

	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo ) {
			$org['logo'] = $logo;
		}
	}

	return $org;
}

function ge_schema_trip() {
	$trip = array(
		'@type'       => 'TouristTrip',
		'@id'         => get_permalink() . '#trip',
		'name'        => get_the_title(),
		'description' => ge_summary(),
		'url'         => get_permalink(),
		'touristType' => __( 'Pilgrims and trekkers', 'gangotri-expeditions' ),
		'provider'    => array( '@id' => home_url( '/#organisation' ) ),
	);

	if ( has_post_thumbnail() ) {
		$trip['image'] = get_the_post_thumbnail_url( null, 'ge-hero' );
	}

	$itinerary = ge_field( 'itinerary' );

	if ( $itinerary ) {
		$items = array();

		foreach ( $itinerary as $i => $day ) {
			$items[] = array(
				'@type'       => 'ListItem',
				'position'    => $i + 1,
				'name'        => $day['title'] ?? '',
				'description' => $day['text'] ?? '',
			);
		}

		$trip['itinerary'] = array(
			'@type'           => 'ItemList',
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	$price = ge_field( 'price' );

	if ( $price ) {
		$trip['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (string) (int) $price,
			'priceCurrency' => 'INR',
			'availability'  => 'https://schema.org/InStock',
			'url'           => get_permalink(),
		);
	}

	return $trip;
}

function ge_schema_breadcrumb( array $crumbs ) {
	$items    = array();
	$position = 1;

	foreach ( $crumbs as $name => $url ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => $name,
			'item'     => $url,
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

function ge_schema_faq( array $faqs ) {
	$items = array();

	foreach ( $faqs as $faq ) {
		if ( empty( $faq['q'] ) || empty( $faq['a'] ) ) {
			continue;
		}

		$items[] = array(
			'@type'          => 'Question',
			'name'           => $faq['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['a'],
			),
		);
	}

	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $items,
	);
}

/**
 * Description and Open Graph tags.
 *
 * Kept deliberately small - enough for a link to preview correctly when it is
 * shared. If the client ever installs an SEO plugin this should be removed
 * rather than left to emit a second, competing set of tags.
 */
function ge_meta_tags() {
	if ( is_singular( 'yatra' ) ) {
		$description = ge_summary();
		$image       = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ge-hero' ) : '';
		$url         = get_permalink();
		$title       = get_the_title();
	} elseif ( is_singular() ) {
		$description = ge_summary();
		$image       = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ge-hero' ) : '';
		$url         = get_permalink();
		$title       = get_the_title();
	} else {
		$description = get_bloginfo( 'description' );
		$image       = GE_URI . '/assets/img/hero/hero-1.jpg';
		$url         = home_url( add_query_arg( null, null ) );
		$title       = wp_get_document_title();
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );

	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}
}
add_action( 'wp_head', 'ge_meta_tags', 5 );
