<?php
/**
 * Enquiry form.
 *
 * Pass a `prefix` so the ids stay unique when the form appears twice on one
 * page - the contact page carries it inline and the popup carries it again. A
 * duplicated id sends a <label for> to the wrong input, and a screen reader
 * follows the label.
 *
 *   get_template_part( 'parts/enquiry-form', null, array( 'prefix' => 'home' ) );
 *
 * The field names are the ones inc/enquiry.php reads out of $_POST; they have
 * not changed since the static build, which is why src/js/form.js needed no
 * edit to talk to WordPress.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

$prefix = isset( $args['prefix'] ) ? sanitize_key( $args['prefix'] ) : 'enquiry';
?>

<form class="grid gap-4 sm:grid-cols-2" data-enquiry-form novalidate>

	<?php
	// FormData picks these up with the rest of the fields, so the JS needs no
	// knowledge of them.
	wp_nonce_field( 'ge_enquiry', 'ge_nonce', false );
	?>
	<input type="hidden" name="action" value="ge_enquiry">
	<input type="hidden" name="source" value="<?php echo esc_attr( get_the_title() ?: 'Site' ); ?>">

	<!-- Honeypot: hidden from people, irresistible to bots. -->
	<div class="hp-field" aria-hidden="true">
		<label for="<?php echo esc_attr( $prefix ); ?>-company"><?php esc_html_e( 'Company', 'gangotri-expeditions' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $prefix ); ?>-company" name="company" tabindex="-1" autocomplete="off">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-name">
			<?php esc_html_e( 'Your name', 'gangotri-expeditions' ); ?> <span class="req">*</span>
		</label>
		<input class="field" type="text" id="<?php echo esc_attr( $prefix ); ?>-name" name="name" required
		       autocomplete="name" placeholder="<?php esc_attr_e( 'Ramesh Kumar', 'gangotri-expeditions' ); ?>">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-phone">
			<?php esc_html_e( 'Phone / WhatsApp', 'gangotri-expeditions' ); ?> <span class="req">*</span>
		</label>
		<input class="field" type="tel" id="<?php echo esc_attr( $prefix ); ?>-phone" name="phone" required
		       autocomplete="tel" inputmode="tel" pattern="[0-9+ ()-]{7,}" placeholder="+91 98765 43210">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-email"><?php esc_html_e( 'Email', 'gangotri-expeditions' ); ?></label>
		<input class="field" type="email" id="<?php echo esc_attr( $prefix ); ?>-email" name="email"
		       autocomplete="email" placeholder="you@example.com">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-yatra"><?php esc_html_e( 'Which yatra?', 'gangotri-expeditions' ); ?></label>
		<select class="field" id="<?php echo esc_attr( $prefix ); ?>-yatra" name="yatra">
			<option value=""><?php esc_html_e( 'Not decided yet', 'gangotri-expeditions' ); ?></option>
			<?php
			// Generated from the packages that exist, so the dropdown cannot
			// offer something nobody can book.
			$options = get_posts( array(
				'post_type'              => 'yatra',
				'posts_per_page'         => -1,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			) );

			foreach ( $options as $option ) {
				printf(
					'<option value="%1$s">%1$s</option>',
					esc_attr( get_the_title( $option ) )
				);
			}
			?>
			<option value="Custom itinerary"><?php esc_html_e( 'Custom itinerary', 'gangotri-expeditions' ); ?></option>
		</select>
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-travellers"><?php esc_html_e( 'Travellers', 'gangotri-expeditions' ); ?></label>
		<input class="field" type="number" id="<?php echo esc_attr( $prefix ); ?>-travellers" name="travellers"
		       min="1" max="60" placeholder="4">
	</div>

	<div>
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-date"><?php esc_html_e( 'Preferred start date', 'gangotri-expeditions' ); ?></label>
		<input class="field" type="date" id="<?php echo esc_attr( $prefix ); ?>-date" name="date">
	</div>

	<div class="sm:col-span-2">
		<label class="label" for="<?php echo esc_attr( $prefix ); ?>-message"><?php esc_html_e( 'Anything we should know?', 'gangotri-expeditions' ); ?></label>
		<textarea class="field" id="<?php echo esc_attr( $prefix ); ?>-message" name="message" rows="3"
		          placeholder="<?php esc_attr_e( 'Travelling with parents, need a helicopter to Kedarnath...', 'gangotri-expeditions' ); ?>"></textarea>
	</div>

	<p class="sm:col-span-2 form-status" data-form-status hidden></p>

	<div class="sm:col-span-2 flex flex-wrap gap-3">
		<button class="btn btn-gold btn-lg" type="submit">
			<?php ge_icon( 'send' ); ?>
			<?php esc_html_e( 'Send Enquiry', 'gangotri-expeditions' ); ?>
		</button>
		<a class="btn btn-outline btn-lg" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
			<?php ge_icon( 'phone-call' ); ?>
			<?php echo esc_html( ge_option( 'phone' ) ); ?>
		</a>
	</div>

	<p class="sm:col-span-2 text-xs leading-relaxed text-ink/55">
		<?php
		printf(
			/* translators: %s: link to the privacy policy. */
			esc_html__( 'We reply within a few hours, usually sooner. Your details are only used to answer this enquiry - see our %s.', 'gangotri-expeditions' ),
			sprintf(
				'<a class="underline underline-offset-2" href="%s">%s</a>',
				esc_url( home_url( '/privacy/' ) ),
				esc_html__( 'privacy policy', 'gangotri-expeditions' )
			)
		);
		?>
	</p>
</form>
