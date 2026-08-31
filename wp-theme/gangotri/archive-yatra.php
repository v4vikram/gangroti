<?php
/**
 * Package archive, and the destination / trip type taxonomy pages.
 *
 * Converted from src/yatras.html. Filtering stays client-side on the posts
 * already rendered, so every package is in the HTML for crawlers and the page
 * still works without JavaScript.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

$is_tax = is_tax();
$title  = $is_tax ? single_term_title( '', false ) : __( 'Our Packages', 'gangotri' );

$intro = $is_tax
	? term_description()
	: __( 'Every journey below is a fixed departure with a published itinerary, a real altitude figure and an honest difficulty rating. Filter to what suits you, then ask us anything.', 'gangotri' );

// Terms actually in use, so a filter can never offer an empty result set.
$destinations = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => true ) );
$trip_types   = get_terms( array( 'taxonomy' => 'trip_type', 'hide_empty' => true ) );
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php
			gangotri_breadcrumbs(
				$is_tax
					? array(
						array( 'label' => __( 'Packages', 'gangotri' ), 'url' => gangotri_packages_url() ),
						array( 'label' => $title ),
					)
					: array( array( 'label' => __( 'Packages', 'gangotri' ) ) )
			);
			?>

			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php echo esc_html( $title ); ?></h1>

			<?php if ( $intro ) : ?>
				<div class="mt-4 max-w-2xl text-brand-100 leading-relaxed"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container-page">

			<form class="filter-bar" data-filter method="get" role="search"
			      aria-label="<?php esc_attr_e( 'Filter packages', 'gangotri' ); ?>">

				<div>
					<label class="label" for="f-destination"><?php esc_html_e( 'Destination', 'gangotri' ); ?></label>
					<select class="field" id="f-destination" name="destination">
						<option value=""><?php esc_html_e( 'All destinations', 'gangotri' ); ?></option>
						<?php foreach ( (array) $destinations as $term ) : ?>
							<option value="<?php echo esc_attr( $term->name ); ?>"><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label class="label" for="f-type"><?php esc_html_e( 'Type', 'gangotri' ); ?></label>
					<select class="field" id="f-type" name="type">
						<option value=""><?php esc_html_e( 'All types', 'gangotri' ); ?></option>
						<?php foreach ( (array) $trip_types as $term ) : ?>
							<option value="<?php echo esc_attr( $term->name ); ?>"><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label class="label" for="f-duration"><?php esc_html_e( 'Duration', 'gangotri' ); ?></label>
					<select class="field" id="f-duration" name="duration">
						<option value=""><?php esc_html_e( 'Any length', 'gangotri' ); ?></option>
						<option value="1-3"><?php esc_html_e( '1 - 3 days', 'gangotri' ); ?></option>
						<option value="4-6"><?php esc_html_e( '4 - 6 days', 'gangotri' ); ?></option>
						<option value="7-99"><?php esc_html_e( '7 days or more', 'gangotri' ); ?></option>
					</select>
				</div>

				<div>
					<label class="label" for="f-sort"><?php esc_html_e( 'Sort by', 'gangotri' ); ?></label>
					<select class="field" id="f-sort" name="sort">
						<option value=""><?php esc_html_e( 'Featured', 'gangotri' ); ?></option>
						<option value="price-asc"><?php esc_html_e( 'Price, low to high', 'gangotri' ); ?></option>
						<option value="price-desc"><?php esc_html_e( 'Price, high to low', 'gangotri' ); ?></option>
						<option value="duration-asc"><?php esc_html_e( 'Duration, short to long', 'gangotri' ); ?></option>
						<option value="duration-desc"><?php esc_html_e( 'Duration, long to short', 'gangotri' ); ?></option>
					</select>
				</div>

				<noscript>
					<button class="btn btn-primary w-full md:self-end" type="submit"><?php esc_html_e( 'Apply', 'gangotri' ); ?></button>
				</noscript>
			</form>

			<div class="flex items-center justify-between gap-4 mt-8 mb-6">
				<p class="text-sm text-ink/60" data-filter-count aria-live="polite"></p>
				<button type="button" class="btn btn-outline btn-sm" data-filter-reset>
					<?php echo gangotri_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Clear filters', 'gangotri' ); ?>
				</button>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="card-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-filter-grid>
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/yatra-card' );
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( 'Previous', 'gangotri' ),
						'next_text' => __( 'Next', 'gangotri' ),
						'class'     => 'pagination mt-10',
					)
				);
				?>
			<?php else : ?>
				<div class="empty-state">
					<span class="trust-icon mx-auto"><?php echo gangotri_icon( 'compass' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'No packages here yet', 'gangotri' ); ?></p>
					<p class="mt-2 text-sm text-ink/65"><?php esc_html_e( 'Tell us what you are looking for - we build custom itineraries too.', 'gangotri' ); ?></p>
					<button type="button" class="btn btn-primary mt-6" data-enquiry-open>
						<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Ask for a custom package', 'gangotri' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<?php // Shown by the filter when the client-side result set is empty. ?>
			<div class="empty-state" data-filter-empty hidden>
				<span class="trust-icon mx-auto"><?php echo gangotri_icon( 'compass' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'Nothing matches those filters', 'gangotri' ); ?></p>
				<p class="mt-2 text-sm text-ink/65"><?php esc_html_e( 'Try widening the duration, or tell us what you are looking for - we build custom itineraries too.', 'gangotri' ); ?></p>
				<button type="button" class="btn btn-primary mt-6" data-enquiry-open>
					<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Ask for a custom package', 'gangotri' ); ?>
				</button>
			</div>

		</div>
	</section>

	<section class="cta-band">
		<div class="container-page text-center max-w-3xl">
			<p class="text-3xl lg:text-4xl font-heading font-bold text-white text-balance">
				<?php esc_html_e( 'Not sure which one to pick?', 'gangotri' ); ?>
			</p>
			<p class="mt-4 text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Tell us who is travelling, how many days you have, and what you want out of it. We will suggest the right package - and say so if none of ours fits.', 'gangotri' ); ?>
			</p>
			<div class="mt-8 flex flex-wrap justify-center gap-3">
				<button type="button" class="btn btn-gold btn-lg" data-enquiry-open>
					<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Get a Free Quote', 'gangotri' ); ?>
				</button>
				<a class="btn btn-ghost-light btn-lg" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
					<?php echo gangotri_icon( 'phone-call' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
