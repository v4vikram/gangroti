<?php
/**
 * Template Name: Gallery
 *
 * Photographs come from the WordPress media library via the page's own gallery
 * block, or - failing that - from the featured images of the packages, so the
 * page is never empty on a fresh install.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();

// Images attached to this page, if the client has uploaded any.
$images = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_parent'    => get_the_ID(),
		'posts_per_page' => 60,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);

// Otherwise fall back to the package photography.
if ( ! $images ) {
	foreach ( get_posts( array( 'post_type' => 'yatra', 'posts_per_page' => -1 ) ) as $package ) {
		$thumb = get_post_thumbnail_id( $package );
		if ( $thumb ) {
			$images[] = get_post( $thumb );
		}
	}
}
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Temples, trails and the people who walk them. Photographs from recent departures across Garhwal.', 'gangotri' ); ?>
			</p>
		</div>
	</section>

	<section class="section">
		<div class="container-page">
			<?php if ( $images ) : ?>
				<ul class="gallery-grid" data-gallery-grid>
					<?php foreach ( $images as $image ) : ?>
						<?php
						$full    = wp_get_attachment_image_url( $image->ID, 'large' );
						$caption = wp_get_attachment_caption( $image->ID );
						?>
						<li>
							<button type="button" class="gallery-item" data-lightbox
							        data-full="<?php echo esc_url( (string) $full ); ?>"
							        data-caption="<?php echo esc_attr( (string) $caption ); ?>">
								<?php
								echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$image->ID,
									'gangotri-square',
									false,
									array( 'loading' => 'lazy', 'decoding' => 'async' )
								);
								?>
								<span class="gallery-zoom"><?php echo gangotri_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="empty-state">
					<span class="trust-icon mx-auto"><?php echo gangotri_icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p class="mt-4 font-heading font-semibold text-brand-800"><?php esc_html_e( 'No photographs yet', 'gangotri' ); ?></p>
					<p class="mt-2 text-sm text-ink/65"><?php esc_html_e( 'Upload images to this page in the media library and they will appear here.', 'gangotri' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

</main>

<?php // Lightbox shell - populated on click, so it costs nothing until used. ?>
<dialog class="lightbox" data-lightbox-dialog aria-label="<?php esc_attr_e( 'Photo viewer', 'gangotri' ); ?>">
	<button type="button" class="lightbox-close" data-lightbox-close aria-label="<?php esc_attr_e( 'Close', 'gangotri' ); ?>">
		<?php echo gangotri_icon( 'x', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<button type="button" class="lightbox-nav left-3" data-lightbox-prev aria-label="<?php esc_attr_e( 'Previous photo', 'gangotri' ); ?>">
		<?php echo gangotri_icon( 'chevron-left', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<figure class="lightbox-figure">
		<img alt="" data-lightbox-img width="1200" height="900">
		<figcaption data-lightbox-caption></figcaption>
	</figure>
	<button type="button" class="lightbox-nav right-3" data-lightbox-next aria-label="<?php esc_attr_e( 'Next photo', 'gangotri' ); ?>">
		<?php echo gangotri_icon( 'chevron-right', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</dialog>

<?php
get_footer();
