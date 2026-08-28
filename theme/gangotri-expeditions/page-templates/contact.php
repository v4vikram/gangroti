<?php
/**
 * Template Name: Contact
 *
 * Assign this to the Contact page in Page Attributes.
 *
 * The static build carried its own copy of the enquiry form here, with the
 * package list typed in by hand - which is how it ended up offering six
 * packages that no longer existed. It uses the shared part now, so the
 * dropdown is generated from the CPT and there is one form to maintain.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

ge_page_head(
	get_the_title(),
	get_the_excerpt() ?: __( 'Tell us your dates and who is travelling. We usually reply within a few hours with a suggested itinerary and a straight price.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
		get_the_title()                      => '',
	)
);
?>

<section class="section">
	<div class="container-page grid gap-10 lg:grid-cols-[1fr_22rem] lg:gap-14">

		<div>
			<h2 class="text-2xl lg:text-3xl"><?php esc_html_e( 'Send an enquiry', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-2 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'No obligation, and we will not add you to a mailing list.', 'gangotri-expeditions' ); ?>
			</p>

			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose-ge mt-6"><?php the_content(); ?></div>
			<?php endif; ?>

			<div class="mt-8">
				<?php get_template_part( 'parts/enquiry-form', null, array( 'prefix' => 'contact' ) ); ?>
			</div>
		</div>

		<aside class="grid gap-4 content-start">
			<?php
			$ge_channels = array(
				array(
					'href'  => 'tel:' . ge_option( 'phone_raw' ),
					'icon'  => 'phone-call',
					'title' => __( 'Call us', 'gangotri-expeditions' ),
					'value' => ge_option( 'phone' ),
					'note'  => __( 'Daily, 7am - 9pm IST', 'gangotri-expeditions' ),
				),
				array(
					'href'  => ge_whatsapp_url(),
					'icon'  => 'whatsapp',
					'title' => __( 'WhatsApp', 'gangotri-expeditions' ),
					'value' => ge_option( 'phone' ),
					'note'  => __( 'Fastest way to reach us', 'gangotri-expeditions' ),
				),
				array(
					'href'  => 'mailto:' . ge_option( 'email' ),
					'icon'  => 'mail',
					'title' => __( 'Email', 'gangotri-expeditions' ),
					'value' => ge_option( 'email' ),
					'note'  => __( 'Replies within a day', 'gangotri-expeditions' ),
				),
				array(
					'href'  => '',
					'icon'  => 'map-pin',
					'title' => __( 'Office', 'gangotri-expeditions' ),
					'value' => ge_option( 'address' ),
					'note'  => __( 'Visits by appointment', 'gangotri-expeditions' ),
				),
			);

			foreach ( $ge_channels as $ge_channel ) :
				$ge_tag  = $ge_channel['href'] ? 'a' : 'div';
				$ge_attr = $ge_channel['href']
					? ' href="' . esc_url( $ge_channel['href'] ) . '" rel="noopener"'
					: ' class="!cursor-default"';
				?>
				<<?php echo esc_html( $ge_tag ); ?> class="contact-card"<?php echo $ge_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above. ?>>
					<span class="feature-icon"><?php ge_icon( $ge_channel['icon'] ); ?></span>
					<span>
						<strong><?php echo esc_html( $ge_channel['title'] ); ?></strong>
						<?php echo esc_html( $ge_channel['value'] ); ?>
						<small><?php echo esc_html( $ge_channel['note'] ); ?></small>
					</span>
				</<?php echo esc_html( $ge_tag ); ?>>
			<?php endforeach; ?>
		</aside>

	</div>
</section>

<?php
// Map loads only on click: a live embed costs the best part of a megabyte and
// would sink the LCP for a page most people scroll past.
$ge_map_query = ge_option( 'locality' ) . ',' . ge_option( 'region' );
?>
<section class="pb-16 lg:pb-20">
	<div class="container-page">
		<div class="map-embed" data-map
		     data-src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( $ge_map_query ) . '&output=embed' ); ?>">
			<img src="<?php echo esc_url( GE_URI . '/assets/img/gallery/gallery-3.webp' ); ?>" alt=""
			     width="800" height="800" loading="lazy" decoding="async">
			<button type="button" class="btn btn-primary btn-lg" data-map-load>
				<?php ge_icon( 'map-pin' ); ?> <?php esc_html_e( 'Load map', 'gangotri-expeditions' ); ?>
			</button>
		</div>
	</div>
</section>

<?php
get_footer();
