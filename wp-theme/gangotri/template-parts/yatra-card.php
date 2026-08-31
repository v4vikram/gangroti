<?php
/**
 * One package card.
 *
 * Converted from src/partials/yatra-card.html. The data-* attributes are what
 * the client-side filter reads on the archive.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$destinations = wp_get_post_terms( get_the_ID(), 'destination', array( 'fields' => 'names' ) );
$types        = wp_get_post_terms( get_the_ID(), 'trip_type', array( 'fields' => 'names' ) );

$destination = is_array( $destinations ) && $destinations ? $destinations[0] : '';
$type        = is_array( $types ) && $types ? $types[0] : '';

$days   = (int) get_post_meta( get_the_ID(), 'ge_days', true );
$nights = (int) get_post_meta( get_the_ID(), 'ge_nights', true );
$price  = (int) get_post_meta( get_the_ID(), 'ge_price', true );
?>

<article class="card card-hover flex flex-col group" data-yatra
         data-type="<?php echo esc_attr( $type ); ?>"
         data-destination="<?php echo esc_attr( $destination ); ?>"
         data-days="<?php echo esc_attr( (string) $days ); ?>"
         data-price="<?php echo esc_attr( (string) $price ); ?>"
         data-title="<?php the_title_attribute(); ?>">

	<div class="media-4-3 relative">
		<?php
		echo gangotri_package_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- returns escaped <img> markup.
			'gangotri-card',
			array( 'class' => 'transition-transform duration-500 group-hover:scale-105' )
		);
		?>

		<?php if ( $type ) : ?>
			<span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur-sm shadow-sm">
				<?php echo gangotri_icon( 'route' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( $type ); ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="p-5 flex flex-col flex-1">
		<h3 class="text-lg">
			<a href="<?php the_permalink(); ?>" class="after:absolute after:inset-0 relative">
				<?php the_title(); ?>
			</a>
		</h3>

		<p class="mt-2 text-sm leading-relaxed text-ink/70 line-clamp-3">
			<?php echo esc_html( get_the_excerpt() ); ?>
		</p>

		<dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-ink/75">
			<?php if ( $days ) : ?>
				<div class="flex items-center gap-1.5">
					<?php echo gangotri_icon( 'clock', 'icon text-brand-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<dt class="sr-only"><?php esc_html_e( 'Duration', 'gangotri' ); ?></dt>
					<dd><?php echo esc_html( sprintf( '%dD / %dN', $days, $nights ) ); ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( gangotri_meta( 'difficulty' ) ) : ?>
				<div class="flex items-center gap-1.5">
					<?php echo gangotri_icon( 'trending-up', 'icon text-brand-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<dt class="sr-only"><?php esc_html_e( 'Difficulty', 'gangotri' ); ?></dt>
					<dd><?php echo gangotri_meta( 'difficulty' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( gangotri_meta( 'altitude' ) ) : ?>
				<div class="flex items-center gap-1.5">
					<?php echo gangotri_icon( 'mountain-snow', 'icon text-brand-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<dt class="sr-only"><?php esc_html_e( 'Maximum altitude', 'gangotri' ); ?></dt>
					<dd><?php echo gangotri_meta( 'altitude' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
				</div>
			<?php endif; ?>

			<?php if ( gangotri_meta( 'season' ) ) : ?>
				<div class="flex items-center gap-1.5">
					<?php echo gangotri_icon( 'calendar', 'icon text-brand-600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<dt class="sr-only"><?php esc_html_e( 'Best season', 'gangotri' ); ?></dt>
					<dd><?php echo gangotri_meta( 'season' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
				</div>
			<?php endif; ?>
		</dl>

		<div class="mt-5 pt-4 border-t border-brand-100 flex items-end justify-between gap-3">
			<?php if ( $price ) : ?>
				<p class="leading-tight">
					<span class="block text-[0.6875rem] uppercase tracking-wider text-ink/50"><?php esc_html_e( 'From', 'gangotri' ); ?></span>
					<span class="font-heading font-bold text-xl text-brand-700">
						<?php echo gangotri_icon( 'indian-rupee', 'icon text-[0.7em]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( gangotri_price() ); ?>
					</span>
					<span class="text-[0.6875rem] text-ink/50"><?php esc_html_e( '/ person', 'gangotri' ); ?></span>
				</p>
			<?php else : ?>
				<p class="text-sm text-ink/60"><?php esc_html_e( 'Price on request', 'gangotri' ); ?></p>
			<?php endif; ?>

			<span class="btn btn-outline btn-sm group-hover:border-brand-600 group-hover:bg-brand-50">
				<?php esc_html_e( 'View Details', 'gangotri' ); ?>
				<?php echo gangotri_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
	</div>
</article>
