<?php
/**
 * A standard page.
 *
 * The bespoke layouts (about, services, gallery, contact, FAQ) each have their
 * own template in page-templates/; this renders whatever the editor wrote for
 * everything else, including the legal pages.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	ge_page_head(
		get_the_title(),
		get_the_excerpt(),
		array(
			__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
			get_the_title()                      => '',
		)
	);
	?>

	<section class="section">
		<div class="container-page max-w-3xl">
			<div class="prose-ge">
				<?php the_content(); ?>
			</div>

			<?php
			wp_link_pages( array(
				'before' => '<nav class="mt-8 flex gap-2 text-sm" aria-label="' . esc_attr__( 'Page', 'gangotri-expeditions' ) . '">',
				'after'  => '</nav>',
			) );
			?>
		</div>
	</section>

	<?php
endwhile;

get_template_part( 'parts/cta-band' );
get_footer();
