<?php
/**
 * Comments, for the blog.
 *
 * Packages do not support comments (see the CPT's `supports` array), so this
 * only ever runs on posts.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;

// A post being password-protected means the comments are too.
if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="text-xl">
			<?php
			$ge_count = (int) get_comments_number();

			printf(
				esc_html(
					/* translators: %s: comment count. */
					_n( '%s comment', '%s comments', $ge_count, 'gangotri-expeditions' )
				),
				esc_html( number_format_i18n( $ge_count ) )
			);
			?>
		</h2>

		<ol class="mt-6 space-y-6">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 44,
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'class'     => 'ge-pagination mt-8',
			'prev_text' => ge_get_icon( 'chevron-left' ) . esc_html__( 'Older', 'gangotri-expeditions' ),
			'next_text' => esc_html__( 'Newer', 'gangotri-expeditions' ) . ge_get_icon( 'chevron-right' ),
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="mt-6 text-sm text-ink/60">
			<?php esc_html_e( 'Comments are closed on this post.', 'gangotri-expeditions' ); ?>
		</p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_form'         => 'grid gap-4 mt-10',
		'class_submit'       => 'btn btn-primary',
		'title_reply_before' => '<h2 class="text-xl">',
		'title_reply_after'  => '</h2>',
		'comment_field'      => sprintf(
			'<div><label class="label" for="comment">%s</label><textarea class="field" id="comment" name="comment" rows="5" required></textarea></div>',
			esc_html__( 'Your comment', 'gangotri-expeditions' )
		),
	) );
	?>

</section>
