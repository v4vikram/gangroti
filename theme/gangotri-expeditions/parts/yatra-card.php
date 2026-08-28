<?php
/**
 * One package card.
 *
 * Used by the homepage strip, the archive and any future related-packages
 * block, so this is the only place card markup exists.
 *
 * The data-* attributes feed the client-side filter on the archive. They stay
 * even when the filter runs server-side, because the JS narrows the current
 * page without a round trip and falls back to the form submit without them.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

$ge_days       = ge_field( 'days' );
$ge_nights     = ge_field( 'nights' );
$ge_price      = ge_field( 'price' );
$ge_difficulty = ge_field( 'difficulty' );
$ge_altitude   = ge_field( 'altitude' );
$ge_season     = ge_field( 'season' );
$ge_type       = ge_term_name( 'yatra_type' );
$ge_dest       = ge_term_name( 'yatra_destination' );
?>

<article class="card card-hover flex flex-col group" data-yatra
         data-type="<?php echo esc_attr( $ge_type ); ?>"
         data-destination="<?php echo esc_attr( $ge_dest ); ?>"
         data-days="<?php echo esc_attr( $ge_days ); ?>"
         data-price="<?php echo esc_attr( $ge_price ); ?>"
         data-title="<?php the_title_attribute(); ?>">

	<div class="media-4-3 relative">
		<?php
		ge_thumbnail( 'ge-card', array(
			'class'    => 'transition-transform duration-500 group-hover:scale-105',
			'loading'  => 'lazy',
			'decoding' => 'async',
		) );
		?>

		<?php if ( $ge_type ) : ?>
			<span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur-sm shadow-sm">
				<?php ge_icon( 'route' ); ?>
				<?php echo esc_html( $ge_type ); ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="p-5 flex flex-col flex-1">
		<h3 class="text-lg">
			<a href="<?php the_permalink(); ?>" class="after:absolute after:inset-0 relative">
				<?php the_title(); ?>
			</a>
		</h3>

		<p class="mt-2 text-sm leading-relaxed text-ink/70 line-clamp-3"><?php echo esc_html( ge_summary() ); ?></p>

		<dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-ink/75">
			<?php if ( $ge_days ) : ?>
				<div class="flex items-center gap-1.5">
					<?php ge_icon( 'clock', 'text-brand-600' ); ?>
					<dt class="sr-only"><?php esc_html_e( 'Duration', 'gangotri-expeditions' ); ?></dt>
					<dd><?php echo esc_html( sprintf( '%dD / %dN', (int) $ge_days, (int) $ge_nights ) ); ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( $ge_difficulty ) : ?>
				<div class="flex items-center gap-1.5">
					<?php ge_icon( 'trending-up', 'text-brand-600' ); ?>
					<dt class="sr-only"><?php esc_html_e( 'Difficulty', 'gangotri-expeditions' ); ?></dt>
					<dd><?php echo esc_html( $ge_difficulty ); ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( $ge_altitude ) : ?>
				<div class="flex items-center gap-1.5">
					<?php ge_icon( 'mountain-snow', 'text-brand-600' ); ?>
					<dt class="sr-only"><?php esc_html_e( 'Maximum altitude', 'gangotri-expeditions' ); ?></dt>
					<dd><?php echo esc_html( $ge_altitude ); ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( $ge_season ) : ?>
				<div class="flex items-center gap-1.5">
					<?php ge_icon( 'calendar', 'text-brand-600' ); ?>
					<dt class="sr-only"><?php esc_html_e( 'Best season', 'gangotri-expeditions' ); ?></dt>
					<dd><?php echo esc_html( $ge_season ); ?></dd>
				</div>
			<?php endif; ?>
		</dl>

		<div class="mt-5 pt-4 border-t border-brand-100 flex items-end justify-between gap-3">
			<p class="leading-tight">
				<?php if ( $ge_price ) : ?>
					<span class="block text-[0.6875rem] uppercase tracking-wider text-ink/50"><?php esc_html_e( 'From', 'gangotri-expeditions' ); ?></span>
					<span class="font-heading font-bold text-xl text-brand-700">
						<?php ge_icon( 'indian-rupee', 'text-[0.7em]' ); ?><?php echo esc_html( ge_format_price( $ge_price ) ); ?>
					</span>
					<span class="text-[0.6875rem] text-ink/50"><?php esc_html_e( '/ person', 'gangotri-expeditions' ); ?></span>
				<?php else : ?>
					<span class="font-heading font-semibold text-brand-700"><?php esc_html_e( 'On request', 'gangotri-expeditions' ); ?></span>
				<?php endif; ?>
			</p>

			<span class="btn btn-outline btn-sm group-hover:border-brand-600 group-hover:bg-brand-50">
				<?php esc_html_e( 'View Details', 'gangotri-expeditions' ); ?>
				<?php ge_icon( 'arrow-right' ); ?>
			</span>
		</div>
	</div>
</article>
