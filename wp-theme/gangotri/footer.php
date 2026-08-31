<?php
/**
 * Site footer, the floating WhatsApp button and the enquiry popup.
 *
 * Converted from src/partials/footer.html.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

$gangotri_whatsapp = gangotri_whatsapp_url();
?>

<footer class="bg-brand-800 text-brand-100 mt-auto">
	<div class="container-page py-14 lg:py-16">
		<div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

			<div class="lg:col-span-1">
				<div class="flex items-center gap-2.5 mb-4">
					<img src="<?php echo esc_url( GANGOTRI_URI . '/assets/img/logo-mark-white.webp' ); ?>" alt=""
					     width="343" height="256" class="h-14 w-auto" loading="lazy">
					<span class="font-heading font-bold text-white text-lg leading-[1.05] tracking-tight">
						GANGOTRI<br><span class="font-semibold tracking-[0.08em] text-[0.7em] text-gold-400">EXPEDITIONS</span>
					</span>
				</div>

				<p class="text-sm leading-relaxed text-brand-200">
					<?php echo esc_html( get_bloginfo( 'description' ) ); ?>
				</p>

				<div class="flex gap-3 mt-5">
					<?php
					$gangotri_social = array(
						'instagram' => __( 'Instagram', 'gangotri' ),
						'facebook'  => __( 'Facebook', 'gangotri' ),
						'youtube'   => __( 'YouTube', 'gangotri' ),
					);

					foreach ( $gangotri_social as $gangotri_network => $gangotri_label ) :
						$gangotri_url = (string) gangotri_option( $gangotri_network );
						if ( ! $gangotri_url ) {
							continue; // An empty profile link is worse than no icon.
						}
						?>
						<a href="<?php echo esc_url( $gangotri_url ); ?>" rel="noopener"
						   class="slider-arrow w-10 h-10 bg-brand-700 border-brand-700 text-brand-100 hover:bg-gold-500 hover:text-ink hover:border-gold-500"
						   aria-label="<?php echo esc_attr( $gangotri_label ); ?>">
							<?php echo gangotri_icon( $gangotri_network ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Explore', 'gangotri' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'fallback_cb'    => 'gangotri_footer_fallback',
						)
					);
					?>
				</ul>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Our Packages', 'gangotri' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<?php
					$gangotri_packages = get_posts(
						array(
							'post_type'      => 'yatra',
							'posts_per_page' => 5,
							'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						)
					);

					foreach ( $gangotri_packages as $gangotri_package ) :
						?>
						<li>
							<a class="hover:text-gold-400 transition-colors" href="<?php echo esc_url( (string) get_permalink( $gangotri_package ) ); ?>">
								<?php echo esc_html( get_the_title( $gangotri_package ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>

					<li>
						<a class="hover:text-gold-400 transition-colors" href="<?php echo esc_url( gangotri_packages_url() ); ?>">
							<?php esc_html_e( 'View all packages', 'gangotri' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Get in Touch', 'gangotri' ); ?></h2>
				<ul class="space-y-3 text-sm">
					<li class="flex gap-3">
						<?php echo gangotri_icon( 'map-pin', 'icon text-gold-400 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( gangotri_option( 'address' ) ); ?></span>
					</li>
					<li class="flex gap-3">
						<?php echo gangotri_icon( 'phone', 'icon text-gold-400 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a class="hover:text-gold-400 transition-colors" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
							<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
						</a>
					</li>
					<li class="flex gap-3">
						<?php echo gangotri_icon( 'mail', 'icon text-gold-400 mt-0.5' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a class="hover:text-gold-400 transition-colors" href="mailto:<?php echo esc_attr( gangotri_option( 'email' ) ); ?>">
							<?php echo esc_html( gangotri_option( 'email' ) ); ?>
						</a>
					</li>
				</ul>

				<?php if ( $gangotri_whatsapp ) : ?>
					<a class="btn btn-gold btn-sm mt-5" href="<?php echo esc_url( $gangotri_whatsapp ); ?>" rel="noopener">
						<?php echo gangotri_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Enquire on WhatsApp', 'gangotri' ); ?>
					</a>
				<?php endif; ?>
			</div>

		</div>
	</div>

	<div class="border-t border-brand-700">
		<div class="container-page py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-brand-300">
			<p>
				&copy; <?php echo esc_html( (string) gmdate( 'Y' ) ); ?>
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'gangotri' ); ?>
			</p>

			<div class="flex flex-wrap justify-center gap-x-5 gap-y-2">
				<?php
				foreach ( array( 'privacy' => 'Privacy Policy', 'terms' => 'Terms', 'cancellation' => 'Cancellation Policy', 'credits' => 'Photo Credits' ) as $gangotri_slug => $gangotri_label ) :
					$gangotri_page = get_page_by_path( $gangotri_slug );
					if ( ! $gangotri_page ) {
						continue;
					}
					?>
					<a class="hover:text-gold-400 transition-colors" href="<?php echo esc_url( (string) get_permalink( $gangotri_page ) ); ?>">
						<?php echo esc_html( $gangotri_label ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>

<?php if ( $gangotri_whatsapp ) : ?>
	<a href="<?php echo esc_url( $gangotri_whatsapp ); ?>" rel="noopener"
	   class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white grid place-items-center shadow-lg hover:scale-105 transition-transform"
	   aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'gangotri' ); ?>">
		<?php echo gangotri_icon( 'whatsapp', 'icon text-2xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php endif; ?>

<?php get_template_part( 'template-parts/enquiry-modal' ); ?>

<?php wp_footer(); ?>
</body>
</html>
