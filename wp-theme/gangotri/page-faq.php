<?php
/**
 * Template Name: FAQ
 *
 * Questions come from a repeater on the page, grouped by an optional section
 * label. inc/schema.php publishes them as FAQPage structured data at the same
 * time, so the answers can be quoted by search and by AI assistants rather
 * than only read here.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

$faqs = gangotri_rows( 'page_faqs' );

// Grouped by section label, keeping the order they were entered in.
$groups = array();
foreach ( $faqs as $faq ) {
	$groups[ (string) ( $faq['section'] ?? '' ) ][] = $faq;
}
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Planning, permits, altitude, money. If your question is not here, ask us - a real person answers.', 'gangotri' ); ?>
			</p>
		</div>
	</section>

	<section class="section">
		<div class="container-page max-w-3xl">

			<?php if ( $groups ) : ?>
				<?php $n = 0; ?>
				<?php foreach ( $groups as $section => $items ) : ?>
					<?php if ( $section ) : ?>
						<h2 class="text-2xl lg:text-3xl<?php echo $n ? ' mt-12' : ''; ?>"><?php echo esc_html( $section ); ?></h2>
					<?php endif; ?>

					<div data-accordion class="mt-4 mb-12 divide-y divide-brand-100 border-y border-brand-100">
						<?php foreach ( $items as $faq ) : ?>
							<?php $n++; ?>
							<div>
								<h3>
									<button type="button" class="accordion-trigger" data-accordion-trigger
									        aria-expanded="false" aria-controls="q<?php echo esc_attr( (string) $n ); ?>">
										<span><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></span>
										<?php echo gangotri_icon( 'chevron-down', 'icon accordion-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</button>
								</h3>
								<div class="accordion-panel" id="q<?php echo esc_attr( (string) $n ); ?>">
									<p class="accordion-body"><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty-state">
					<span class="trust-icon mx-auto"><?php echo gangotri_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'No questions added yet', 'gangotri' ); ?></p>
					<p class="mt-2 text-sm text-ink/65"><?php esc_html_e( 'Add them in the FAQ box when editing this page.', 'gangotri' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="rounded-card bg-brand-50 border border-brand-100 p-6 text-center">
				<p class="font-heading font-semibold text-brand-800"><?php esc_html_e( 'Still have a question?', 'gangotri' ); ?></p>
				<p class="mt-2 text-sm text-ink/70"><?php esc_html_e( 'We would rather answer it now than have you guess.', 'gangotri' ); ?></p>

				<div class="mt-5 flex flex-wrap justify-center gap-3">
					<button type="button" class="btn btn-primary" data-enquiry-open>
						<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Ask Us', 'gangotri' ); ?>
					</button>
					<a class="btn btn-outline" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
						<?php echo gangotri_icon( 'phone-call' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
					</a>
				</div>
			</div>

		</div>
	</section>

</main>

<?php
get_footer();
