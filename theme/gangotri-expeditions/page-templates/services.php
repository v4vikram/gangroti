<?php
/**
 * Template Name: Services
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

ge_page_head(
	get_the_title(),
	get_the_excerpt() ?: __( 'Everything we arrange around the journey itself - transport, stays, permits, guides and the help that makes a high-altitude trip safe.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
		get_the_title()                      => '',
	)
);
?>

<section class="section">
	<div class="container-page">

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose-ge max-w-3xl mb-10"><?php the_content(); ?></div>
		<?php endif; ?>

		<ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
			<?php
			$ge_services = array(
				array( 'bus', __( 'Private Transport', 'gangotri-expeditions' ), __( 'Innova, Tempo Traveller or bus depending on group size, with drivers who have run the Rishikesh - Kedarnath road for years. Pickup from Haridwar, Rishikesh, Dehradun or Jolly Grant airport.', 'gangotri-expeditions' ) ),
				array( 'bed', __( 'Stays & Camps', 'gangotri-expeditions' ), __( 'Hotels we have personally checked, plus fixed camps on the trekking routes. Rooms are blocked months ahead, so nobody ends up in whatever is left at 9pm in Sonprayag.', 'gangotri-expeditions' ) ),
				array( 'compass', __( 'Certified Guides', 'gangotri-expeditions' ), __( 'Local guides trained in high-altitude first aid, on every departure. They carry the oxygen and the oximeter, and they make the call on whether the group goes up.', 'gangotri-expeditions' ) ),
				array( 'ticket', __( 'Registration & Permits', 'gangotri-expeditions' ), __( 'Char Dham Yatra registration and the Uttarakhand e-pass, plus forest permits for Har Ki Dun and Valley of Flowers. Send a photo ID and we file everything.', 'gangotri-expeditions' ) ),
				array( 'route', __( 'Helicopter & Palki', 'gangotri-expeditions' ), __( 'Helicopter transfers to Kedarnath from Phata, Sirsi or Guptkashi, and pony or palki arrangements for anyone who should not be walking 16 km. Booked in advance, at the published rate.', 'gangotri-expeditions' ) ),
				array( 'shield-check', __( 'Medical Support', 'gangotri-expeditions' ), __( 'Oxygen cylinders, first-aid kits and an evacuation plan on every trip, with a standing arrangement with hospitals in Rudraprayag and Dehradun.', 'gangotri-expeditions' ) ),
			);

			foreach ( $ge_services as list( $ge_ic, $ge_head, $ge_copy ) ) :
				?>
				<li class="card p-6 h-full" data-reveal>
					<span class="feature-icon"><?php ge_icon( $ge_ic ); ?></span>
					<h2 class="text-lg mt-4"><?php echo esc_html( $ge_head ); ?></h2>
					<p class="mt-2 text-sm leading-relaxed text-ink/70"><?php echo esc_html( $ge_copy ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php // A comparison table: exactly the shape AI answer engines lift verbatim. ?>
<section class="section bg-mist">
	<div class="container-page">
		<div class="max-w-2xl mb-10">
			<span class="eyebrow"><?php ge_icon( 'indian-rupee' ); ?> <?php esc_html_e( 'What things cost', 'gangotri-expeditions' ); ?></span>
			<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Indicative add-on pricing', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-3 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Rates for 2026, per person unless stated. Government-set charges change year to year, so we confirm the exact figure when you book.', 'gangotri-expeditions' ); ?>
			</p>
		</div>

		<div class="table-wrap">
			<table class="data-table">
				<caption class="sr-only"><?php esc_html_e( 'Indicative add-on service pricing for 2026', 'gangotri-expeditions' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Service', 'gangotri-expeditions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Route', 'gangotri-expeditions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Indicative cost', 'gangotri-expeditions' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$ge_rates = array(
						array( __( 'Helicopter transfer', 'gangotri-expeditions' ), __( 'Phata to Kedarnath, return', 'gangotri-expeditions' ), '&#8377; 7,500 - 8,500' ),
						array( __( 'Pony / horse', 'gangotri-expeditions' ), __( 'Gaurikund to Kedarnath, return', 'gangotri-expeditions' ), '&#8377; 4,000 - 5,000' ),
						array( __( 'Palki (4 bearers)', 'gangotri-expeditions' ), __( 'Gaurikund to Kedarnath, return', 'gangotri-expeditions' ), '&#8377; 9,000 - 11,000' ),
						array( __( 'Airport pickup', 'gangotri-expeditions' ), __( 'Jolly Grant to Haridwar', 'gangotri-expeditions' ), __( '&#8377; 1,800 per vehicle', 'gangotri-expeditions' ) ),
						array( __( 'Extra hotel night', 'gangotri-expeditions' ), __( 'Haridwar or Rishikesh', 'gangotri-expeditions' ), __( '&#8377; 1,500 - 3,000 per room', 'gangotri-expeditions' ) ),
					);

					foreach ( $ge_rates as list( $ge_service, $ge_route, $ge_cost ) ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $ge_service ); ?></th>
							<td><?php echo esc_html( $ge_route ); ?></td>
							<td><?php echo wp_kses( $ge_cost, array() ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>

<?php
get_template_part( 'parts/cta-band', null, array(
	'title' => __( 'Need something not on this list?', 'gangotri-expeditions' ),
	'text'  => __( 'Tell us what you are planning. If we cannot arrange it ourselves we will say so, and point you at someone who can.', 'gangotri-expeditions' ),
) );

get_footer();
