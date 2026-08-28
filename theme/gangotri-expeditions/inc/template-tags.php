<?php
/**
 * Helpers the templates lean on.
 *
 * Anything a template would otherwise repeat lives here, so markup that has to
 * stay consistent - an icon, a price, a breadcrumb - has exactly one spelling.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * One icon from the inlined sprite.
 *
 * `$name` is the id without the `i-` prefix, so ge_icon( 'route' ) resolves to
 * <use href="#i-route">. Icons are decorative wherever they appear next to a
 * label, which is everywhere here, hence aria-hidden.
 */
function ge_icon( $name, $classes = '' ) {
	printf(
		'<svg class="icon %s" aria-hidden="true"><use href="#i-%s"/></svg>',
		esc_attr( $classes ),
		esc_attr( $name )
	);
}

/** The same, returned rather than printed, for use inside sprintf(). */
function ge_get_icon( $name, $classes = '' ) {
	ob_start();
	ge_icon( $name, $classes );
	return ob_get_clean();
}

/** Indian digit grouping: 1,20,000 rather than 120,000. */
function ge_format_price( $amount ) {
	$amount = (int) $amount;

	if ( $amount < 1000 ) {
		return (string) $amount;
	}

	$last_three = substr( (string) $amount, -3 );
	$rest       = substr( (string) $amount, 0, -3 );

	return preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest ) . ',' . $last_three;
}

/** The archive URL, wherever the CPT's rewrite happens to point. */
function ge_yatras_url() {
	return get_post_type_archive_link( 'yatra' ) ?: home_url( '/' );
}

/**
 * The first taxonomy term as plain text.
 *
 * Packages carry one type and one destination; the templates want the name,
 * not a linked list, so this stays simpler than get_the_term_list().
 */
function ge_term_name( $taxonomy, $post_id = null ) {
	$terms = get_the_terms( $post_id ?: get_the_ID(), $taxonomy );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * <option> per term that has at least one package behind it.
 *
 * `hide_empty` is what keeps the filter honest: offering a destination with no
 * packages just returns an empty grid and reads as a broken site.
 */
function ge_term_options( $taxonomy, $selected = '' ) {
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
	) );

	if ( is_wp_error( $terms ) ) {
		return;
	}

	// The value is the term NAME, not the slug, because the same string has to
	// match the card's data-destination attribute that filter.js compares
	// against. One vocabulary for both halves of the filter.
	foreach ( $terms as $term ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $term->name ),
			selected( $selected, $term->name, false ),
			esc_html( $term->name )
		);
	}
}

/**
 * Breadcrumb trail.
 *
 * `$crumbs` is an ordered array of [ label => url ]; the last entry is
 * rendered as the current page and carries no link.
 */
function ge_breadcrumb( array $crumbs ) {
	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'gangotri-expeditions' ) . '"><ol>';

	$last = array_key_last( $crumbs );

	foreach ( $crumbs as $label => $url ) {
		if ( $label === $last ) {
			printf( '<li aria-current="page">%s</li>', esc_html( $label ) );
		} else {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
		}
	}

	echo '</ol></nav>';
}

/**
 * The dark banner every inner page opens with.
 *
 * Kept as a function rather than a template part because it takes three
 * arguments and is called from a dozen templates.
 */
function ge_page_head( $title, $intro = '', array $crumbs = array() ) {
	echo '<section class="page-head"><div class="container-page">';

	if ( $crumbs ) {
		ge_breadcrumb( $crumbs );
	}

	printf( '<h1 class="text-3xl lg:text-5xl mt-3 text-white">%s</h1>', esc_html( $title ) );

	if ( $intro ) {
		printf(
			'<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed">%s</p>',
			esc_html( $intro )
		);
	}

	echo '</div></section>';
}

/**
 * A responsive <img> for a package, or the placeholder tint when the client
 * has not set a featured image yet.
 *
 * WordPress builds the srcset from the sizes registered in inc/setup.php, so
 * a phone pulls the 800px crop rather than the original upload.
 */
function ge_thumbnail( $size = 'ge-card', $attr = array() ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, $attr );
		return;
	}

	echo '<span class="block w-full h-full bg-brand-100" aria-hidden="true"></span>';
}

/**
 * Reading time for a blog post, rounded up.
 *
 * 200 words a minute is the usual English reading estimate; it is a hint on a
 * card, not a promise, so it is not worth tuning further.
 */
function ge_reading_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ?: get_the_ID() ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Excerpt that prefers what the editor actually wrote.
 *
 * get_the_excerpt() invents one from the content when the field is empty,
 * which for a package means the first sentence of the overview - fine as a
 * fallback, wrong if the client took the trouble to write a summary.
 */
function ge_summary( $length = 28 ) {
	$post = get_post();

	if ( $post && '' !== trim( $post->post_excerpt ) ) {
		return $post->post_excerpt;
	}

	return wp_trim_words( wp_strip_all_tags( get_the_content() ), $length );
}
