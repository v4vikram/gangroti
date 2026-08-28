<?php
/**
 * One blog post card.
 *
 * Shares the card shell with the packages so the two grids sit together
 * without a second set of styles.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class( 'card card-hover flex flex-col group' ); ?>>

	<div class="media-4-3 relative">
		<?php
		ge_thumbnail( 'ge-card', array(
			'class'    => 'transition-transform duration-500 group-hover:scale-105',
			'loading'  => 'lazy',
			'decoding' => 'async',
		) );
		?>

		<?php
		$ge_category = get_the_category();

		if ( $ge_category ) :
			?>
			<span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur-sm shadow-sm">
				<?php echo esc_html( $ge_category[0]->name ); ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="p-5 flex flex-col flex-1">
		<p class="text-xs text-ink/55">
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

		<h3 class="text-lg mt-2">
			<a href="<?php the_permalink(); ?>" class="after:absolute after:inset-0 relative">
				<?php the_title(); ?>
			</a>
		</h3>

		<p class="mt-2 text-sm leading-relaxed text-ink/70 line-clamp-3"><?php echo esc_html( ge_summary() ); ?></p>

		<span class="btn btn-outline btn-sm mt-5 self-start group-hover:border-brand-600 group-hover:bg-brand-50">
			<?php esc_html_e( 'Read more', 'gangotri-expeditions' ); ?>
			<?php ge_icon( 'arrow-right' ); ?>
		</span>
	</div>
</article>
