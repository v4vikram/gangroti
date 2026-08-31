<?php
/**
 * Template Name: Home
 *
 * The front page. Converted from src/index.html.
 *
 * The hero slides and the FAQ come from meta boxes on this page (see
 * inc/meta/home-fields.php) so the client can change them. The trust bar and
 * the "why us" copy are fixed - they are brand statements, not content, and
 * making them editable only invites them to be broken.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

$slides = gangotri_rows( 'home_slides' );
$faqs   = gangotri_rows( 'home_faqs' );
?>

<main id="main" class="flex-1">

	<?php if ( $slides ) : ?>
		<section class="relative" aria-label="<?php esc_attr_e( 'Featured journeys', 'gangotri' ); ?>">
			<div class="slider" data-slider data-per-view="1" data-autoplay="6000" data-loop>
				<div class="slider-viewport">
					<ul class="slider-track">
						<?php foreach ( $slides as $i => $slide ) : ?>
							<?php
							$image_id  = (int) ( $slide['image'] ?? 0 );
							$is_first  = 0 === $i;
							$link      = (string) ( $slide['link'] ?? '' );
							$link_text = (string) ( $slide['link_text'] ?? '' );
							?>
							<li class="slider-slide w-full relative">
								<div class="hero-media">
									<?php
									if ( $image_id ) {
										echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											$image_id,
											'gangotri-hero',
											false,
											array(
												// Only the first slide is the LCP element; the rest
												// must not compete with it for early bandwidth.
												'loading'       => $is_first ? 'eager' : 'lazy',
												'fetchpriority' => $is_first ? 'high' : 'auto',
												'decoding'      => 'async',
											)
										);
									}
									?>
								</div>

								<div class="hero-overlay"></div>

								<div class="hero-content">
									<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
										<span class="eyebrow text-gold-400">
											<?php echo gangotri_icon( 'trishul' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<?php echo esc_html( (string) $slide['eyebrow'] ); ?>
										</span>
									<?php endif; ?>

									<?php
									// Exactly one <h1> per page: the first slide carries it,
									// the rest are paragraphs styled to match.
									$tag = $is_first ? 'h1' : 'p';
									printf(
										'<%1$s class="hero-title">%2$s</%1$s>',
										esc_attr( $tag ),
										wp_kses( nl2br( esc_html( (string) ( $slide['title'] ?? '' ) ) ), array( 'br' => array() ) )
									);
									?>

									<?php if ( ! empty( $slide['text'] ) ) : ?>
										<p class="hero-text"><?php echo esc_html( (string) $slide['text'] ); ?></p>
									<?php endif; ?>

									<div class="hero-actions">
										<?php if ( $link && $link_text ) : ?>
											<a class="btn btn-gold btn-lg" href="<?php echo esc_url( $link ); ?>">
												<?php echo esc_html( $link_text ); ?>
												<?php echo gangotri_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</a>
										<?php endif; ?>

										<?php if ( $is_first ) : ?>
											<button type="button" class="btn btn-ghost-light btn-lg" data-enquiry-open>
												<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<?php esc_html_e( 'Enquire Now', 'gangotri' ); ?>
											</button>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php if ( count( $slides ) > 1 ) : ?>
					<div class="hidden md:flex absolute bottom-8 right-8 z-20 gap-2">
						<button type="button" class="slider-arrow bg-white/15 border-white/30 text-white backdrop-blur-sm hover:bg-white hover:text-brand-700"
						        data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'gangotri' ); ?>">
							<?php echo gangotri_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<button type="button" class="slider-arrow bg-white/15 border-white/30 text-white backdrop-blur-sm hover:bg-white hover:text-brand-700"
						        data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'gangotri' ); ?>">
							<?php echo gangotri_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>

					<div class="slider-dots absolute bottom-8 left-1/2 -translate-x-1/2 z-20 md:left-8 md:translate-x-0" data-slider-dots></div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php // Brand promises, not content - fixed on purpose. ?>
	<section class="section pt-10 md:pt-14" aria-label="<?php esc_attr_e( 'Why travel with us', 'gangotri' ); ?>">
		<div class="container-page">
			<ul class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8">
				<?php
				$trust = array(
					array( 'compass', __( 'Expert Guides', 'gangotri' ), __( 'Local leaders, certified in high-altitude first aid.', 'gangotri' ) ),
					array( 'trishul', __( 'Spiritual Experience', 'gangotri' ), __( 'Darshan timed to avoid the crush, priests arranged.', 'gangotri' ) ),
					array( 'bed', __( 'Comfortable Stays', 'gangotri' ), __( 'Vetted hotels and camps, never a last-minute room.', 'gangotri' ) ),
					array( 'shield-check', __( 'Safe & Memorable', 'gangotri' ), __( 'Oxygen, medical kit and evacuation plan on every trip.', 'gangotri' ) ),
				);

				foreach ( $trust as $item ) :
					?>
					<li class="flex flex-col items-center text-center gap-3" data-reveal>
						<span class="trust-icon"><?php echo gangotri_icon( $item[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h2 class="text-base"><?php echo esc_html( $item[1] ); ?></h2>
						<p class="text-sm text-ink/65"><?php echo esc_html( $item[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php
	$packages = get_posts(
		array(
			'post_type'      => 'yatra',
			'posts_per_page' => 6,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);
	?>
	<?php if ( $packages ) : ?>
		<section class="section bg-mist" aria-labelledby="popular-yatras">
			<div class="container-page">
				<div class="flex flex-wrap items-end justify-between gap-4 mb-10">
					<div class="max-w-2xl">
						<span class="eyebrow">
							<?php echo gangotri_icon( 'route' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Popular Yatras', 'gangotri' ); ?>
						</span>
						<h2 id="popular-yatras" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Our Packages', 'gangotri' ); ?></h2>
						<p class="mt-3 text-ink/70 leading-relaxed">
							<?php esc_html_e( 'Fixed departures with published itineraries, real altitudes and honest difficulty ratings - so you know what you are signing up for before you pay.', 'gangotri' ); ?>
						</p>
					</div>

					<a class="btn btn-outline" href="<?php echo esc_url( gangotri_packages_url() ); ?>">
						<?php esc_html_e( 'View All Packages', 'gangotri' ); ?>
						<?php echo gangotri_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>

				<div class="card-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
					<?php
					global $post;
					foreach ( $packages as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/yatra-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $faqs ) : ?>
		<section class="section bg-mist" aria-labelledby="faq">
			<div class="container-page grid gap-10 lg:grid-cols-[22rem_1fr] lg:gap-16">
				<div>
					<span class="eyebrow">
						<?php echo gangotri_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'FAQ', 'gangotri' ); ?>
					</span>
					<h2 id="faq" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Before you book', 'gangotri' ); ?></h2>
					<p class="mt-3 text-ink/70 leading-relaxed">
						<?php esc_html_e( 'Anything not covered here, message us - a real person answers.', 'gangotri' ); ?>
					</p>
					<button type="button" class="btn btn-primary mt-6" data-enquiry-open>
						<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Ask a Question', 'gangotri' ); ?>
					</button>
				</div>

				<div data-accordion data-accordion-single class="divide-y divide-brand-100 border-y border-brand-100">
					<?php foreach ( $faqs as $i => $faq ) : ?>
						<div>
							<h3>
								<button type="button" class="accordion-trigger" data-accordion-trigger
								        aria-expanded="false" aria-controls="home-faq-<?php echo esc_attr( (string) $i ); ?>">
									<span><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></span>
									<?php echo gangotri_icon( 'chevron-down', 'icon accordion-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							</h3>
							<div class="accordion-panel" id="home-faq-<?php echo esc_attr( (string) $i ); ?>">
								<p class="accordion-body"><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/enquiry-section' ); ?>

</main>

<?php
get_footer();
