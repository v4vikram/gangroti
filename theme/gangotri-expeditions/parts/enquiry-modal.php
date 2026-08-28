<?php
/**
 * Enquiry popup, present on every page.
 *
 * Opened by anything carrying `data-enquiry-open`. Built on <dialog>, so the
 * browser handles the backdrop, focus trapping, inert background and Escape -
 * none of that needs writing or maintaining.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;
?>

<dialog class="enquiry-modal" data-enquiry-modal aria-labelledby="modal-heading">
	<div class="enquiry-modal-head">
		<div>
			<p class="eyebrow text-gold-400">
				<?php ge_icon( 'trishul' ); ?>
				<?php esc_html_e( 'Free quote', 'gangotri-expeditions' ); ?>
			</p>
			<h2 id="modal-heading" class="text-xl text-white mt-1">
				<?php esc_html_e( 'Plan your yatra', 'gangotri-expeditions' ); ?>
			</h2>
		</div>
		<button type="button" class="enquiry-modal-close" data-enquiry-close
		        aria-label="<?php esc_attr_e( 'Close', 'gangotri-expeditions' ); ?>">
			<?php ge_icon( 'x', 'text-xl' ); ?>
		</button>
	</div>

	<div class="enquiry-modal-body">
		<?php get_template_part( 'parts/enquiry-form', null, array( 'prefix' => 'modal' ) ); ?>
	</div>
</dialog>
