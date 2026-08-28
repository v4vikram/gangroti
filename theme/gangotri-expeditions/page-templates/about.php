<?php
/**
 * Template Name: About
 *
 * The intro prose comes from the editor, so the client can rewrite the story
 * without a deploy. The three designed blocks below it - the figures, the four
 * pillars and the team - are laid out here because their shape is design, not
 * copy: they only work as a grid of equal cards, which is not something a
 * plain editor can be trusted to keep.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

ge_page_head(
	get_the_title(),
	get_the_excerpt(),
	array(
		__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
		get_the_title()                      => '',
	)
);
?>

<section class="section">
	<div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
		<div>
			<span class="eyebrow"><?php ge_icon( 'mountain' ); ?> <?php esc_html_e( 'Who we are', 'gangotri-expeditions' ); ?></span>
			<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Twelve years, one mountain range', 'gangotri-expeditions' ); ?></h2>

			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose-ge mt-4"><?php the_content(); ?></div>
			<?php else : ?>
				<p class="mt-4 text-ink/70 leading-relaxed">
					<?php esc_html_e( 'Gangotri Expeditions began in 2014 with two guides and one shared jeep. Today we host more than 8,500 travellers a year across the Char Dham circuit and the trekking routes of Garhwal - Chopta, Har Ki Dun, Valley of Flowers, Deoria Tal and Auli.', 'gangotri-expeditions' ); ?>
				</p>
				<p class="mt-4 text-ink/70 leading-relaxed">
					<?php esc_html_e( 'What has not changed is who runs the trips. Our team is from Rudraprayag, Uttarkashi and Rishikesh. They know which stretch of trail turns treacherous after rain, which tea shop is worth the stop, and when the darshan queue actually moves.', 'gangotri-expeditions' ); ?>
				</p>
			<?php endif; ?>

			<dl class="mt-10 grid grid-cols-3 gap-4 border-t border-brand-100 pt-6">
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Founded', 'gangotri-expeditions' ); ?></dt><dd class="stat">2014</dd></div>
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Yatris hosted', 'gangotri-expeditions' ); ?></dt><dd class="stat">8,500+</dd></div>
				<div><dt class="text-sm text-ink/60"><?php esc_html_e( 'Guides on staff', 'gangotri-expeditions' ); ?></dt><dd class="stat">24</dd></div>
			</dl>
		</div>

		<div class="media-4-3 rounded-card">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'ge-hero', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( GE_URI . '/assets/img/hero/hero-3.webp' ); ?>"
				     alt="<?php esc_attr_e( 'The Bhagirathi valley in the Garhwal Himalaya', 'gangotri-expeditions' ); ?>"
				     width="1920" height="1080" loading="lazy" decoding="async">
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section bg-mist">
	<div class="container-page">
		<div class="max-w-2xl mb-10">
			<span class="eyebrow"><?php ge_icon( 'heart' ); ?> <?php esc_html_e( 'How we work', 'gangotri-expeditions' ); ?></span>
			<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Four things we refuse to compromise on', 'gangotri-expeditions' ); ?></h2>
		</div>

		<ul class="grid gap-6 md:grid-cols-2">
			<?php
			$ge_pillars = array(
				array( 'shield-check', __( 'Safety over the summit', 'gangotri-expeditions' ), __( 'Every group carries oxygen, a pulse oximeter and a stocked medical kit, and every guide is certified in high-altitude first aid. If the weather turns, we turn back - and we have done it often enough to mean it.', 'gangotri-expeditions' ) ),
				array( 'users', __( 'Groups capped at 15', 'gangotri-expeditions' ), __( 'Small enough that the slowest walker is never alone at the back, and that a guide can actually answer your questions instead of shouting instructions at a crowd.', 'gangotri-expeditions' ) ),
				array( 'badge-check', __( 'Prices you can check', 'gangotri-expeditions' ), __( 'Inclusions and exclusions are published in full on every itinerary. There is no "local charge" waiting for you at Gaurikund, and no surcharge invented at the hotel.', 'gangotri-expeditions' ) ),
				array( 'footprints', __( 'Leave the trail cleaner', 'gangotri-expeditions' ), __( 'We carry our waste out, refill rather than buy bottled water where we can, and hire porters and drivers from the villages the route passes through.', 'gangotri-expeditions' ) ),
			);

			foreach ( $ge_pillars as list( $ge_ic, $ge_head, $ge_copy ) ) :
				?>
				<li class="card p-6" data-reveal>
					<span class="feature-icon"><?php ge_icon( $ge_ic ); ?></span>
					<h3 class="text-lg mt-4"><?php echo esc_html( $ge_head ); ?></h3>
					<p class="mt-2 text-sm leading-relaxed text-ink/70"><?php echo esc_html( $ge_copy ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section">
	<div class="container-page">
		<div class="max-w-2xl mb-10">
			<span class="eyebrow"><?php ge_icon( 'compass' ); ?> <?php esc_html_e( 'The team', 'gangotri-expeditions' ); ?></span>
			<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'People you will actually meet', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-3 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'No call centre in between. The person who plans your yatra is the person you can call from the trail.', 'gangotri-expeditions' ); ?>
			</p>
		</div>

		<ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			$ge_team = array(
				array( 'gallery-2', 'Mahesh Negi', __( 'Founder & Lead Guide', 'gangotri-expeditions' ), __( 'Born in Guptkashi. Has walked the Kedarnath route more than 300 times, including during the 2013 rebuild.', 'gangotri-expeditions' ) ),
				array( 'gallery-4', 'Sunita Rawat', __( 'Operations', 'gangotri-expeditions' ), __( 'Handles permits, e-passes and hotels. If your registration is sorted before you land, that was her.', 'gangotri-expeditions' ) ),
				array( 'gallery-6', 'Deepak Bisht', __( 'Trek Leader', 'gangotri-expeditions' ), __( 'Wilderness first responder. Leads the Har Ki Dun and Valley of Flowers departures through the monsoon months.', 'gangotri-expeditions' ) ),
			);

			foreach ( $ge_team as list( $ge_img, $ge_name, $ge_role, $ge_bio ) ) :
				?>
				<li class="card overflow-hidden" data-reveal>
					<div class="media-4-3">
						<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/' . $ge_img . '.webp' ); ?>" alt=""
						     width="800" height="800" loading="lazy" decoding="async">
					</div>
					<div class="p-5">
						<h3 class="text-base"><?php echo esc_html( $ge_name ); ?></h3>
						<p class="text-sm text-brand-600 font-medium"><?php echo esc_html( $ge_role ); ?></p>
						<p class="mt-2 text-sm text-ink/65 leading-relaxed"><?php echo esc_html( $ge_bio ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php
get_template_part( 'parts/cta-band' );
get_footer();
