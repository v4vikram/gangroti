<?php
/**
 * Template Name: Gallery
 *
 * Photos come from the media library, filtered by the gallery_category
 * taxonomy, so the client adds a picture by uploading it and ticking a box.
 * The tabs are the terms that actually have photos behind them.
 *
 * Until anything is uploaded the page falls back to the six images bundled
 * with the theme, so it is never blank on the day the site goes live.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

ge_page_head(
	get_the_title(),
	get_the_excerpt() ?: __( 'Temples, trails and the people who walk them. Photographs from recent departures across Garhwal.', 'gangotri-expeditions' ),
	array(
		__( 'Home', 'gangotri-expeditions' ) => home_url( '/' ),
		get_the_title()                      => '',
	)
);

$ge_photos = get_posts( array(
	'post_type'      => 'attachment',
	'post_mime_type' => 'image',
	'post_status'    => 'inherit',
	'posts_per_page' => 60,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		array(
			'taxonomy' => 'gallery_category',
			'operator' => 'EXISTS',
		),
	),
) );

$ge_terms = get_terms( array(
	'taxonomy'   => 'gallery_category',
	'hide_empty' => true,
) );

if ( is_wp_error( $ge_terms ) ) {
	$ge_terms = array();
}
?>

<section class="section">
	<div class="container-page">

		<?php if ( $ge_photos ) : ?>

			<?php if ( count( $ge_terms ) > 1 ) : ?>
				<!-- Tabs filter the grid in place. Without JS every item stays visible. -->
				<div class="gallery-tabs" data-gallery-tabs role="tablist"
				     aria-label="<?php esc_attr_e( 'Filter photos', 'gangotri-expeditions' ); ?>">
					<button type="button" class="gallery-tab is-active" data-gallery-filter="all"
					        role="tab" aria-selected="true"><?php esc_html_e( 'All', 'gangotri-expeditions' ); ?></button>
					<?php foreach ( $ge_terms as $ge_term ) : ?>
						<button type="button" class="gallery-tab" data-gallery-filter="<?php echo esc_attr( $ge_term->slug ); ?>"
						        role="tab" aria-selected="false"><?php echo esc_html( $ge_term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<ul class="gallery-grid mt-8" data-gallery-grid>
				<?php
				foreach ( $ge_photos as $ge_photo ) :
					$ge_cats    = wp_get_object_terms( $ge_photo->ID, 'gallery_category', array( 'fields' => 'slugs' ) );
					$ge_full    = wp_get_attachment_image_url( $ge_photo->ID, 'ge-hero' );
					$ge_caption = wp_get_attachment_caption( $ge_photo->ID ) ?: get_the_title( $ge_photo );
					?>
					<li data-cat="<?php echo esc_attr( implode( ' ', (array) $ge_cats ) ); ?>">
						<button type="button" class="gallery-item" data-lightbox
						        data-full="<?php echo esc_url( $ge_full ); ?>"
						        data-caption="<?php echo esc_attr( $ge_caption ); ?>">
							<?php
							echo wp_get_attachment_image( $ge_photo->ID, 'ge-card', false, array(
								'loading'  => 'lazy',
								'decoding' => 'async',
							) );
							?>
							<span class="gallery-zoom"><?php ge_icon( 'plus' ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>

		<?php else : ?>

			<?php
			// Nothing categorised yet - show what ships with the theme.
			$ge_bundled = array(
				1 => __( 'Yamunotri temple in the Garhwal Himalaya', 'gangotri-expeditions' ),
				2 => __( 'A trekking group on a Himalayan trail in Uttarakhand', 'gangotri-expeditions' ),
				3 => __( 'The Ganga flowing past Rishikesh', 'gangotri-expeditions' ),
				4 => __( 'Deoria Tal reflecting the surrounding peaks', 'gangotri-expeditions' ),
				5 => __( 'Nanda Devi peak', 'gangotri-expeditions' ),
				6 => __( 'Snow-covered slopes at Auli', 'gangotri-expeditions' ),
			);
			?>

			<ul class="gallery-grid" data-gallery-grid>
				<?php foreach ( $ge_bundled as $ge_n => $ge_alt ) : ?>
					<?php $ge_src = GE_URI . '/assets/img/gallery/gallery-' . $ge_n . '.webp'; ?>
					<li data-cat="all">
						<button type="button" class="gallery-item" data-lightbox
						        data-full="<?php echo esc_url( $ge_src ); ?>"
						        data-caption="<?php echo esc_attr( $ge_alt ); ?>">
							<img src="<?php echo esc_url( $ge_src ); ?>" alt="<?php echo esc_attr( $ge_alt ); ?>"
							     width="800" height="800" loading="lazy" decoding="async">
							<span class="gallery-zoom"><?php ge_icon( 'plus' ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( current_user_can( 'upload_files' ) ) : ?>
				<p class="mt-8 text-sm text-ink/60">
					<?php
					printf(
						/* translators: %s: link to the media library. */
						esc_html__( 'These are the placeholders bundled with the theme. Upload your own in %s and give each one a Gallery category to replace them.', 'gangotri-expeditions' ),
						sprintf(
							'<a class="underline underline-offset-2" href="%s">%s</a>',
							esc_url( admin_url( 'upload.php' ) ),
							esc_html__( 'the media library', 'gangotri-expeditions' )
						)
					);
					?>
				</p>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="prose-ge mt-12 max-w-3xl"><?php the_content(); ?></div>
		<?php endif; ?>

	</div>
</section>

<?php
get_template_part( 'parts/cta-band', null, array(
	'title' => __( 'Want to be in next season\'s photos?', 'gangotri-expeditions' ),
	'text'  => __( 'Tell us your dates and who is travelling, and we will put together a route that suits your group.', 'gangotri-expeditions' ),
) );

get_footer();
