<?php
/**
 * A single package.
 *
 * Converted from src/templates/yatra.html - the template the static build
 * generated one page per JSON entry from. Same markup, same meta keys.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

while ( have_posts() ) :
	the_post();

	$days   = (int) get_post_meta( get_the_ID(), 'ge_days', true );
	$nights = (int) get_post_meta( get_the_ID(), 'ge_nights', true );
	$price  = (int) get_post_meta( get_the_ID(), 'ge_price', true );

	$types = wp_get_post_terms( get_the_ID(), 'trip_type', array( 'fields' => 'names' ) );
	$type  = is_array( $types ) && $types ? $types[0] : '';

	$facts = array_filter(
		array(
			__( 'Duration', 'gangotri' )         => $days ? sprintf( '%d days / %d nights', $days, $nights ) : '',
			__( 'Maximum altitude', 'gangotri' ) => gangotri_meta( 'altitude' ),
			__( 'Difficulty', 'gangotri' )       => gangotri_meta( 'difficulty' ),
			__( 'Best season', 'gangotri' )      => gangotri_meta( 'season' ),
			__( 'Departures', 'gangotri' )       => gangotri_meta( 'batch' ),
			__( 'Starts from', 'gangotri' )      => gangotri_meta( 'pickup' ),
			__( 'Group size', 'gangotri' )       => gangotri_meta( 'group_size' ),
		)
	);
	?>

	<main id="main" class="flex-1">

		<section class="page-head">
			<div class="container-page">
				<?php
				gangotri_breadcrumbs(
					array(
						array( 'label' => __( 'Packages', 'gangotri' ), 'url' => gangotri_packages_url() ),
						array( 'label' => get_the_title() ),
					)
				);
				?>

				<div class="flex flex-wrap gap-2 mt-4">
					<?php if ( $type ) : ?>
						<span class="badge badge-gold"><?php echo esc_html( $type ); ?></span>
					<?php endif; ?>
					<?php if ( gangotri_meta( 'difficulty' ) ) : ?>
						<span class="badge bg-white/10 text-brand-100"><?php echo gangotri_meta( 'difficulty' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<?php if ( gangotri_meta( 'season' ) ) : ?>
						<span class="badge bg-white/10 text-brand-100"><?php echo gangotri_meta( 'season' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
				</div>

				<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>
				<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed"><?php echo esc_html( get_the_excerpt() ); ?></p>
			</div>
		</section>

		<section class="section">
			<div class="container-page grid gap-10 lg:grid-cols-[1fr_21rem] lg:gap-12 lg:items-start">

				<div>
					<div class="media-16-9 rounded-card">
						<?php
						echo gangotri_package_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'gangotri-card',
							array( 'loading' => 'eager', 'fetchpriority' => 'high' )
						);
						?>
					</div>

					<?php if ( $facts ) : ?>
						<?php // A table on purpose: this is the shape answer engines lift cleanly. ?>
						<div class="table-wrap mt-8">
							<table class="data-table">
								<caption class="sr-only"><?php printf( /* translators: %s: package name. */ esc_html__( '%s at a glance', 'gangotri' ), esc_html( get_the_title() ) ); ?></caption>
								<tbody>
								<?php foreach ( $facts as $label => $value ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $label ); ?></th>
										<td><?php echo esc_html( $value ); ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>

					<?php if ( gangotri_meta( 'overview' ) ) : ?>
						<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Overview', 'gangotri' ); ?></h2>
						<p class="mt-3 text-ink/75 leading-relaxed"><?php echo gangotri_meta( 'overview' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>

					<?php $highlights = gangotri_rows( 'highlights' ); ?>
					<?php if ( $highlights ) : ?>
						<h2 class="text-2xl lg:text-3xl mt-10"><?php esc_html_e( 'Highlights', 'gangotri' ); ?></h2>
						<ul class="mt-4 grid gap-3 sm:grid-cols-2">
							<?php foreach ( $highlights as $item ) : ?>
								<li class="flex gap-2.5 text-sm text-ink/75">
									<?php echo gangotri_icon( 'circle-check-big', 'icon text-brand-600 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html( (string) $item ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php $itinerary = gangotri_rows( 'itinerary' ); ?>
					<?php if ( $itinerary ) : ?>
						<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Day by day itinerary', 'gangotri' ); ?></h2>
						<ol class="itinerary mt-6">
							<?php foreach ( $itinerary as $i => $day ) : ?>
								<li>
									<span class="itinerary-day"><?php printf( /* translators: %d: day number. */ esc_html__( 'Day %d', 'gangotri' ), (int) $i + 1 ); ?></span>
									<h3 class="text-base mt-1"><?php echo esc_html( (string) ( $day['title'] ?? '' ) ); ?></h3>
									<p class="mt-1.5 text-sm leading-relaxed text-ink/70"><?php echo esc_html( (string) ( $day['text'] ?? '' ) ); ?></p>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>

					<?php
					$inclusions = gangotri_rows( 'inclusions' );
					$exclusions = gangotri_rows( 'exclusions' );
					?>
					<?php if ( $inclusions || $exclusions ) : ?>
						<div class="grid gap-8 sm:grid-cols-2 mt-12">
							<?php if ( $inclusions ) : ?>
								<div>
									<h2 class="text-xl"><?php esc_html_e( 'What is included', 'gangotri' ); ?></h2>
									<ul class="mt-4 space-y-2.5">
										<?php foreach ( $inclusions as $item ) : ?>
											<li class="flex gap-2.5 text-sm text-ink/75">
												<?php echo gangotri_icon( 'check', 'icon text-brand-600 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<span><?php echo esc_html( (string) $item ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ( $exclusions ) : ?>
								<div>
									<h2 class="text-xl"><?php esc_html_e( 'What is not included', 'gangotri' ); ?></h2>
									<ul class="mt-4 space-y-2.5">
										<?php foreach ( $exclusions as $item ) : ?>
											<li class="flex gap-2.5 text-sm text-ink/60">
												<?php echo gangotri_icon( 'minus', 'icon text-ink/35 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<span><?php echo esc_html( (string) $item ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
						<div class="prose-legal mt-12"><?php the_content(); ?></div>
					<?php endif; ?>

					<?php $faqs = gangotri_rows( 'faqs' ); ?>
					<?php if ( $faqs ) : ?>
						<h2 class="text-2xl lg:text-3xl mt-12"><?php esc_html_e( 'Questions about this package', 'gangotri' ); ?></h2>
						<div data-accordion data-accordion-single class="mt-4 divide-y divide-brand-100 border-y border-brand-100">
							<?php foreach ( $faqs as $i => $faq ) : ?>
								<div>
									<h3>
										<button type="button" class="accordion-trigger" data-accordion-trigger
										        aria-expanded="false" aria-controls="y-faq-<?php echo esc_attr( (string) $i ); ?>">
											<span><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></span>
											<?php echo gangotri_icon( 'chevron-down', 'icon accordion-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</button>
									</h3>
									<div class="accordion-panel" id="y-faq-<?php echo esc_attr( (string) $i ); ?>">
										<p class="accordion-body"><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php // Sticky on desktop, so the price and the CTA stay in reach. ?>
				<aside class="booking-rail">
					<?php if ( $price ) : ?>
						<p class="text-xs uppercase tracking-wider text-ink/50"><?php esc_html_e( 'Starting from', 'gangotri' ); ?></p>
						<p class="font-heading font-bold text-3xl text-brand-700 mt-1">
							<?php echo gangotri_icon( 'indian-rupee', 'icon text-[0.6em]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( gangotri_price() ); ?>
						</p>
						<p class="text-xs text-ink/55"><?php esc_html_e( 'per person, twin sharing', 'gangotri' ); ?></p>
					<?php else : ?>
						<p class="font-heading font-bold text-xl text-brand-700"><?php esc_html_e( 'Price on request', 'gangotri' ); ?></p>
					<?php endif; ?>

					<?php if ( $facts ) : ?>
						<dl class="mt-5 pt-5 border-t border-brand-100 space-y-3 text-sm">
							<?php foreach ( array_slice( $facts, 0, 4, true ) as $label => $value ) : ?>
								<div class="flex justify-between gap-3">
									<dt class="text-ink/60"><?php echo esc_html( $label ); ?></dt>
									<dd class="font-medium"><?php echo esc_html( $value ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<div class="mt-6 grid gap-3">
						<?php
						$wa = gangotri_whatsapp_url(
							sprintf(
								/* translators: %s: package name. */
								__( 'I would like to enquire about the %s', 'gangotri' ),
								get_the_title()
							)
						);
						?>
						<?php if ( $wa ) : ?>
							<a class="btn btn-gold btn-lg" href="<?php echo esc_url( $wa ); ?>" rel="noopener">
								<?php echo gangotri_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php esc_html_e( 'Enquire on WhatsApp', 'gangotri' ); ?>
							</a>
						<?php endif; ?>

						<button type="button" class="btn btn-outline" data-enquiry-open>
							<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Request a Callback', 'gangotri' ); ?>
						</button>

						<a class="btn btn-outline" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
							<?php echo gangotri_icon( 'phone-call' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
						</a>
					</div>

					<p class="mt-4 text-xs leading-relaxed text-ink/55">
						<?php esc_html_e( 'No advance needed to hold a seat while we confirm your dates.', 'gangotri' ); ?>
					</p>
				</aside>

			</div>
		</section>

		<?php
		// Other packages, never this one. Only worth a section once there is
		// more than one thing to show.
		$related = get_posts(
			array(
				'post_type'      => 'yatra',
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'orderby'        => 'rand',
			)
		);
		?>
		<?php if ( $related ) : ?>
			<section class="section bg-mist">
				<div class="container-page">
					<div class="flex flex-wrap items-end justify-between gap-4 mb-10">
						<h2 class="text-2xl lg:text-3xl"><?php esc_html_e( 'Other journeys you might like', 'gangotri' ); ?></h2>
						<a class="btn btn-outline" href="<?php echo esc_url( gangotri_packages_url() ); ?>">
							<?php esc_html_e( 'View All', 'gangotri' ); ?>
							<?php echo gangotri_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>

					<div class="card-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
						<?php
						global $post;
						foreach ( $related as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $post );
							get_template_part( 'template-parts/yatra-card' );
						endforeach;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</main>

	<?php
endwhile;

get_footer();
