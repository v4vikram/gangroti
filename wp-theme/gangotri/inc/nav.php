<?php
/**
 * Navigation output.
 *
 * The design puts links directly in a flex row with no <ul>/<li> wrapper, so
 * the default walker's markup would need overriding in CSS. This produces the
 * markup the stylesheet already expects instead.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emits bare <a> elements rather than a nested list.
 */
class Gangotri_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Classes applied to each link.
	 *
	 * @var string
	 */
	private string $link_class;

	/**
	 * @param string $link_class Classes for each link.
	 */
	public function __construct( string $link_class = 'nav-link' ) {
		$this->link_class = $link_class;
	}

	/**
	 * No <ul> - the parent element is already the flex container.
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * No <li> either.
	 *
	 * @param string  $output Passed by reference.
	 * @param WP_Post $item   Menu item.
	 * @param int     $depth  Depth.
	 * @param array   $args   Args.
	 * @param int     $id     Menu id.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		// WordPress marks the current item on the <li>; with no <li> it moves
		// to the link, which is also where aria-current belongs.
		$current = in_array( 'current-menu-item', (array) $item->classes, true )
			|| in_array( 'current_page_parent', (array) $item->classes, true );

		$output .= sprintf(
			'<a class="%s" href="%s"%s>%s</a>',
			esc_attr( $this->link_class ),
			esc_url( $item->url ),
			$current ? ' aria-current="page"' : '',
			esc_html( $item->title )
		);
	}

	/**
	 * @param string  $output Passed by reference.
	 * @param WP_Post $item   Menu item.
	 * @param int     $depth  Depth.
	 * @param array   $args   Args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * The pages a menu would contain, used until one is assigned in Appearance →
 * Menus. Without a fallback a fresh install shows an empty header, which reads
 * as a broken theme rather than an unconfigured one.
 *
 * @return array<string,string> URL => label.
 */
function gangotri_default_links(): array {
	$links = array( home_url( '/' ) => __( 'Home', 'gangotri' ) );

	$archive = gangotri_packages_url();
	if ( $archive ) {
		$links[ $archive ] = __( 'Packages', 'gangotri' );
	}

	foreach ( array( 'about' => 'About Us', 'services' => 'Services', 'gallery' => 'Gallery', 'contact' => 'Contact' ) as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$links[ (string) get_permalink( $page ) ] = $label;
		}
	}

	return $links;
}

/**
 * Desktop fallback menu.
 */
function gangotri_default_menu(): void {
	foreach ( gangotri_default_links() as $url => $label ) {
		printf( '<a class="nav-link" href="%s">%s</a>', esc_url( $url ), esc_html( $label ) );
	}
}

/**
 * Mobile fallback menu.
 */
function gangotri_default_menu_mobile(): void {
	foreach ( gangotri_default_links() as $url => $label ) {
		printf(
			'<a class="py-3 text-lg font-heading font-medium text-brand-800 border-b border-brand-100" href="%s">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}

/**
 * Footer "Explore" links, used until a footer menu is assigned.
 */
function gangotri_footer_fallback(): void {
	$links = gangotri_default_links();
	unset( $links[ home_url( '/' ) ] ); // Home is the logo, not a footer link.

	$faq = get_page_by_path( 'faq' );
	if ( $faq ) {
		$links[ (string) get_permalink( $faq ) ] = __( 'FAQ', 'gangotri' );
	}

	foreach ( $links as $url => $label ) {
		printf(
			'<li><a class="hover:text-gold-400 transition-colors" href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}
