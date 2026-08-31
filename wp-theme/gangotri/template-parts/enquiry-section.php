<?php
/**
 * The inline enquiry block.
 *
 * Shared by the home page and the contact page, so the form and its supporting
 * copy exist once rather than in two templates that drift apart.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$heading = isset( $args['heading'] ) ? (string) $args['heading'] : __( 'Tell us your dates', 'gangotri' );
$prefix  = isset( $args['prefix'] ) ? (string) $args['prefix'] : 'inline';
?>

<section class="section" id="enquiry" aria-labelledby="enquiry-heading">
	<div class="container-page grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-16 lg:items-start">

		<div>
			<span class="eyebrow">
				<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Plan your yatra', 'gangotri' ); ?>
			</span>

			<h2 id="enquiry-heading" class="text-3xl lg:text-4xl mt-3"><?php echo esc_html( $heading ); ?></h2>

			<p class="mt-4 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Send us who is travelling and roughly when. We will come back with an itinerary, a straight price, and an honest word on whether the route suits your group.', 'gangotri' ); ?>
			</p>

			<ul class="mt-8 space-y-4">
				<?php
				$points = array(
					__( 'No advance needed to hold a seat while dates are confirmed', 'gangotri' ),
					__( 'Custom itineraries for families and private groups', 'gangotri' ),
					__( 'A real person replies, not an auto-responder', 'gangotri' ),
				);

				foreach ( $points as $point ) :
					?>
					<li class="flex gap-3 text-sm text-ink/75">
						<?php echo gangotri_icon( 'circle-check-big', 'icon text-brand-600 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $point ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="mt-8 pt-6 border-t border-brand-100 grid gap-3 text-sm">
				<a class="flex items-center gap-3 hover:text-brand-600" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
					<span class="feature-icon w-10 h-10 text-base"><?php echo gangotri_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
				</a>
				<a class="flex items-center gap-3 hover:text-brand-600" href="mailto:<?php echo esc_attr( gangotri_option( 'email' ) ); ?>">
					<span class="feature-icon w-10 h-10 text-base"><?php echo gangotri_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php echo esc_html( gangotri_option( 'email' ) ); ?>
				</a>
			</div>
		</div>

		<div class="card p-6 lg:p-8">
			<?php get_template_part( 'template-parts/enquiry-form', null, array( 'prefix' => $prefix ) ); ?>
		</div>

	</div>
</section>
