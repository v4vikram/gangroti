<?php
/**
 * Not found.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

ge_page_head(
	__( 'That page has wandered off the trail', 'gangotri-expeditions' ),
	__( 'The link may be old, or the page may have moved. Here is the way back.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' )      => home_url( '/' ),
		__( 'Not found', 'gangotri-expeditions' ) => '',
	)
);
?>

<section class="section">
	<div class="container-page max-w-3xl text-center">

		<span class="trust-icon mx-auto"><?php ge_icon( 'compass' ); ?></span>

		<p class="mt-6 text-ink/70 leading-relaxed">
			<?php esc_html_e( 'Try searching, or head straight to the packages - that is what most people are looking for.', 'gangotri-expeditions' ); ?>
		</p>

		<form role="search" method="get" class="mt-8 flex flex-wrap justify-center gap-3"
		      action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="sr-only" for="s-404"><?php esc_html_e( 'Search', 'gangotri-expeditions' ); ?></label>
			<input class="field max-w-xs" type="search" id="s-404" name="s"
			       placeholder="<?php esc_attr_e( 'Kedarnath, trek, 3 days...', 'gangotri-expeditions' ); ?>">
			<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Search', 'gangotri-expeditions' ); ?></button>
		</form>

		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a class="btn btn-gold btn-lg" href="<?php echo esc_url( ge_yatras_url() ); ?>">
				<?php ge_icon( 'route' ); ?> <?php esc_html_e( 'All Packages', 'gangotri-expeditions' ); ?>
			</a>
			<a class="btn btn-outline btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php ge_icon( 'arrow-left' ); ?> <?php esc_html_e( 'Home', 'gangotri-expeditions' ); ?>
			</a>
		</div>

	</div>
</section>

<?php
get_footer();
