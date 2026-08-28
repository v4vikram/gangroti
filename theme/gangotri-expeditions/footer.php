<?php
/**
 * Site footer, floating WhatsApp button and the enquiry popup.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="bg-brand-800 text-brand-100 mt-auto">
	<div class="container-page py-14 lg:py-16">
		<div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

			<div class="lg:col-span-1">
				<div class="flex items-center gap-2.5 mb-4">
					<img src="<?php echo esc_url( GE_URI . '/assets/img/logo-mark-white.webp' ); ?>" alt=""
					     width="343" height="256" class="h-14 w-auto" loading="lazy">
					<span class="font-heading font-bold text-white text-lg leading-[1.05] tracking-tight">
						GANGOTRI<br><span class="font-semibold tracking-[0.08em] text-[0.7em] text-gold-400">EXPEDITIONS</span>
					</span>
				</div>
				<p class="text-sm leading-relaxed text-brand-200">
					<?php esc_html_e( 'Curated Char Dham yatras and Himalayan treks across Uttarakhand, run by local guides who have walked these trails for years.', 'gangotri-expeditions' ); ?>
				</p>
				<div class="flex gap-3 mt-5">
					<?php
					$socials = array(
						'instagram' => __( 'Instagram', 'gangotri-expeditions' ),
						'facebook'  => __( 'Facebook', 'gangotri-expeditions' ),
						'youtube'   => __( 'YouTube', 'gangotri-expeditions' ),
					);

					foreach ( $socials as $key => $label ) :
						$url = ge_option( $key );
						if ( ! $url ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $url ); ?>" rel="noopener"
						   class="slider-arrow w-10 h-10 bg-brand-700 border-brand-700 text-brand-100 hover:bg-gold-500 hover:text-ink hover:border-gold-500"
						   aria-label="<?php echo esc_attr( $label ); ?>">
							<?php ge_icon( $key ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Explore', 'gangotri-expeditions' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<?php
					if ( has_nav_menu( 'explore' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'explore',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'walker'         => new GE_Nav_Walker( 'hover:text-gold-400 transition-colors' ),
							'before'         => '<li>',
							'after'          => '</li>',
						) );
					} else {
						$explore = array(
							__( 'All Yatras', 'gangotri-expeditions' )   => ge_yatras_url(),
							__( 'About Us', 'gangotri-expeditions' )     => home_url( '/about/' ),
							__( 'Our Services', 'gangotri-expeditions' ) => home_url( '/services/' ),
							__( 'Gallery', 'gangotri-expeditions' )      => home_url( '/gallery/' ),
							__( 'FAQ', 'gangotri-expeditions' )          => home_url( '/faq/' ),
						);
						foreach ( $explore as $label => $url ) {
							printf(
								'<li><a class="hover:text-gold-400 transition-colors" href="%s">%s</a></li>',
								esc_url( $url ),
								esc_html( $label )
							);
						}
					}
					?>
				</ul>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Our Packages', 'gangotri-expeditions' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<?php
					// Straight from the CPT, so the list cannot advertise a
					// package that has been unpublished.
					$footer_yatras = get_posts( array(
						'post_type'              => 'yatra',
						'posts_per_page'         => 6,
						'orderby'                => 'menu_order title',
						'order'                  => 'ASC',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
					) );

					foreach ( $footer_yatras as $yatra ) {
						printf(
							'<li><a class="hover:text-gold-400 transition-colors" href="%s">%s</a></li>',
							esc_url( get_permalink( $yatra ) ),
							esc_html( get_the_title( $yatra ) )
						);
					}
					?>
					<li><a class="hover:text-gold-400 transition-colors" href="<?php echo esc_url( ge_yatras_url() ); ?>">
						<?php esc_html_e( 'View all packages', 'gangotri-expeditions' ); ?>
					</a></li>
				</ul>
			</div>

			<div>
				<h2 class="font-heading font-semibold text-white text-base mb-4"><?php esc_html_e( 'Get in Touch', 'gangotri-expeditions' ); ?></h2>
				<ul class="space-y-3 text-sm">
					<li class="flex gap-3">
						<?php ge_icon( 'map-pin', 'text-gold-400 mt-0.5' ); ?>
						<span><?php echo esc_html( ge_option( 'address' ) ); ?></span>
					</li>
					<li class="flex gap-3">
						<?php ge_icon( 'phone', 'text-gold-400 mt-0.5' ); ?>
						<a class="hover:text-gold-400 transition-colors" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
							<?php echo esc_html( ge_option( 'phone' ) ); ?>
						</a>
					</li>
					<li class="flex gap-3">
						<?php ge_icon( 'mail', 'text-gold-400 mt-0.5' ); ?>
						<a class="hover:text-gold-400 transition-colors" href="mailto:<?php echo esc_attr( ge_option( 'email' ) ); ?>">
							<?php echo esc_html( ge_option( 'email' ) ); ?>
						</a>
					</li>
				</ul>
				<a class="btn btn-gold btn-sm mt-5" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
					<?php ge_icon( 'whatsapp' ); ?>
					<?php esc_html_e( 'Enquire on WhatsApp', 'gangotri-expeditions' ); ?>
				</a>
			</div>

		</div>
	</div>

	<div class="border-t border-brand-700">
		<div class="container-page py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-brand-300">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'gangotri-expeditions' ); ?></p>
			<div class="flex gap-5">
				<?php
				if ( has_nav_menu( 'legal' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'legal',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'walker'         => new GE_Nav_Walker( 'hover:text-gold-400 transition-colors' ),
					) );
				} else {
					$legal = array(
						__( 'Privacy Policy', 'gangotri-expeditions' )      => home_url( '/privacy/' ),
						__( 'Terms', 'gangotri-expeditions' )               => home_url( '/terms/' ),
						__( 'Cancellation Policy', 'gangotri-expeditions' ) => home_url( '/cancellation/' ),
					);
					foreach ( $legal as $label => $url ) {
						printf(
							'<a class="hover:text-gold-400 transition-colors" href="%s">%s</a>',
							esc_url( $url ),
							esc_html( $label )
						);
					}
				}
				?>
			</div>
		</div>
	</div>
</footer>

<a href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener"
   class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white grid place-items-center shadow-lg hover:scale-105 transition-transform"
   aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'gangotri-expeditions' ); ?>">
	<?php ge_icon( 'whatsapp', 'text-2xl' ); ?>
</a>

<?php get_template_part( 'parts/enquiry-modal' ); ?>

<?php wp_footer(); ?>
</body>
</html>
