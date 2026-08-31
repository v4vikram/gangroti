<?php
/**
 * Template Name: Services
 *
 * Three repeaters: the service cards, the indicative rate table, and the FAQ.
 * The rate table is here rather than in the page content because it is the
 * block most likely to be lifted verbatim by an answer engine, and a table
 * built in the editor rarely survives a copy-paste intact.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

$services = gangotri_rows( 'page_services' );
$rates    = gangotri_rows( 'page_rates' );
$faqs     = gangotri_rows( 'page_faqs' );
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $services ) : ?>
		<section class="section">
			<div class="container-page">
				<ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $services as $service ) : ?>
						<?php $icon = (string) ( $service['icon'] ?? '' ); ?>
						<li class="card p-6 h-full" data-reveal>
							<span class="feature-icon">
								<?php echo gangotri_icon( $icon ? $icon : 'compass' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<h2 class="text-lg mt-4"><?php echo esc_html( (string) ( $service['title'] ?? '' ) ); ?></h2>
							<p class="mt-2 text-sm leading-relaxed text-ink/70"><?php echo esc_html( (string) ( $service['text'] ?? '' ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<section class="section<?php echo $services ? ' pt-0' : ''; ?>">
			<div class="container-page max-w-3xl prose-legal"><?php the_content(); ?></div>
		</section>
	<?php endif; ?>

	<?php if ( $rates ) : ?>
		<section class="section bg-mist">
			<div class="container-page">
				<div class="max-w-2xl mb-10">
					<span class="eyebrow">
						<?php echo gangotri_icon( 'indian-rupee' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'What things cost', 'gangotri' ); ?>
					</span>
					<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Indicative add-on pricing', 'gangotri' ); ?></h2>
					<p class="mt-3 text-ink/70 leading-relaxed">
						<?php esc_html_e( 'Per person unless stated. Government-set charges change year to year, so we confirm the exact figure when you book.', 'gangotri' ); ?>
					</p>
				</div>

				<div class="table-wrap">
					<table class="data-table">
						<caption class="sr-only"><?php esc_html_e( 'Indicative add-on service pricing', 'gangotri' ); ?></caption>
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Service', 'gangotri' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Route', 'gangotri' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Indicative cost', 'gangotri' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rates as $rate ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( (string) ( $rate['service'] ?? '' ) ); ?></th>
									<td><?php echo esc_html( (string) ( $rate['route'] ?? '' ) ); ?></td>
									<td><?php echo esc_html( (string) ( $rate['cost'] ?? '' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $faqs ) : ?>
		<section class="section">
			<div class="container-page grid gap-10 lg:grid-cols-[22rem_1fr] lg:gap-16">
				<div>
					<span class="eyebrow">
						<?php echo gangotri_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Service FAQ', 'gangotri' ); ?>
					</span>
					<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'Common questions', 'gangotri' ); ?></h2>
				</div>

				<div data-accordion data-accordion-single class="divide-y divide-brand-100 border-y border-brand-100">
					<?php foreach ( $faqs as $i => $faq ) : ?>
						<?php $id = 's-faq-' . ( (int) $i + 1 ); ?>
						<div>
							<h3>
								<button type="button" class="accordion-trigger" data-accordion-trigger
								        aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>">
									<span><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></span>
									<?php echo gangotri_icon( 'chevron-down', 'icon accordion-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							</h3>
							<div class="accordion-panel" id="<?php echo esc_attr( $id ); ?>">
								<p class="accordion-body"><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="cta-band">
		<div class="container-page text-center max-w-3xl">
			<p class="text-3xl lg:text-4xl font-heading font-bold text-white text-balance">
				<?php esc_html_e( 'Tell us what you need arranged', 'gangotri' ); ?>
			</p>
			<p class="mt-4 text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Transport only, a full package, or something in between - we will quote each part separately so you can see what you are paying for.', 'gangotri' ); ?>
			</p>

			<div class="mt-8 flex flex-wrap justify-center gap-3">
				<button type="button" class="btn btn-gold btn-lg" data-enquiry-open>
					<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Request a Quote', 'gangotri' ); ?>
				</button>

				<?php $wa = gangotri_whatsapp_url( __( 'Hello, I would like a quote for a Uttarakhand trip.', 'gangotri' ) ); ?>
				<?php if ( $wa ) : ?>
					<a class="btn btn-ghost-light btn-lg" href="<?php echo esc_url( $wa ); ?>" rel="noopener" target="_blank">
						<?php echo gangotri_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'WhatsApp Us', 'gangotri' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
