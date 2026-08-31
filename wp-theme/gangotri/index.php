<?php
/**
 * Fallback template.
 *
 * WordPress falls back here for anything without a more specific template. The
 * site has no blog, so in practice this is only reached by a stray archive URL.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => get_the_archive_title() ? wp_strip_all_tags( get_the_archive_title() ) : __( 'Posts', 'gangotri' ) ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white">
				<?php echo esc_html( get_the_archive_title() ? wp_strip_all_tags( get_the_archive_title() ) : get_bloginfo( 'name' ) ); ?>
			</h1>
		</div>
	</section>

	<section class="section">
		<div class="container-page max-w-3xl">
			<?php if ( have_posts() ) : ?>
				<ul class="space-y-6">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<li class="card p-6">
							<h2 class="text-lg"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="mt-2 text-sm text-ink/70 leading-relaxed"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</li>
					<?php endwhile; ?>
				</ul>

				<?php the_posts_pagination( array( 'mid_size' => 1, 'class' => 'pagination mt-10' ) ); ?>
			<?php else : ?>
				<p class="text-ink/70"><?php esc_html_e( 'Nothing here yet.', 'gangotri' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
