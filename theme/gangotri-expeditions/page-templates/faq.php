<?php
/**
 * Template Name: FAQ
 *
 * Questions come from the repeater this template adds to the page (the same
 * one packages use), so the client edits them without touching a file. The
 * page's own content, if any, prints above them as an intro.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

ge_page_head(
	get_the_title(),
	get_the_excerpt() ?: __( 'The questions we are asked most, answered honestly. Anything not covered here, message us - a real person replies.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
		get_the_title()                      => '',
	)
);

$ge_faqs = ge_field( 'faqs' );
?>

<section class="section">
	<div class="container-page grid gap-10 lg:grid-cols-[22rem_1fr] lg:gap-16">

		<div>
			<span class="eyebrow"><?php ge_icon( 'info' ); ?> <?php esc_html_e( 'FAQ', 'gangotri-expeditions' ); ?></span>
			<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Still wondering?', 'gangotri-expeditions' ); ?></h2>
			<p class="mt-3 text-ink/70 leading-relaxed">
				<?php esc_html_e( 'Anything not covered here, message us on WhatsApp - a real person answers.', 'gangotri-expeditions' ); ?>
			</p>
			<a class="btn btn-primary mt-6" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
				<?php ge_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Ask a Question', 'gangotri-expeditions' ); ?>
			</a>
		</div>

		<div>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose-ge mb-8"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $ge_faqs ) : ?>
				<div data-accordion data-accordion-single class="divide-y divide-brand-100 border-y border-brand-100">
					<?php foreach ( $ge_faqs as $ge_i => $ge_faq ) : ?>
						<div>
							<h3>
								<button type="button" class="accordion-trigger" data-accordion-trigger
								        aria-expanded="false" aria-controls="page-faq-<?php echo (int) $ge_i + 1; ?>">
									<span><?php echo esc_html( $ge_faq['q'] ?? '' ); ?></span>
									<?php ge_icon( 'chevron-down', 'accordion-chevron' ); ?>
								</button>
							</h3>
							<div class="accordion-panel" id="page-faq-<?php echo (int) $ge_i + 1; ?>">
								<p class="accordion-body"><?php echo esc_html( $ge_faq['a'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
				<div class="empty-state">
					<p class="font-heading font-semibold text-brand-800">
						<?php esc_html_e( 'No questions added yet', 'gangotri-expeditions' ); ?>
					</p>
					<p class="mt-2 text-sm text-ink/65">
						<?php esc_html_e( 'Edit this page and fill in the "Questions and answers" box below the editor.', 'gangotri-expeditions' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>

<?php
get_template_part( 'parts/cta-band' );
get_footer();
