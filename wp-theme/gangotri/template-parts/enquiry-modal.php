<?php
/**
 * The enquiry popup, output once per page from footer.php.
 *
 * Built on <dialog>: the browser supplies the backdrop, focus trap, inert
 * background and Escape handling, so none of that is ours to maintain.
 *
 * The auto-open thresholds come from Theme Options rather than the markup, so
 * the client can retune them without a developer.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$auto_on = '1' === (string) gangotri_option( 'popup_enabled' );
?>

<dialog class="enquiry-modal" data-enquiry-modal aria-labelledby="modal-heading"
	<?php if ( $auto_on ) : ?>
		data-auto-delay="<?php echo esc_attr( (string) ( (int) gangotri_option( 'popup_delay' ) * 1000 ) ); ?>"
		data-auto-scroll="<?php echo esc_attr( (string) ( (int) gangotri_option( 'popup_scroll' ) / 100 ) ); ?>"
	<?php else : ?>
		data-auto-off
	<?php endif; ?>
>
	<div class="enquiry-modal-head">
		<div>
			<p class="eyebrow text-gold-400">
				<?php echo gangotri_icon( 'trishul' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Free quote', 'gangotri' ); ?>
			</p>
			<h2 id="modal-heading" class="text-xl text-white mt-1"><?php esc_html_e( 'Plan your yatra', 'gangotri' ); ?></h2>
		</div>

		<button type="button" class="enquiry-modal-close" data-enquiry-close
		        aria-label="<?php esc_attr_e( 'Close', 'gangotri' ); ?>">
			<?php echo gangotri_icon( 'x', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>

	<div class="enquiry-modal-body">
		<?php get_template_part( 'template-parts/enquiry-form', null, array( 'prefix' => 'modal' ) ); ?>
	</div>
</dialog>
