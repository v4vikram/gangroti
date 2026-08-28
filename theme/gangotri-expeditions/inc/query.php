<?php
/**
 * Server-side filtering and sorting for the package archive.
 *
 * The static build did this in JavaScript alone, which was fine for a page
 * that shipped every card in the HTML. On WordPress the archive is paginated,
 * so a filter that only hides rendered cards would silently ignore everything
 * on page two. Reading the same query strings here makes the filter correct,
 * linkable and crawlable; the JS stays as the instant-feedback layer over
 * whatever this returns.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

/** The filter's query strings, registered so get_query_var() can read them. */
function ge_query_vars( $vars ) {
	$vars[] = 'destination';
	$vars[] = 'type';
	$vars[] = 'duration';
	$vars[] = 'sort';

	return $vars;
}
add_filter( 'query_vars', 'ge_query_vars' );

/**
 * One filter value, cleaned.
 *
 * Both this file and the archive template read through here, so the template
 * never touches $_GET and the sanitising happens in exactly one place.
 */
function ge_filter_value( $key ) {
	$raw = get_query_var( $key, '' );

	return is_string( $raw ) ? sanitize_text_field( $raw ) : '';
}

/**
 * Applies the filters to the main archive query.
 *
 * Only ever touches the front-end main query for this one archive - a
 * pre_get_posts hook that forgets those guards is the classic way to break
 * the admin list table and every secondary loop on the site.
 */
function ge_filter_yatra_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'yatra' ) ) {
		return;
	}

	$destination = ge_filter_value( 'destination' );
	$type        = ge_filter_value( 'type' );
	$duration    = ge_filter_value( 'duration' );
	$sort        = ge_filter_value( 'sort' );

	$tax_query = array();

	if ( $destination ) {
		$tax_query[] = array(
			'taxonomy' => 'yatra_destination',
			'field'    => 'name',
			'terms'    => $destination,
		);
	}

	if ( $type ) {
		$tax_query[] = array(
			'taxonomy' => 'yatra_type',
			'field'    => 'name',
			'terms'    => $type,
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	// "1-3", "4-6", "7-99" - a closed range in both directions, so the
	// buckets cannot overlap.
	if ( preg_match( '/^(\d{1,2})-(\d{1,2})$/', $duration, $bounds ) ) {
		$query->set( 'meta_query', array(
			array(
				'key'     => ge_meta_key( 'days' ),
				'value'   => array( (int) $bounds[1], (int) $bounds[2] ),
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			),
		) );
	}

	switch ( $sort ) {
		case 'price-asc':
		case 'price-desc':
			$query->set( 'meta_key', ge_meta_key( 'price' ) );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'price-asc' === $sort ? 'ASC' : 'DESC' );
			break;

		case 'duration-asc':
		case 'duration-desc':
			$query->set( 'meta_key', ge_meta_key( 'days' ) );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'duration-asc' === $sort ? 'ASC' : 'DESC' );
			break;

		default:
			// "Featured" - the flagged packages first, then the order the
			// client dragged them into, then alphabetical as a tiebreak.
			$query->set( 'meta_key', ge_meta_key( 'featured' ) );
			$query->set( 'orderby', array(
				'meta_value' => 'DESC',
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			) );
	}

	$query->set( 'posts_per_page', 12 );
}
add_action( 'pre_get_posts', 'ge_filter_yatra_archive' );

/**
 * Sorting by "featured" must not hide packages that have never been flagged.
 *
 * A meta_key on the query is an INNER JOIN, so posts with no row for that key
 * drop out entirely. Switching the relation to a LEFT JOIN keeps them, sorted
 * after the flagged ones.
 */
function ge_featured_sort_keeps_unflagged( $clauses, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'yatra' ) ) {
		return $clauses;
	}

	if ( ge_filter_value( 'sort' ) ) {
		return $clauses;
	}

	$clauses['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $clauses['join'] );

	return $clauses;
}
add_filter( 'posts_clauses', 'ge_featured_sort_keeps_unflagged', 10, 2 );
