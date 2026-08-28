<?php
/**
 * The fallback template, and the blog.
 *
 * Every request WordPress cannot match to a more specific template lands here,
 * so it has to work for an archive, a category, a tag and the posts page
 * alike - which is why the heading comes from the query rather than being
 * written in.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ge_crumbs = array( __( 'Home', 'gangotri-expeditions' ) => home_url( '/' ) );

if ( is_home() ) {
	$ge_title = get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Journal', 'gangotri-expeditions' );
	$ge_intro = __( 'Trail notes, route conditions and practical advice from the people who walk these paths for a living.', 'gangotri-expeditions' );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$ge_title = single_term_title( '', false );
	$ge_intro = wp_strip_all_tags( term_description() );
} elseif ( is_author() ) {
	$ge_title = get_the_author();
	$ge_intro = get_the_author_meta( 'description' );
} elseif ( is_archive() ) {
	$ge_title = get_the_archive_title();
	$ge_intro = wp_strip_all_tags( get_the_archive_description() );
} else {
	$ge_title = __( 'Journal', 'gangotri-expeditions' );
	$ge_intro = '';
}

$ge_crumbs[ $ge_title ] = '';

ge_page_head( $ge_title, $ge_intro, $ge_crumbs );
?>

<section class="section">
	<div class="container-page">

		<?php if ( have_posts() ) : ?>

			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/post-card' );
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
					<?php esc_html_e( 'Nothing here yet', 'gangotri-expeditions' ); ?>
				</p>
				<p class="mt-2 text-sm text-ink/65">
					<?php esc_html_e( 'There are no posts to show. Try the packages instead.', 'gangotri-expeditions' ); ?>
				</p>
				<a class="btn btn-primary mt-6" href="<?php echo esc_url( ge_yatras_url() ); ?>">
					<?php ge_icon( 'route' ); ?> <?php esc_html_e( 'Browse packages', 'gangotri-expeditions' ); ?>
				</a>
			</div>

		<?php endif; ?>

	</div>
</section>

<?php
get_template_part( 'parts/cta-band' );
get_footer();
