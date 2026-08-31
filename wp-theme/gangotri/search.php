<?php
/**
 * Search results.
 *
 * Packages are the only thing worth finding on this site, so results are shown
 * as package cards where they are packages, and as plain links otherwise.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => __( 'Search', 'gangotri' ) ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white">
				<?php
				printf(
					/* translators: %s: search term. */
					esc_html__( 'Results for %s', 'gangotri' ),
					'&ldquo;' . esc_html( get_search_query() ) . '&rdquo;'
				);
				?>
			</h1>
		</div>
	</section>

	<section class="section">
		<div class="container-page">
			<div class="max-w-md mb-10"><?php get_search_form(); ?></div>

			<?php if ( have_posts() ) : ?>
				<div class="card-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
					<?php
					while ( have_posts() ) :
						the_post();

						if ( 'yatra' === get_post_type() ) {
							get_template_part( 'template-parts/yatra-card' );
							continue;
						}
						?>
						<article class="card p-6">
							<h2 class="text-lg"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="mt-2 text-sm text-ink/70 leading-relaxed"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</article>
						<?php
					endwhile;
					?>
				</div>

				<?php the_posts_pagination( array( 'mid_size' => 1, 'class' => 'pagination mt-10' ) ); ?>
			<?php else : ?>
				<div class="empty-state">
					<span class="trust-icon mx-auto"><?php echo gangotri_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'Nothing matched that', 'gangotri' ); ?></p>
					<p class="mt-2 text-sm text-ink/65"><?php esc_html_e( 'Try a destination name, or just tell us what you are looking for.', 'gangotri' ); ?></p>
					<button type="button" class="btn btn-primary mt-6" data-enquiry-open>
						<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Ask us directly', 'gangotri' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
