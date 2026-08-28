<?php
/**
 * One package.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ge_days       = ge_field( 'days' );
	$ge_nights     = ge_field( 'nights' );
	$ge_price      = ge_field( 'price' );
	$ge_difficulty = ge_field( 'difficulty' );
	$ge_altitude   = ge_field( 'altitude' );
	$ge_season     = ge_field( 'season' );
	$ge_batch      = ge_field( 'batch' );
	$ge_pickup     = ge_field( 'pickup' );
	$ge_group      = ge_field( 'group_size' );
	$ge_type       = ge_term_name( 'yatra_type' );
	?>

	<section class="page-head">
		<div class="container-page">
			<?php
			ge_breadcrumb( array(
				__( 'Home', 'gangotri-expeditions' )   => home_url( '/' ),
				__( 'Yatras', 'gangotri-expeditions' ) => ge_yatras_url(),
				get_the_title()                        => '',
			) );
			?>

			<div class="flex flex-wrap gap-2 mt-4">
				<?php if ( $ge_type ) : ?>
					<span class="badge badge-gold"><?php echo esc_html( $ge_type ); ?></span>
				<?php endif; ?>
				<?php if ( $ge_difficulty ) : ?>
					<span class="badge bg-white/10 text-brand-100"><?php echo esc_html( $ge_difficulty ); ?></span>
				<?php endif; ?>
				<?php if ( $ge_season ) : ?>
					<span class="badge bg-white/10 text-brand-100"><?php echo esc_html( $ge_season ); ?></span>
				<?php endif; ?>
			</div>

			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed"><?php echo esc_html( ge_summary() ); ?></p>
		</div>
	</section>

	<section class="section">
		<div class="container-page grid gap-10 lg:grid-cols-[1fr_21rem] lg:gap-12 lg:items-start">

			<div>
				<div class="media-16-9 rounded-card">
					<?php
					// The LCP element on this page, so it loads eagerly and at
					// high priority rather than waiting its turn.
					ge_thumbnail( 'ge-hero', array(
						'fetchpriority' => 'high',
						'decoding'      => 'async',
					) );
					?>
				</div>

				<?php
				// Key facts as a table: the shape AI answer engines lift cleanly.
				$ge_facts = array_filter( array(
					__( 'Duration', 'gangotri-expeditions' )         => $ge_days ? sprintf(
						/* translators: 1: days, 2: nights. */
						__( '%1$d days / %2$d nights', 'gangotri-expeditions' ),
						(int) $ge_days,
						(int) $ge_nights
					) : '',
					__( 'Maximum altitude', 'gangotri-expeditions' ) => $ge_altitude,
					__( 'Difficulty', 'gangotri-expeditions' )       => $ge_difficulty,
					__( 'Best season', 'gangotri-expeditions' )      => $ge_season,
					__( 'Departures', 'gangotri-expeditions' )       => $ge_batch,
					__( 'Starts from', 'gangotri-expeditions' )      => $ge_pickup,
					__( 'Group size', 'gangotri-expeditions' )       => $ge_group,
				) );
				?>

				<?php if ( $ge_facts ) : ?>
					<div class="table-wrap mt-8">
						<table class="data-table">
							<caption class="sr-only">
								<?php
								printf(
									/* translators: %s: package title. */
									esc_html__( '%s at a glance', 'gangotri-expeditions' ),
									esc_html( get_the_title() )
								);
								?>
							</caption>
							<tbody>
								<?php foreach ( $ge_facts as $ge_label => $ge_value ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $ge_label ); ?></th>
										<td><?php echo esc_html( $ge_value ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<?php if ( trim( get_the_content() ) ) : ?>
					<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Overview', 'gangotri-expeditions' ); ?></h2>
					<div class="prose-ge mt-3 text-ink/75 leading-relaxed">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>

				<?php $ge_highlights = ge_field( 'highlights' ); ?>
				<?php if ( $ge_highlights ) : ?>
					<h2 class="text-2xl lg:text-3xl mt-10"><?php esc_html_e( 'Highlights', 'gangotri-expeditions' ); ?></h2>
					<ul class="mt-4 grid gap-3 sm:grid-cols-2">
						<?php foreach ( $ge_highlights as $ge_item ) : ?>
							<li class="flex gap-2.5 text-sm text-ink/75">
								<?php ge_icon( 'circle-check-big', 'text-brand-600 mt-0.5' ); ?>
								<span><?php echo esc_html( $ge_item ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php $ge_itinerary = ge_field( 'itinerary' ); ?>
				<?php if ( $ge_itinerary ) : ?>
					<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Day by day itinerary', 'gangotri-expeditions' ); ?></h2>
					<ol class="itinerary mt-6">
						<?php foreach ( $ge_itinerary as $ge_i => $ge_day ) : ?>
							<li>
								<span class="itinerary-day">
									<?php
									printf(
										/* translators: %d: day number. */
										esc_html__( 'Day %d', 'gangotri-expeditions' ),
										(int) $ge_i + 1
									);
									?>
								</span>
								<h3 class="text-base mt-1"><?php echo esc_html( $ge_day['title'] ?? '' ); ?></h3>
								<p class="mt-1.5 text-sm leading-relaxed text-ink/70"><?php echo esc_html( $ge_day['text'] ?? '' ); ?></p>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>

				<?php
				$ge_inclusions = ge_field( 'inclusions' );
				$ge_exclusions = ge_field( 'exclusions' );
				?>
				<?php if ( $ge_inclusions || $ge_exclusions ) : ?>
					<div class="grid gap-8 sm:grid-cols-2 mt-12">
						<?php if ( $ge_inclusions ) : ?>
							<div>
								<h2 class="text-xl"><?php esc_html_e( 'What is included', 'gangotri-expeditions' ); ?></h2>
								<ul class="mt-4 space-y-2.5">
									<?php foreach ( $ge_inclusions as $ge_item ) : ?>
										<li class="flex gap-2.5 text-sm text-ink/75">
											<?php ge_icon( 'check', 'text-brand-600 mt-0.5' ); ?>
											<span><?php echo esc_html( $ge_item ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<?php if ( $ge_exclusions ) : ?>
							<div>
								<h2 class="text-xl"><?php esc_html_e( 'What is not included', 'gangotri-expeditions' ); ?></h2>
								<ul class="mt-4 space-y-2.5">
									<?php foreach ( $ge_exclusions as $ge_item ) : ?>
										<li class="flex gap-2.5 text-sm text-ink/60">
											<?php ge_icon( 'minus', 'text-ink/35 mt-0.5' ); ?>
											<span><?php echo esc_html( $ge_item ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php $ge_faqs = ge_field( 'faqs' ); ?>
				<?php if ( $ge_faqs ) : ?>
					<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Questions about this yatra', 'gangotri-expeditions' ); ?></h2>
					<div data-accordion data-accordion-single class="mt-4 divide-y divide-brand-100 border-y border-brand-100">
						<?php foreach ( $ge_faqs as $ge_i => $ge_faq ) : ?>
							<div>
								<h3>
									<button type="button" class="accordion-trigger" data-accordion-trigger
									        aria-expanded="false" aria-controls="y-faq-<?php echo (int) $ge_i + 1; ?>">
										<span><?php echo esc_html( $ge_faq['q'] ?? '' ); ?></span>
										<?php ge_icon( 'chevron-down', 'accordion-chevron' ); ?>
									</button>
								</h3>
								<div class="accordion-panel" id="y-faq-<?php echo (int) $ge_i + 1; ?>">
									<p class="accordion-body"><?php echo esc_html( $ge_faq['a'] ?? '' ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Booking rail: sticky on desktop so the price and CTA stay in reach. -->
			<aside class="booking-rail">
				<?php if ( $ge_price ) : ?>
					<p class="text-xs uppercase tracking-wider text-ink/50"><?php esc_html_e( 'Starting from', 'gangotri-expeditions' ); ?></p>
					<p class="font-heading font-bold text-3xl text-brand-700 mt-1">
						<?php ge_icon( 'indian-rupee', 'text-[0.6em]' ); ?><?php echo esc_html( ge_format_price( $ge_price ) ); ?>
					</p>
					<p class="text-xs text-ink/55"><?php esc_html_e( 'per person, twin sharing', 'gangotri-expeditions' ); ?></p>
				<?php else : ?>
					<p class="font-heading font-bold text-2xl text-brand-700"><?php esc_html_e( 'Price on request', 'gangotri-expeditions' ); ?></p>
				<?php endif; ?>

				<dl class="mt-5 pt-5 border-t border-brand-100 space-y-3 text-sm">
					<?php
					$ge_rail = array_filter( array(
						__( 'Duration', 'gangotri-expeditions' )     => $ge_days ? sprintf( '%dD / %dN', (int) $ge_days, (int) $ge_nights ) : '',
						__( 'Difficulty', 'gangotri-expeditions' )   => $ge_difficulty,
						__( 'Max altitude', 'gangotri-expeditions' ) => $ge_altitude,
						__( 'Departures', 'gangotri-expeditions' )   => $ge_batch,
					) );

					foreach ( $ge_rail as $ge_label => $ge_value ) :
						?>
						<div class="flex justify-between gap-3">
							<dt class="text-ink/60"><?php echo esc_html( $ge_label ); ?></dt>
							<dd class="font-medium"><?php echo esc_html( $ge_value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<div class="mt-6 grid gap-3">
					<a class="btn btn-gold btn-lg" rel="noopener"
					   href="<?php echo esc_url( ge_whatsapp_url( sprintf(
						   /* translators: %s: package title. */
						   __( 'I would like to enquire about the %s', 'gangotri-expeditions' ),
						   get_the_title()
					   ) ) ); ?>">
						<?php ge_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Enquire on WhatsApp', 'gangotri-expeditions' ); ?>
					</a>
					<button type="button" class="btn btn-outline" data-enquiry-open>
						<?php ge_icon( 'send' ); ?> <?php esc_html_e( 'Request a Callback', 'gangotri-expeditions' ); ?>
					</button>
					<a class="btn btn-outline" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
						<?php ge_icon( 'phone-call' ); ?> <?php echo esc_html( ge_option( 'phone' ) ); ?>
					</a>
				</div>

				<p class="mt-4 text-xs leading-relaxed text-ink/55">
					<?php esc_html_e( 'No advance needed to hold a seat while we confirm your dates.', 'gangotri-expeditions' ); ?>
				</p>
			</aside>

		</div>
	</section>

	<?php
	// Other packages, now that there can be more than one. Excluding the
	// current post is what the static @each loop could not do.
	$ge_related = get_posts( array(
		'post_type'      => 'yatra',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'orderby'        => 'rand',
	) );

	if ( $ge_related ) :
		?>
		<section class="section bg-mist">
			<div class="container-page">
				<h2 class="text-2xl lg:text-3xl mb-8"><?php esc_html_e( 'Other journeys you might like', 'gangotri-expeditions' ); ?></h2>
				<div class="card-grid">
					<?php
					foreach ( $ge_related as $ge_post ) :
						setup_postdata( $GLOBALS['post'] = $ge_post ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						get_template_part( 'parts/yatra-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'parts/cta-band', null, array(
		'title' => __( 'Questions about this trek?', 'gangotri-expeditions' ),
		'text'  => __( 'Tell us your dates and who is walking. We will tell you honestly whether the route suits your group.', 'gangotri-expeditions' ),
	) );
	?>

<?php
endwhile;

get_footer();
