<?php
/**
 * Not found.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();
?>

<main id="main" class="flex-1 grid place-items-center">
	<div class="container-page max-w-xl text-center py-20">
		<span class="trust-icon mx-auto text-3xl">
			<?php echo gangotri_icon( 'compass' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>

		<p class="eyebrow mt-6 justify-center"><?php esc_html_e( 'Error 404', 'gangotri' ); ?></p>
		<h1 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'This trail does not go anywhere', 'gangotri' ); ?></h1>
		<p class="mt-4 text-ink/70 leading-relaxed">
			<?php esc_html_e( 'The page you were looking for has moved or never existed. Here are the ways back.', 'gangotri' ); ?>
		</p>

		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo gangotri_icon( 'arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Back to Home', 'gangotri' ); ?>
			</a>
			<a class="btn btn-outline btn-lg" href="<?php echo esc_url( gangotri_packages_url() ); ?>">
				<?php esc_html_e( 'Browse Packages', 'gangotri' ); ?>
			</a>
		</div>

		<div class="mt-10 max-w-sm mx-auto"><?php get_search_form(); ?></div>
	</div>
</main>

<?php
get_footer();
