<?php
/**
 * A standard page - About, Services, FAQ, and the legal pages.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="flex-1">

		<section class="page-head">
			<div class="container-page">
				<?php gangotri_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>
				<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="section">
			<div class="container-page">
				<?php // Legal pages read better narrow; everything else keeps the full grid. ?>
				<div class="<?php echo esc_attr( is_page( array( 'privacy', 'terms', 'cancellation', 'credits' ) ) ? 'max-w-3xl prose-legal' : '' ); ?>">
					<?php the_content(); ?>
				</div>
			</div>
		</section>

	</main>
	<?php
endwhile;

get_footer();
