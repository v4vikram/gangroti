<?php
/**
 * The `yatra` post type and its taxonomies.
 *
 * This is what src/data/yatras.json was in the static build - one entry per
 * package, with the same field names, so the templates carried over unchanged.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		register_post_type(
			'yatra',
			array(
				'labels'        => array(
					'name'               => __( 'Packages', 'gangotri' ),
					'singular_name'      => __( 'Package', 'gangotri' ),
					'add_new'            => __( 'Add Package', 'gangotri' ),
					'add_new_item'       => __( 'Add New Package', 'gangotri' ),
					'edit_item'          => __( 'Edit Package', 'gangotri' ),
					'new_item'           => __( 'New Package', 'gangotri' ),
					'view_item'          => __( 'View Package', 'gangotri' ),
					'search_items'       => __( 'Search Packages', 'gangotri' ),
					'not_found'          => __( 'No packages yet', 'gangotri' ),
					'not_found_in_trash' => __( 'No packages in the trash', 'gangotri' ),
					'menu_name'          => __( 'Packages', 'gangotri' ),
				),
				'public'        => true,
				'has_archive'   => 'yatras',
				'menu_icon'     => 'dashicons-palmtree',
				'menu_position' => 20,
				'rewrite'       => array( 'slug' => 'yatras', 'with_front' => false ),
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
				// Off on purpose: the fields below are meta boxes, and the block
				// editor hides classic meta boxes behind a panel.
				'show_in_rest'  => false,
			)
		);

		register_taxonomy(
			'destination',
			'yatra',
			array(
				'labels'            => array(
					'name'          => __( 'Destinations', 'gangotri' ),
					'singular_name' => __( 'Destination', 'gangotri' ),
					'menu_name'     => __( 'Destinations', 'gangotri' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'destinations', 'with_front' => false ),
			)
		);

		register_taxonomy(
			'trip_type',
			'yatra',
			array(
				'labels'            => array(
					'name'          => __( 'Trip Types', 'gangotri' ),
					'singular_name' => __( 'Trip Type', 'gangotri' ),
					'menu_name'     => __( 'Trip Types', 'gangotri' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'trip-type', 'with_front' => false ),
			)
		);
	}
);

/**
 * Flushes rewrite rules once after the theme is switched on.
 *
 * Without this the new /yatras/ URLs 404 until someone thinks to re-save the
 * permalinks page, which is a support call every single time.
 */
add_action(
	'after_switch_theme',
	static function (): void {
		flush_rewrite_rules();
	}
);

/**
 * Archive ordering: cheapest first is the wrong default for a travel site -
 * it buries the flagship trips. Menu order, then title.
 */
add_action(
	'pre_get_posts',
	static function ( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( 'yatra' ) || $query->is_tax( array( 'destination', 'trip_type' ) ) ) {
			$query->set( 'posts_per_page', 12 );
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);

/**
 * Admin columns worth having: price and duration are what the client will scan
 * the list for, and neither is visible from the title.
 */
add_filter(
	'manage_yatra_posts_columns',
	static function ( array $columns ): array {
		$date = $columns['date'] ?? null;
		unset( $columns['date'] );

		$columns['ge_duration'] = __( 'Duration', 'gangotri' );
		$columns['ge_price']    = __( 'From', 'gangotri' );

		if ( $date ) {
			$columns['date'] = $date;
		}
		return $columns;
	}
);

add_action(
	'manage_yatra_posts_custom_column',
	static function ( string $column, int $post_id ): void {
		if ( 'ge_duration' === $column ) {
			$days   = (int) get_post_meta( $post_id, 'ge_days', true );
			$nights = (int) get_post_meta( $post_id, 'ge_nights', true );
			echo $days ? esc_html( sprintf( '%dD / %dN', $days, $nights ) ) : '&mdash;';
		}

		if ( 'ge_price' === $column ) {
			$price = (int) get_post_meta( $post_id, 'ge_price', true );
			echo $price ? esc_html( '₹' . number_format_i18n( $price ) ) : '&mdash;';
		}
	},
	10,
	2
);
