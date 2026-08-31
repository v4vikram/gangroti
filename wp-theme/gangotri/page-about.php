<?php
/**
 * Template Name: About
 *
 * The narrative is the page content, so it stays editable in the normal place.
 * The statistics are a repeater instead: they are the numbers most likely to
 * change, and the most damaging to leave wrong.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

$stats  = gangotri_rows( 'page_stats' );
$values = gangotri_rows( 'page_values' );
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

	<section class="section">
		<div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
			<div>
				<div class="prose-legal"><?php the_content(); ?></div>

				<?php if ( $stats ) : ?>
					<dl class="mt-10 grid grid-cols-3 gap-4 border-t border-brand-100 pt-6">
						<?php foreach ( $stats as $stat ) : ?>
							<div>
								<dt class="text-sm text-ink/60"><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></dt>
								<dd class="stat"><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="media-4-3 rounded-card">
					<?php the_post_thumbnail( 'gangotri-hero', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $values ) : ?>
		<section class="section bg-mist">
			<div class="container-page">
				<div class="max-w-2xl mb-10">
					<span class="eyebrow">
						<?php echo gangotri_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'How we work', 'gangotri' ); ?>
					</span>
					<h2 class="text-3xl lg:text-4xl mt-3"><?php esc_html_e( 'What we will not compromise on', 'gangotri' ); ?></h2>
				</div>

				<ul class="grid gap-6 md:grid-cols-2">
					<?php foreach ( $values as $value ) : ?>
						<?php $icon = (string) ( $value['icon'] ?? '' ); ?>
						<li class="card p-6" data-reveal>
							<span class="feature-icon">
								<?php echo gangotri_icon( $icon ? $icon : 'circle-check-big' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<h3 class="text-lg mt-4"><?php echo esc_html( (string) ( $value['title'] ?? '' ) ); ?></h3>
							<p class="mt-2 text-sm leading-relaxed text-ink/70"><?php echo esc_html( (string) ( $value['text'] ?? '' ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<section class="cta-band">
		<div class="container-page text-center max-w-3xl">
			<p class="text-3xl lg:text-4xl font-heading font-bold text-white text-balance">
				<?php esc_html_e( 'Come walk with us', 'gangotri' ); ?>
			</p>
			<p class="mt-4 text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Send us your dates and we will tell you honestly whether the route suits your group.', 'gangotri' ); ?>
			</p>

			<div class="mt-8 flex flex-wrap justify-center gap-3">
				<a class="btn btn-gold btn-lg" href="<?php echo esc_url( gangotri_packages_url() ); ?>">
					<?php esc_html_e( 'Browse Packages', 'gangotri' ); ?>
					<?php echo gangotri_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<button type="button" class="btn btn-ghost-light btn-lg" data-enquiry-open>
					<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Contact Us', 'gangotri' ); ?>
				</button>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
