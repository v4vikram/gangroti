<?php
/**
 * Template Name: Contact
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

get_header();
?>

<main id="main" class="flex-1">

	<section class="page-head">
		<div class="container-page">
			<?php gangotri_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>
			<h1 class="text-3xl lg:text-5xl mt-3 text-white"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-2xl text-brand-100 leading-relaxed">
				<?php esc_html_e( 'Call, message, or send the form - whichever suits. A real person answers, usually within a few hours.', 'gangotri' ); ?>
			</p>
		</div>
	</section>

	<?php
	get_template_part(
		'template-parts/enquiry-section',
		null,
		array( 'heading' => __( 'Send us an enquiry', 'gangotri' ), 'prefix' => 'contact' )
	);
	?>

	<?php if ( trim( (string) get_the_content() ) ) : ?>
		<section class="section bg-mist">
			<div class="container-page max-w-3xl prose-legal">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_footer();
