<?php
/**
 * Search form.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$id = 'search-' . wp_unique_id();
?>
<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex gap-2">
	<label class="sr-only" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Search', 'gangotri' ); ?></label>
	<input class="field" type="search" id="<?php echo esc_attr( $id ); ?>" name="s"
	       value="<?php echo esc_attr( get_search_query() ); ?>"
	       placeholder="<?php esc_attr_e( 'Search packages...', 'gangotri' ); ?>">
	<button class="btn btn-primary" type="submit">
		<?php echo gangotri_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="sr-only"><?php esc_html_e( 'Search', 'gangotri' ); ?></span>
	</button>
</form>
