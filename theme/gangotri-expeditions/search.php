<?php
/**
 * Search results.
 *
 * Packages and posts come back in one list, so the card is chosen per result
 * rather than per query.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ge_term = get_search_query();

ge_page_head(
	sprintf(
		/* translators: %s: the search term. */
		__( 'Search: %s', 'gangotri-expeditions' ),
		$ge_term
	),
	'',
	array(
		__( 'Home', 'gangotri-expeditions' )   => home_url( '/' ),
		__( 'Search', 'gangotri-expeditions' ) => '',
	)
);
?>

<section class="section">
	<div class="container-page">

		<form role="search" method="get" class="filter-bar mb-8" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div class="sm:col-span-3">
				<label class="label" for="s"><?php esc_html_e( 'Search this site', 'gangotri-expeditions' ); ?></label>
				<input class="field" type="search" id="s" name="s" value="<?php echo esc_attr( $ge_term ); ?>">
			</div>
			<button class="btn btn-primary md:self-end" type="submit"><?php esc_html_e( 'Search', 'gangotri-expeditions' ); ?></button>
		</form>

		<?php if ( have_posts() ) : ?>

			<p class="text-sm text-ink/60 mb-6">
				<?php
				$ge_found = (int) $GLOBALS['wp_query']->found_posts;
				printf(
					esc_html(
						/* translators: %s: number of results. */
						_n( '%s result', '%s results', $ge_found, 'gangotri-expeditions' )
					),
					esc_html( number_format_i18n( $ge_found ) )
				);
				?>
			</p>

			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/' . ( 'yatra' === get_post_type() ? 'yatra-card' : 'post-card' ) );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'class'     => 'ge-pagination mt-12',
				'prev_text' => ge_get_icon( 'chevron-left' ) . esc_html__( 'Previous', 'gangotri-expeditions' ),
				'next_text' => esc_html__( 'Next', 'gangotri-expeditions' ) . ge_get_icon( 'chevron-right' ),
			) );
			?>

		<?php else : ?>

			<div class="empty-state">
				<span class="trust-icon mx-auto"><?php ge_icon( 'compass' ); ?></span>
				<p class="mt-4 font-heading font-semibold text-brand-800">
					<?php esc_html_e( 'Nothing matched that', 'gangotri-expeditions' ); ?>
				</p>
				<p class="mt-2 text-sm text-ink/65">
					<?php esc_html_e( 'Try a shorter phrase, or browse the packages.', 'gangotri-expeditions' ); ?>
				</p>
				<a class="btn btn-primary mt-6" href="<?php echo esc_url( ge_yatras_url() ); ?>">
					<?php ge_icon( 'route' ); ?> <?php esc_html_e( 'Browse packages', 'gangotri-expeditions' ); ?>
				</a>
			</div>

		<?php endif; ?>

	</div>
</section>

<?php
get_footer();
