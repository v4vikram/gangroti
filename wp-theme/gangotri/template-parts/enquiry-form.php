<?php
/**
 * The enquiry form.
 *
 * Converted from src/partials/enquiry-form.html. Field names are unchanged, so
 * inc/enquiry.php reads exactly what the static build was already sending.
 *
 * Pass a prefix so ids stay unique when the form appears twice on one page
 * (inline and inside the popup):
 *
 *   get_template_part( 'template-parts/enquiry-form', null, array( 'prefix' => 'home' ) );
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$prefix = isset( $args['prefix'] ) ? sanitize_key( (string) $args['prefix'] ) : 'form';
?>

<form class="grid gap-4 sm:grid-cols-2" data-enquiry-form novalidate>

	<?php // Honeypot: hidden from people, irresistible to bots. ?>
	<div class="hp-field" aria-hidden="true">
		<label for="<?php echo esc_attr( $prefix ); ?>-company"><?php esc_html_e( 'Company', 'gangotri' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $prefix ); ?>-company" name="company" tabindex="-1" autocomplete="off">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-name">
			<?php esc_html_e( 'Your name', 'gangotri' ); ?> <span class="req">*</span>
		</label>
		<input class="field" type="text" id="<?php echo esc_attr( $prefix ); ?>-name" name="name" required
		       autocomplete="name" placeholder="<?php esc_attr_e( 'Ramesh Kumar', 'gangotri' ); ?>">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-phone">
			<?php esc_html_e( 'Phone / WhatsApp', 'gangotri' ); ?> <span class="req">*</span>
		</label>
		<input class="field" type="tel" id="<?php echo esc_attr( $prefix ); ?>-phone" name="phone" required
		       autocomplete="tel" inputmode="tel" pattern="[0-9+ ()-]{7,}" placeholder="+91 98765 43210">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-email"><?php esc_html_e( 'Email', 'gangotri' ); ?></label>
		<input class="field" type="email" id="<?php echo esc_attr( $prefix ); ?>-email" name="email"
		       autocomplete="email" placeholder="you@example.com">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-yatra"><?php esc_html_e( 'Which package?', 'gangotri' ); ?></label>
		<select class="field" id="<?php echo esc_attr( $prefix ); ?>-yatra" name="yatra">
			<option value=""><?php esc_html_e( 'Not decided yet', 'gangotri' ); ?></option>
			<?php
			// Generated from the packages that exist, so the dropdown can never
			// offer something that has been unpublished.
			foreach ( get_posts( array( 'post_type' => 'yatra', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $gangotri_option_post ) {
				printf( '<option>%s</option>', esc_html( get_the_title( $gangotri_option_post ) ) );
			}
			?>
			<option><?php esc_html_e( 'Custom itinerary', 'gangotri' ); ?></option>
		</select>
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-travellers"><?php esc_html_e( 'Travellers', 'gangotri' ); ?></label>
		<input class="field" type="number" id="<?php echo esc_attr( $prefix ); ?>-travellers" name="travellers"
		       min="1" max="60" placeholder="4">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-date"><?php esc_html_e( 'Preferred start date', 'gangotri' ); ?></label>
		<input class="field" type="date" id="<?php echo esc_attr( $prefix ); ?>-date" name="date">
	</div>

	<div class="sm:col-span-2">
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-message"><?php esc_html_e( 'Anything we should know?', 'gangotri' ); ?></label>
		<textarea class="field" id="<?php echo esc_attr( $prefix ); ?>-message" name="message" rows="3"
		          placeholder="<?php esc_attr_e( 'Travelling with parents, need a helicopter to Kedarnath...', 'gangotri' ); ?>"></textarea>
	</div>

	<p class="sm:col-span-2 form-status" data-form-status hidden></p>

	<div class="sm:col-span-2 flex flex-wrap gap-3">
		<button class="btn btn-gold btn-lg" type="submit">
			<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'Send Enquiry', 'gangotri' ); ?>
		</button>

		<a class="btn btn-outline btn-lg" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
			<?php echo gangotri_icon( 'phone-call' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
		</a>
	</div>

	<p class="sm:col-span-2 text-xs leading-relaxed text-ink/55">
		<?php esc_html_e( 'We reply within a few hours, usually sooner. Your details are only used to answer this enquiry', 'gangotri' ); ?>
		<?php
		$gangotri_privacy = get_page_by_path( 'privacy' );
		if ( $gangotri_privacy ) :
			?>
			&mdash; <?php esc_html_e( 'see our', 'gangotri' ); ?>
			<a class="underline underline-offset-2" href="<?php echo esc_url( (string) get_permalink( $gangotri_privacy ) ); ?>">
				<?php esc_html_e( 'privacy policy', 'gangotri' ); ?></a>.
		<?php else : ?>.<?php endif; ?>
	</p>
</form>
