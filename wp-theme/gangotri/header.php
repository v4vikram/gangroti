<?php
/**
 * Site header.
 *
 * Converted from src/partials/header.html. The classes are unchanged, so the
 * same stylesheet drives both.
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#1e5a3a">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php gangotri_sprite(); ?>

<a class="sr-only-focusable top-4 left-4 z-[70] btn btn-primary" href="#main">
	<?php esc_html_e( 'Skip to content', 'gangotri' ); ?>
</a>

<header class="site-header" data-header>
	<div class="container-page">
		<div class="flex items-center justify-between gap-6 py-3">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3 shrink-0"
			   aria-label="<?php echo esc_attr( sprintf( '%s - home', get_bloginfo( 'name' ) ) ); ?>">
				<img src="<?php echo esc_url( GANGOTRI_URI . '/assets/img/logo-mark.webp' ); ?>" alt=""
				     width="343" height="256" class="h-11 w-auto lg:h-14" fetchpriority="high">
				<span class="font-heading font-bold leading-[1.05] text-brand-800 text-base lg:text-xl tracking-tight">
					GANGOTRI<br><span class="text-brand-600 font-semibold tracking-[0.08em] text-[0.7em]">EXPEDITIONS</span>
				</span>
			</a>

			<nav class="hidden lg:flex items-center gap-8" aria-label="<?php esc_attr_e( 'Primary', 'gangotri' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'link_before'    => '',
						'fallback_cb'    => 'gangotri_default_menu',
						// The <li> wrapper is dropped: the links sit directly in
						// a flex row, which is what the design does.
						'walker'         => new Gangotri_Nav_Walker(),
					)
				);
				?>
			</nav>

			<div class="flex items-center gap-2">
				<a class="hidden md:inline-flex btn btn-outline btn-sm" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
					<?php echo gangotri_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( gangotri_option( 'phone' ) ); ?>
				</a>

				<button type="button" class="hidden sm:inline-flex btn btn-gold btn-sm" data-enquiry-open>
					<?php esc_html_e( 'Book Now', 'gangotri' ); ?>
				</button>

				<button type="button" class="lg:hidden slider-arrow w-11 h-11" data-nav-toggle
				        aria-expanded="false" aria-controls="nav-panel"
				        aria-label="<?php esc_attr_e( 'Open menu', 'gangotri' ); ?>">
					<?php echo gangotri_icon( 'menu', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>

		</div>
	</div>
</header>

<div class="nav-panel lg:hidden" id="nav-panel" data-nav-panel hidden>
	<div class="container-page h-full flex flex-col">
		<div class="flex items-center justify-between py-3">
			<span class="font-heading font-bold text-brand-800"><?php esc_html_e( 'Menu', 'gangotri' ); ?></span>
			<button type="button" class="slider-arrow w-11 h-11" data-nav-close
			        aria-label="<?php esc_attr_e( 'Close menu', 'gangotri' ); ?>">
				<?php echo gangotri_icon( 'x', 'icon text-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<nav class="flex flex-col gap-1 pt-4" aria-label="<?php esc_attr_e( 'Mobile', 'gangotri' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'fallback_cb'    => 'gangotri_default_menu_mobile',
					'walker'         => new Gangotri_Nav_Walker( 'py-3 text-lg font-heading font-medium text-brand-800 border-b border-brand-100' ),
				)
			);
			?>
		</nav>

		<div class="mt-auto pb-8 pt-6 grid gap-3">
			<button type="button" class="btn btn-gold btn-lg" data-enquiry-open>
				<?php echo gangotri_icon( 'send' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Get a Free Quote', 'gangotri' ); ?>
			</button>

			<a class="btn btn-primary btn-lg" href="tel:<?php echo esc_attr( gangotri_option( 'phone_raw' ) ); ?>">
				<?php echo gangotri_icon( 'phone-call' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php printf( /* translators: %s: phone number. */ esc_html__( 'Call %s', 'gangotri' ), esc_html( gangotri_option( 'phone' ) ) ); ?>
			</a>

			<?php if ( gangotri_whatsapp_url() ) : ?>
				<a class="btn btn-gold btn-lg" href="<?php echo esc_url( gangotri_whatsapp_url() ); ?>" rel="noopener">
					<?php echo gangotri_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'WhatsApp Us', 'gangotri' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
