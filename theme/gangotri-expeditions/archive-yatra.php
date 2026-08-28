<?php
/**
 * The package listing.
 *
 * Filtering runs twice on purpose. inc/query.php reads the same query strings
 * server-side, so the page works without JavaScript, is linkable and is
 * crawlable; src/js/filter.js then narrows the rendered cards without a round
 * trip. Both read the same data-* attributes on the card.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

ge_page_head(
	__( 'Our Packages', 'gangotri-expeditions' ),
	__( 'Every journey below is a fixed departure with a published itinerary, a real altitude figure and an honest difficulty rating. Filter to what suits you, then ask us anything.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' )     => home_url( '/' ),
		__( 'Packages', 'gangotri-expeditions' ) => '',
	)
);
?>

<section class="section">
	<div class="container-page">

		<form class="filter-bar" data-filter method="get" role="search"
		      action="<?php echo esc_url( ge_yatras_url() ); ?>"
		      aria-label="<?php esc_attr_e( 'Filter yatras', 'gangotri-expeditions' ); ?>">

			<div>
				<label class="label" for="f-destination"><?php esc_html_e( 'Destination', 'gangotri-expeditions' ); ?></label>
				<select class="field" id="f-destination" name="destination">
					<option value=""><?php esc_html_e( 'All destinations', 'gangotri-expeditions' ); ?></option>
					<?php ge_term_options( 'yatra_destination', ge_filter_value( 'destination' ) ); ?>
				</select>
			</div>

			<div>
				<label class="label" for="f-type"><?php esc_html_e( 'Type', 'gangotri-expeditions' ); ?></label>
				<select class="field" id="f-type" name="type">
					<option value=""><?php esc_html_e( 'All types', 'gangotri-expeditions' ); ?></option>
					<?php ge_term_options( 'yatra_type', ge_filter_value( 'type' ) ); ?>
				</select>
			</div>

			<div>
				<label class="label" for="f-duration"><?php esc_html_e( 'Duration', 'gangotri-expeditions' ); ?></label>
				<?php $ge_duration = ge_filter_value( 'duration' ); ?>
				<select class="field" id="f-duration" name="duration">
					<option value=""><?php esc_html_e( 'Any length', 'gangotri-expeditions' ); ?></option>
					<option value="1-3" <?php selected( $ge_duration, '1-3' ); ?>><?php esc_html_e( '1 - 3 days', 'gangotri-expeditions' ); ?></option>
					<option value="4-6" <?php selected( $ge_duration, '4-6' ); ?>><?php esc_html_e( '4 - 6 days', 'gangotri-expeditions' ); ?></option>
					<option value="7-99" <?php selected( $ge_duration, '7-99' ); ?>><?php esc_html_e( '7 days or more', 'gangotri-expeditions' ); ?></option>
				</select>
			</div>

			<div>
				<label class="label" for="f-sort"><?php esc_html_e( 'Sort by', 'gangotri-expeditions' ); ?></label>
				<?php $ge_sort = ge_filter_value( 'sort' ); ?>
				<select class="field" id="f-sort" name="sort">
					<option value=""><?php esc_html_e( 'Featured', 'gangotri-expeditions' ); ?></option>
					<option value="price-asc" <?php selected( $ge_sort, 'price-asc' ); ?>><?php esc_html_e( 'Price, low to high', 'gangotri-expeditions' ); ?></option>
					<option value="price-desc" <?php selected( $ge_sort, 'price-desc' ); ?>><?php esc_html_e( 'Price, high to low', 'gangotri-expeditions' ); ?></option>
					<option value="duration-asc" <?php selected( $ge_sort, 'duration-asc' ); ?>><?php esc_html_e( 'Duration, short to long', 'gangotri-expeditions' ); ?></option>
					<option value="duration-desc" <?php selected( $ge_sort, 'duration-desc' ); ?>><?php esc_html_e( 'Duration, long to short', 'gangotri-expeditions' ); ?></option>
				</select>
			</div>

			<noscript>
				<button class="btn btn-primary w-full md:self-end" type="submit"><?php esc_html_e( 'Apply', 'gangotri-expeditions' ); ?></button>
			</noscript>
		</form>

		<div class="flex items-center justify-between gap-4 mt-8 mb-6">
			<p class="text-sm text-ink/60" data-filter-count aria-live="polite">
				<?php
				$ge_total = (int) $GLOBALS['wp_query']->found_posts;
				printf(
					esc_html(
						/* translators: %s: number of packages. */
						_n( '%s package', '%s packages', $ge_total, 'gangotri-expeditions' )
					),
					esc_html( number_format_i18n( $ge_total ) )
				);
				?>
			</p>
			<a class="btn btn-outline btn-sm" href="<?php echo esc_url( ge_yatras_url() ); ?>" data-filter-reset>
				<?php ge_icon( 'x' ); ?> <?php esc_html_e( 'Clear filters', 'gangotri-expeditions' ); ?>
			</a>
		</div>

		<div class="card-grid" data-filter-grid>
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/yatra-card' );
			endwhile;
			?>
		</div>

		<?php // $ge_total, not have_posts(): the loop above has already run. ?>
		<div class="empty-state" data-filter-empty <?php echo $ge_total ? 'hidden' : ''; ?>>
			<span class="trust-icon mx-auto"><?php ge_icon( 'compass' ); ?></span>
			<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'Nothing matches those filters', 'gangotri-expeditions' ); ?></p>
			<p class="mt-2 text-sm text-ink/65">
				<?php esc_html_e( 'Try widening the duration, or tell us what you are looking for - we build custom itineraries too.', 'gangotri-expeditions' ); ?>
			</p>
			<a class="btn btn-primary mt-6" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
				<?php ge_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Ask for a custom yatra', 'gangotri-expeditions' ); ?>
			</a>
		</div>

		<?php
		// Only paints once there is more than one page of packages.
		the_posts_pagination( array(
			'mid_size'  => 1,
			'class'     => 'ge-pagination mt-12',
			'prev_text' => ge_get_icon( 'chevron-left' ) . esc_html__( 'Previous', 'gangotri-expeditions' ),
			'next_text' => esc_html__( 'Next', 'gangotri-expeditions' ) . ge_get_icon( 'chevron-right' ),
		) );
		?>

	</div>
</section>

<?php
get_template_part( 'parts/cta-band' );
get_footer();
