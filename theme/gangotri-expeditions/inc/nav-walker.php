<?php
/**
 * Menu rendering.
 *
 * WordPress wraps every menu item in an <li> carrying a dozen classes. The
 * layouts here want bare <a> elements with one Tailwind class string, so this
 * walker emits exactly that and drops the list markup - the nav element is the
 * landmark, and the items are links, so nothing is lost to a screen reader.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

class GE_Nav_Walker extends Walker_Nav_Menu {

	/** Tailwind classes applied to every link this walker renders. */
	private $link_class;

	public function __construct( $link_class = '' ) {
		$this->link_class = $link_class;
	}

	/** No <ul> wrapper - items_wrap is '%3$s' and the items are bare links. */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = $this->link_class;

		// The current page gets marked for both CSS and assistive tech.
		$current = in_array( 'current-menu-item', (array) $item->classes, true )
			|| in_array( 'current-menu-ancestor', (array) $item->classes, true );

		// `before` / `after` let a caller wrap each item - the footer's Explore
		// column is a <ul>, so its links have to sit inside <li> to be valid.
		$before = $args->before ?? '';
		$after  = $args->after ?? '';

		$output .= $before . sprintf(
			'<a class="%s" href="%s"%s%s>%s</a>',
			esc_attr( trim( $classes . ( $current ? ' is-current' : '' ) ) ),
			esc_url( $item->url ),
			$current ? ' aria-current="page"' : '',
			$item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '',
			esc_html( $item->title )
		) . $after;
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * The pages the site has before anyone builds a menu in the admin.
 *
 * A theme that renders nothing until a menu is assigned looks broken on the
 * first load after activation, which is exactly when someone is judging it.
 */
function ge_default_nav_items() {
	return array(
		__( 'Home', 'gangotri-expeditions' )     => home_url( '/' ),
		__( 'Yatras', 'gangotri-expeditions' )   => ge_yatras_url(),
		__( 'About Us', 'gangotri-expeditions' ) => home_url( '/about/' ),
		__( 'Services', 'gangotri-expeditions' ) => home_url( '/services/' ),
		__( 'Gallery', 'gangotri-expeditions' )  => home_url( '/gallery/' ),
		__( 'Contact', 'gangotri-expeditions' )  => home_url( '/contact/' ),
	);
}

function ge_primary_menu_fallback() {
	echo '<nav class="hidden lg:flex items-center gap-8" aria-label="' . esc_attr__( 'Primary', 'gangotri-expeditions' ) . '">';

	foreach ( ge_default_nav_items() as $label => $url ) {
		printf( '<a class="nav-link" href="%s">%s</a>', esc_url( $url ), esc_html( $label ) );
	}

	echo '</nav>';
}

function ge_mobile_menu_fallback() {
	echo '<nav class="flex flex-col gap-1 pt-4" aria-label="' . esc_attr__( 'Mobile', 'gangotri-expeditions' ) . '">';

	foreach ( ge_default_nav_items() as $label => $url ) {
		printf(
			'<a class="py-3 text-lg font-heading font-medium text-brand-800 border-b border-brand-100" href="%s">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	echo '</nav>';
}
