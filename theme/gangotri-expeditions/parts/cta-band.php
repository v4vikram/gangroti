<?php
/**
 * The green call-to-action band that closes most pages.
 *
 *   get_template_part( 'parts/cta-band', null, array(
 *       'title' => '...', 'text' => '...',
 *   ) );
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

$ge_title = $args['title'] ?? __( 'Not sure which one to pick?', 'gangotri-expeditions' );
$ge_text  = $args['text'] ?? __( 'Tell us who is travelling, how many days you have, and what you want out of it. We will suggest the right yatra - and say so if none of ours fits.', 'gangotri-expeditions' );
?>

<section class="cta-band">
	<div class="container-page text-center max-w-3xl">
		<p class="text-3xl lg:text-4xl font-heading font-bold text-white text-balance">
			<?php echo esc_html( $ge_title ); ?>
		</p>
		<p class="mt-4 text-brand-100 leading-relaxed">
			<?php echo esc_html( $ge_text ); ?>
		</p>
		<div class="mt-8 flex flex-wrap justify-center gap-3">
			<button type="button" class="btn btn-gold btn-lg" data-enquiry-open>
				<?php ge_icon( 'send' ); ?>
				<?php esc_html_e( 'Get a Free Quote', 'gangotri-expeditions' ); ?>
			</button>
			<a class="btn btn-ghost-light btn-lg" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
				<?php ge_icon( 'phone-call' ); ?>
				<?php echo esc_html( ge_option( 'phone' ) ); ?>
			</a>
		</div>
	</div>
</section>
