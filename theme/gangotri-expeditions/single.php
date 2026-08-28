<?php
/**
 * A blog post.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	ge_page_head(
		get_the_title(),
		'',
		array(
			__( 'Home', 'gangotri-expeditions' )    => home_url( '/' ),
			__( 'Journal', 'gangotri-expeditions' ) => get_permalink( (int) get_option( 'page_for_posts' ) ) ?: home_url( '/' ),
			get_the_title()                         => '',
		)
	);
	?>

	<article class="section">
		<div class="container-page max-w-3xl">

			<p class="text-sm text-ink/55">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				&middot;
				<?php
				printf(
					/* translators: %d: minutes. */
					esc_html__( '%d min read', 'gangotri-expeditions' ),
					(int) ge_reading_time()
				);
				?>
			</p>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="media-16-9 rounded-card mt-6">
					<?php the_post_thumbnail( 'ge-hero', array( 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="prose-ge mt-8">
				<?php the_content(); ?>
			</div>

			<?php
			wp_link_pages( array(
				'before' => '<nav class="mt-8 flex gap-2 text-sm" aria-label="' . esc_attr__( 'Page', 'gangotri-expeditions' ) . '">',
				'after'  => '</nav>',
			) );
			?>

			<?php $ge_tags = get_the_tag_list( '', '' ); ?>
			<?php if ( $ge_tags ) : ?>
				<div class="mt-8 pt-6 border-t border-brand-100 flex flex-wrap gap-2 text-sm">
					<?php echo wp_kses_post( $ge_tags ); ?>
				</div>
			<?php endif; ?>

			<nav class="mt-10 pt-6 border-t border-brand-100 flex justify-between gap-4 text-sm"
			     aria-label="<?php esc_attr_e( 'More posts', 'gangotri-expeditions' ); ?>">
				<?php
				previous_post_link( '<span>%link</span>', ge_get_icon( 'chevron-left' ) . '%title' );
				next_post_link( '<span class="text-right">%link</span>', '%title' . ge_get_icon( 'chevron-right' ) );
				?>
			</nav>

			<?php
			if ( comments_open() || get_comments_number() ) {
				echo '<div class="mt-12">';
				comments_template();
				echo '</div>';
			}
			?>

		</div>
	</article>

	<?php
endwhile;

get_template_part( 'parts/cta-band', null, array(
	'title' => __( 'Planning a trip after reading this?', 'gangotri-expeditions' ),
	'text'  => __( 'Tell us your dates and who is travelling. We will suggest a route that suits your group, honestly.', 'gangotri-expeditions' ),
) );

get_footer();
