<?php
/**
 * The homepage.
 *
 * Packages come from the CPT; the hero, reviews and FAQ are editorial copy
 * that belongs to the brand rather than to the client's day-to-day editing, so
 * they live here as arrays at the top of the file. Repeating markup five times
 * for five reviews - as the static build did - is how a typo ends up in one of
 * them and nobody notices.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

/* ------------------------------------------------------------------ hero -- */

$ge_slides = array(
	array(
		'image'   => 'hero-1',
		'alt'     => __( 'Kedarnath temple below the snow-covered Kedarnath peak', 'gangotri-expeditions' ),
		'icon'    => 'trishul',
		'eyebrow' => __( 'Char Dham 2026', 'gangotri-expeditions' ),
		'title'   => __( 'Spiritual Journeys.<br>Timeless Memories.', 'gangotri-expeditions' ),
		'text'    => __( 'Curated yatras to the most sacred destinations in the Himalayas, led by guides who have walked these trails for years.', 'gangotri-expeditions' ),
		'cta'     => __( 'Explore Yatras', 'gangotri-expeditions' ),
	),
	array(
		'image'   => 'hero-2',
		'alt'     => __( 'The ridge trail to Chandrashila summit above Chopta', 'gangotri-expeditions' ),
		'icon'    => 'footprints',
		'eyebrow' => __( 'Weekend Treks', 'gangotri-expeditions' ),
		'title'   => __( 'Trails most people<br>never walk.', 'gangotri-expeditions' ),
		'text'    => __( 'Panch Kedar temples and Garhwal ridges that stay quiet even in season - walked at a pace that leaves room to look around.', 'gangotri-expeditions' ),
		'cta'     => __( 'See Packages', 'gangotri-expeditions' ),
	),
	array(
		'image'   => 'hero-3',
		'alt'     => __( 'The Bhagirathi river below the Himalayan range at Gangotri', 'gangotri-expeditions' ),
		'icon'    => 'mountain-snow',
		'eyebrow' => __( 'Small Groups', 'gangotri-expeditions' ),
		'title'   => __( 'Where the Ganga<br>Begins.', 'gangotri-expeditions' ),
		'text'    => __( 'Gangotri, Yamunotri and everything between - planned at a pace that leaves room to actually be there.', 'gangotri-expeditions' ),
		'cta'     => __( 'All Journeys', 'gangotri-expeditions' ),
	),
);
?>

<section class="relative" aria-label="<?php esc_attr_e( 'Featured journeys', 'gangotri-expeditions' ); ?>">
	<div class="slider" data-slider data-per-view="1" data-autoplay="6000" data-loop>
		<div class="slider-viewport">
			<ul class="slider-track">

				<?php foreach ( $ge_slides as $ge_i => $ge_slide ) : ?>
					<li class="slider-slide w-full relative">
						<div class="hero-media">
							<?php
							$ge_base = GE_URI . '/assets/img/hero/' . $ge_slide['image'];
							$ge_lcp  = 0 === $ge_i;
							?>
							<img src="<?php echo esc_url( $ge_base . '.webp' ); ?>"
							     srcset="<?php echo esc_attr( sprintf(
								     '%1$s-960w.webp 960w, %1$s-1440w.webp 1440w, %1$s.webp 1920w',
								     $ge_base
							     ) ); ?>"
							     sizes="100vw"
							     alt="<?php echo esc_attr( $ge_slide['alt'] ); ?>"
							     width="1920" height="1080"
							     <?php echo $ge_lcp ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
							     decoding="async">
						</div>
						<div class="hero-overlay"></div>
						<div class="hero-content">
							<span class="eyebrow text-gold-400">
								<?php ge_icon( $ge_slide['icon'] ); ?>
								<?php echo esc_html( $ge_slide['eyebrow'] ); ?>
							</span>

							<?php
							// Only the first slide is the page's h1; the rest are
							// styled the same but must not add extra top-level
							// headings to the outline.
							$ge_tag = $ge_lcp ? 'h1' : 'p';
							printf(
								'<%1$s class="hero-title">%2$s</%1$s>',
								esc_attr( $ge_tag ),
								wp_kses( $ge_slide['title'], array( 'br' => array() ) )
							);
							?>

							<p class="hero-text"><?php echo esc_html( $ge_slide['text'] ); ?></p>

							<div class="hero-actions">
								<a class="btn btn-gold btn-lg" href="<?php echo esc_url( ge_yatras_url() ); ?>">
									<?php echo esc_html( $ge_slide['cta'] ); ?> <?php ge_icon( 'arrow-right' ); ?>
								</a>
								<?php if ( $ge_lcp ) : ?>
									<a class="btn btn-ghost-light btn-lg" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
										<?php ge_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Enquire Now', 'gangotri-expeditions' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>

			</ul>
		</div>

		<div class="hidden md:flex absolute bottom-8 right-8 z-20 gap-2">
			<button type="button" class="slider-arrow bg-white/15 border-white/30 text-white backdrop-blur-sm hover:bg-white hover:text-brand-700"
			        data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'gangotri-expeditions' ); ?>">
				<?php ge_icon( 'chevron-left' ); ?>
			</button>
			<button type="button" class="slider-arrow bg-white/15 border-white/30 text-white backdrop-blur-sm hover:bg-white hover:text-brand-700"
			        data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'gangotri-expeditions' ); ?>">
				<?php ge_icon( 'chevron-right' ); ?>
			</button>
		</div>

		<div class="slider-dots absolute bottom-8 left-1/2 -translate-x-1/2 z-20 md:left-8 md:translate-x-0" data-slider-dots></div>
	</div>
</section>

<?php /* ---------------------------------------------------------- trust -- */ ?>

<section class="section pt-10 md:pt-14" aria-label="<?php esc_attr_e( 'Why travel with us', 'gangotri-expeditions' ); ?>">
	<div class="container-page">
		<ul class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8">
			<?php
			$ge_trust = array(
				array( 'compass', __( 'Expert Guides', 'gangotri-expeditions' ), __( 'Local leaders, certified in high-altitude first aid.', 'gangotri-expeditions' ) ),
				array( 'trishul', __( 'Spiritual Experience', 'gangotri-expeditions' ), __( 'Darshan timed to avoid the crush, priests arranged.', 'gangotri-expeditions' ) ),
				array( 'bed', __( 'Comfortable Stays', 'gangotri-expeditions' ), __( 'Vetted hotels and camps, never a last-minute room.', 'gangotri-expeditions' ) ),
				array( 'shield-check', __( 'Safe & Memorable', 'gangotri-expeditions' ), __( 'Oxygen, medical kit and evacuation plan on every trip.', 'gangotri-expeditions' ) ),
			);

			foreach ( $ge_trust as list( $ge_icon, $ge_head, $ge_copy ) ) :
				?>
				<li class="flex flex-col items-center text-center gap-3" data-reveal>
					<span class="trust-icon"><?php ge_icon( $ge_icon ); ?></span>
					<h2 class="text-base"><?php echo esc_html( $ge_head ); ?></h2>
					<p class="text-sm text-ink/65"><?php echo esc_html( $ge_copy ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php /* -------------------------------------------------------- packages -- */ ?>

<?php
$ge_packages = new WP_Query( array(
	'post_type'      => 'yatra',
	'posts_per_page' => 6,
	'meta_key'       => ge_meta_key( 'featured' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	'orderby'        => array( 'meta_value' => 'DESC', 'menu_order' => 'ASC', 'title' => 'ASC' ),
	'no_found_rows'  => true,
) );

if ( $ge_packages->have_posts() ) :
	?>
	<section class="section bg-mist" aria-labelledby="popular-yatras">
		<div class="container-page">
			<div class="flex flex-wrap items-end justify-between gap-4 mb-10">
				<div class="max-w-2xl">
					<span class="eyebrow">
						<?php ge_icon( 'route' ); ?> <?php esc_html_e( 'Popular Yatras', 'gangotri-expeditions' ); ?>
					</span>
					<h2 id="popular-yatras" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Our Packages', 'gangotri-expeditions' ); ?></h2>
					<p class="mt-3 text-ink/70 leading-relaxed">
						<?php esc_html_e( 'Fixed departures with published itineraries, real altitudes and honest difficulty ratings - so you know what you are signing up for before you pay.', 'gangotri-expeditions' ); ?>
					</p>
				</div>
				<a class="btn btn-outline" href="<?php echo esc_url( ge_yatras_url() ); ?>">
					<?php esc_html_e( 'View All Yatras', 'gangotri-expeditions' ); ?> <?php ge_icon( 'arrow-right' ); ?>
				</a>
			</div>

			<div class="card-grid">
				<?php
				while ( $ge_packages->have_posts() ) :
					$ge_packages->the_post();
					get_template_part( 'parts/yatra-card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php /* ---------------------------------------------------------- why us -- */ ?>

<section class="section" aria-labelledby="why-us">
	<div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
		<div class="grid grid-cols-2 gap-4">
			<div class="media-4-3 rounded-card row-span-2 h-full">
				<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/gallery-2.webp' ); ?>"
				     alt="<?php esc_attr_e( 'Trekkers crossing a Himalayan trail in Uttarakhand', 'gangotri-expeditions' ); ?>"
				     width="800" height="800" loading="lazy" decoding="async">
			</div>
			<div class="media-4-3 rounded-card">
				<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/gallery-4.webp' ); ?>"
				     alt="<?php esc_attr_e( 'Deoria Tal reflecting the surrounding peaks', 'gangotri-expeditions' ); ?>"
				     width="800" height="800" loading="lazy" decoding="async">
			</div>
			<div class="media-4-3 rounded-card">
				<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/gallery-6.webp' ); ?>"
				     alt="<?php esc_attr_e( 'Snow-covered slopes at Auli in Uttarakhand', 'gangotri-expeditions' ); ?>"
				     width="800" height="800" loading="lazy" decoding="async">
			</div>
		</div>

		<div data-reveal>
			<span class="eyebrow">
				<?php ge_icon( 'mountain' ); ?> <?php esc_html_e( 'Why Gangotri Expeditions', 'gangotri-expeditions' ); ?>
			</span>
			<h2 id="why-us" class="text-3xl lg:text-4xl mt-3">
				<?php esc_html_e( 'We live here. That is the whole difference.', 'gangotri-expeditions' ); ?>
			</h2>
			<p class="mt-4 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Our team is based in Uttarakhand, not in a call centre. We know which stretch of the Kedarnath trail turns slippery after rain, which dhaba is worth stopping at, and when the darshan queue actually thins out.', 'gangotri-expeditions' ); ?>
			</p>

			<ul class="mt-8 space-y-5">
				<?php
				$ge_reasons = array(
					array( 'badge-check', __( 'No hidden costs', 'gangotri-expeditions' ), __( 'Inclusions and exclusions are listed in full on every itinerary page. The price you see is the price you pay.', 'gangotri-expeditions' ) ),
					array( 'users', __( 'Small groups', 'gangotri-expeditions' ), __( 'Capped at 15 people, so nobody is left behind on a climb and everyone gets a seat at dinner.', 'gangotri-expeditions' ) ),
					array( 'shield-check', __( 'Altitude taken seriously', 'gangotri-expeditions' ), __( 'Acclimatisation days are built into the schedule, and every group carries oxygen and a pulse oximeter.', 'gangotri-expeditions' ) ),
				);

				foreach ( $ge_reasons as list( $ge_icon, $ge_head, $ge_copy ) ) :
					?>
					<li class="flex gap-4">
						<span class="feature-icon"><?php ge_icon( $ge_icon ); ?></span>
						<div>
							<h3 class="text-base"><?php echo esc_html( $ge_head ); ?></h3>
							<p class="mt-1 text-sm text-ink/65"><?php echo esc_html( $ge_copy ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<dl class="mt-10 grid grid-cols-3 gap-4 border-t border-brand-100 pt-6">
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Yatris hosted', 'gangotri-expeditions' ); ?></dt><dd class="stat">8,500+</dd></div>
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Years running', 'gangotri-expeditions' ); ?></dt><dd class="stat">12</dd></div>
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Repeat guests', 'gangotri-expeditions' ); ?></dt><dd class="stat">40%</dd></div>
			</dl>
		</div>
	</div>
</section>

<?php /* --------------------------------------------------------- gallery -- */ ?>

<section class="section bg-mist" aria-labelledby="gallery-preview">
	<div class="container-page">
		<div class="flex flex-wrap items-end justify-between gap-4 mb-10">
			<div class="max-w-2xl">
				<span class="eyebrow"><?php ge_icon( 'camera' ); ?> <?php esc_html_e( 'Gallery', 'gangotri-expeditions' ); ?></span>
				<h2 id="gallery-preview" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'From our recent departures', 'gangotri-expeditions' ); ?></h2>
			</div>
			<a class="btn btn-outline" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">
				<?php esc_html_e( 'Open Gallery', 'gangotri-expeditions' ); ?> <?php ge_icon( 'arrow-right' ); ?>
			</a>
		</div>

		<ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
			<?php
			$ge_gallery = array(
				1 => __( 'Yamunotri temple in the Garhwal Himalaya', 'gangotri-expeditions' ),
				2 => __( 'A trekking group on a Himalayan trail', 'gangotri-expeditions' ),
				3 => __( 'The Ganga at Rishikesh', 'gangotri-expeditions' ),
				4 => __( 'Deoria Tal at first light', 'gangotri-expeditions' ),
				5 => __( 'Nanda Devi peak', 'gangotri-expeditions' ),
				6 => __( 'Snow at Auli', 'gangotri-expeditions' ),
			);

			foreach ( $ge_gallery as $ge_n => $ge_alt ) :
				?>
				<li class="media-4-3 aspect-square rounded-card">
					<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/gallery-' . $ge_n . '.webp' ); ?>"
					     alt="<?php echo esc_attr( $ge_alt ); ?>"
					     width="800" height="800" loading="lazy" decoding="async">
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php /* --------------------------------------------------------- reviews -- */ ?>

<section class="section" aria-labelledby="reviews">
	<div class="container-page">
		<div class="max-w-2xl mb-10">
			<span class="eyebrow"><?php ge_icon( 'quote' ); ?> <?php esc_html_e( 'Yatri Stories', 'gangotri-expeditions' ); ?></span>
			<h2 id="reviews" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'What our travellers say', 'gangotri-expeditions' ); ?></h2>
		</div>

		<div class="slider" data-slider data-per-view="1" data-per-view-md="2" data-per-view-lg="3" data-gap="24" data-loop>
			<div class="slider-viewport">
				<ul class="slider-track">
					<?php
					$ge_reviews = array(
						array(
							'quote' => __( 'My parents are both over sixty and I was nervous about Kedarnath. The team paced the whole climb around them and arranged a palki without me even asking twice.', 'gangotri-expeditions' ),
							'name'  => 'Anjali Sharma',
							'trip'  => __( 'Kedarnath Yatra, June 2025', 'gangotri-expeditions' ),
						),
						array(
							'quote' => __( 'Third trek with them. What I appreciate is that nothing is oversold - the difficulty rating on the site is exactly what you get on the ground.', 'gangotri-expeditions' ),
							'name'  => 'Rohit Menon',
							'trip'  => __( 'Har Ki Dun Trek, May 2025', 'gangotri-expeditions' ),
						),
						array(
							'quote' => __( 'Weather turned on us at Chandrashila and they called it off an hour from the top. Disappointing at the time, obviously right in hindsight.', 'gangotri-expeditions' ),
							'name'  => 'Priya Nair',
							'trip'  => __( 'Chopta - Tungnath, Feb 2025', 'gangotri-expeditions' ),
						),
						array(
							'quote' => __( 'Booked the full Char Dham circuit for eleven days. Hotels were clean, drivers were calm on those roads, and the rest days made all the difference.', 'gangotri-expeditions' ),
							'name'  => 'Suresh Iyer',
							'trip'  => __( 'Char Dham Yatra, Sept 2025', 'gangotri-expeditions' ),
						),
					);

					foreach ( $ge_reviews as $ge_review ) :
						?>
						<li class="slider-slide">
							<figure class="review-card">
								<div class="review-stars" aria-label="<?php esc_attr_e( 'Rated 5 out of 5', 'gangotri-expeditions' ); ?>">
									<?php
									for ( $ge_s = 0; $ge_s < 5; $ge_s++ ) {
										ge_icon( 'star' );
									}
									?>
								</div>
								<blockquote class="mt-4 text-sm leading-relaxed text-ink/75">
									<?php echo esc_html( $ge_review['quote'] ); ?>
								</blockquote>
								<figcaption class="review-by">
									<?php echo esc_html( $ge_review['name'] ); ?>
									<span><?php echo esc_html( $ge_review['trip'] ); ?></span>
								</figcaption>
							</figure>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="mt-8 flex items-center justify-between">
				<div class="slider-dots" data-slider-dots></div>
				<div class="flex gap-2">
					<button type="button" class="slider-arrow" data-slider-prev aria-label="<?php esc_attr_e( 'Previous reviews', 'gangotri-expeditions' ); ?>">
						<?php ge_icon( 'chevron-left' ); ?>
					</button>
					<button type="button" class="slider-arrow" data-slider-next aria-label="<?php esc_attr_e( 'Next reviews', 'gangotri-expeditions' ); ?>">
						<?php ge_icon( 'chevron-right' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</section>

<?php /* ------------------------------------------------------------- faq -- */ ?>

<section class="section bg-mist" aria-labelledby="faq">
	<div class="container-page grid gap-10 lg:grid-cols-[22rem_1fr] lg:gap-16">
		<div>
			<span class="eyebrow"><?php ge_icon( 'info' ); ?> <?php esc_html_e( 'FAQ', 'gangotri-expeditions' ); ?></span>
			<h2 id="faq" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Before you book', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-3 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Anything not covered here, message us on WhatsApp - a real person answers.', 'gangotri-expeditions' ); ?>
			</p>
			<a class="btn btn-primary mt-6" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
				<?php ge_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Ask a Question', 'gangotri-expeditions' ); ?>
			</a>
		</div>

		<div data-accordion data-accordion-single class="divide-y divide-brand-100 border-y border-brand-100">
			<?php
			$ge_faqs = array(
				__( 'What is the best time for the Char Dham Yatra?', 'gangotri-expeditions' )   => __( 'The portals open around late April or early May and close in the first week of November. May to June and September to October are the most comfortable. July and August bring heavy monsoon rain and a real risk of landslides on the Kedarnath and Badrinath routes.', 'gangotri-expeditions' ),
				__( 'How difficult is the Kedarnath trek?', 'gangotri-expeditions' )             => __( 'It is 16 km each way from Gaurikund, climbing to 3,583 m, and most people take 6 to 8 hours going up. It is a well-made stone path rather than technical terrain, so ordinary fitness is enough - but the altitude is real. Ponies, palkis and helicopter transfers can all be arranged in advance.', 'gangotri-expeditions' ),
				__( 'Do I need to register for the yatra?', 'gangotri-expeditions' )             => __( 'Yes. Uttarakhand requires every Char Dham traveller to carry a registration with a Yatra e-pass. We complete it for you once you send a photo ID, so there is nothing to queue for on arrival.', 'gangotri-expeditions' ),
				__( 'What is included in the package price?', 'gangotri-expeditions' )           => __( 'Accommodation, breakfast and dinner, all transfers in a private vehicle, a guide throughout, permits and registration, and first-aid and oxygen support. Airfare, lunches, pony or palki charges, and anything personal are not included - each itinerary page lists both sides in full.', 'gangotri-expeditions' ),
				__( 'Can senior citizens do the yatra?', 'gangotri-expeditions' )                => __( 'Regularly, yes. Badrinath, Gangotri and Yamunotri are largely road journeys. For Kedarnath we arrange a pony, palki or helicopter. We do ask anyone over 60, or with a heart or respiratory condition, to bring a doctor\'s clearance.', 'gangotri-expeditions' ),
			);

			$ge_n = 0;

			foreach ( $ge_faqs as $ge_question => $ge_answer ) :
				$ge_n++;
				?>
				<div>
					<h3>
						<button type="button" class="accordion-trigger" data-accordion-trigger
						        aria-expanded="false" aria-controls="faq-<?php echo (int) $ge_n; ?>">
							<span><?php echo esc_html( $ge_question ); ?></span>
							<?php ge_icon( 'chevron-down', 'accordion-chevron' ); ?>
						</button>
					</h3>
					<div class="accordion-panel" id="faq-<?php echo (int) $ge_n; ?>">
						<p class="accordion-body"><?php echo esc_html( $ge_answer ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php /* --------------------------------------------------------- enquiry -- */ ?>

<section class="section" id="enquiry" aria-labelledby="enquiry-heading">
	<div class="container-page grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-16 lg:items-start">

		<div>
			<span class="eyebrow"><?php ge_icon( 'send' ); ?> <?php esc_html_e( 'Plan your yatra', 'gangotri-expeditions' ); ?></span>
			<h2 id="enquiry-heading" class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Tell us your dates', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-4 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Send us who is travelling and roughly when. We will come back with an itinerary, a straight price, and an honest word on whether the route suits your group.', 'gangotri-expeditions' ); ?>
			</p>

			<ul class="mt-8 space-y-4">
				<?php
				$ge_promises = array(
					__( 'No advance needed to hold a seat while dates are confirmed', 'gangotri-expeditions' ),
					__( 'Custom itineraries for families and private groups', 'gangotri-expeditions' ),
					__( 'A real person replies, not an auto-responder', 'gangotri-expeditions' ),
				);

				foreach ( $ge_promises as $ge_promise ) :
					?>
					<li class="flex gap-3 text-sm text-ink/75">
						<?php ge_icon( 'circle-check-big', 'text-brand-600 mt-0.5' ); ?>
						<span><?php echo esc_html( $ge_promise ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="mt-8 pt-6 border-t border-brand-100 grid gap-3 text-sm">
				<a class="flex items-center gap-3 hover:text-brand-600" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
					<span class="feature-icon w-10 h-10 text-base"><?php ge_icon( 'phone' ); ?></span>
					<?php echo esc_html( ge_option( 'phone' ) ); ?>
				</a>
				<a class="flex items-center gap-3 hover:text-brand-600" href="mailto:<?php echo esc_attr( ge_option( 'email' ) ); ?>">
					<span class="feature-icon w-10 h-10 text-base"><?php ge_icon( 'mail' ); ?></span>
					<?php echo esc_html( ge_option( 'email' ) ); ?>
				</a>
			</div>
		</div>

		<div class="card p-6 lg:p-8">
			<?php get_template_part( 'parts/enquiry-form', null, array( 'prefix' => 'home' ) ); ?>
		</div>

	</div>
</section>

<?php
get_template_part( 'parts/cta-band', null, array(
	'title' => __( 'Planning a yatra for 2026?', 'gangotri-expeditions' ),
	'text'  => __( 'Tell us your dates and who is travelling. We will send back an itinerary and a straight price - usually within a few hours.', 'gangotri-expeditions' ),
) );

get_footer();
