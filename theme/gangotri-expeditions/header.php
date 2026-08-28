<?php
/**
 * Document head, site header and mobile nav panel.
 *
 * @package Gangotri_Expeditions
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#1e5a3a">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'min-h-screen flex flex-col' ); ?>>
<?php wp_body_open(); // inc/assets.php inlines the icon sprite here. ?>

<a class="sr-only-focusable top-4 left-4 z-[70] btn btn-primary" href="#main">
	<?php esc_html_e( 'Skip to content', 'gangotri-expeditions' ); ?>
</a>

<header class="site-header" data-header>
	<div class="container-page">
		<div class="flex items-center justify-between gap-6 py-3">

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3 shrink-0"
			   aria-label="<?php echo esc_attr( sprintf(
				   /* translators: %s: site name. */
				   __( '%s - home', 'gangotri-expeditions' ),
				   get_bloginfo( 'name' )
			   ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( GE_URI . '/assets/img/logo-mark.webp' ); ?>" alt=""
					     width="343" height="256" class="h-11 w-auto lg:h-14" fetchpriority="high">
				<?php endif; ?>
				<span class="font-heading font-bold leading-[1.05] text-brand-800 text-base lg:text-xl tracking-tight">
					GANGOTRI<br><span class="text-brand-600 font-semibold tracking-[0.08em] text-[0.7em]">EXPEDITIONS</span>
				</span>
			</a>

			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => 'nav',
				'container_class' => 'hidden lg:flex items-center gap-8',
				'container_aria_label' => __( 'Primary', 'gangotri-expeditions' ),
				'menu_class'     => 'contents',
				'depth'          => 1,
				'link_before'    => '',
				'fallback_cb'    => 'ge_primary_menu_fallback',
				'items_wrap'     => '%3$s',
				'walker'         => new GE_Nav_Walker( 'nav-link' ),
			) );
			?>

			<div class="flex items-center gap-2">
				<a class="hidden md:inline-flex btn btn-outline btn-sm" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
					<?php ge_icon( 'phone' ); ?>
					<?php echo esc_html( ge_option( 'phone' ) ); ?>
				</a>
				<button type="button" class="hidden sm:inline-flex btn btn-gold btn-sm" data-enquiry-open>
					<?php esc_html_e( 'Book Now', 'gangotri-expeditions' ); ?>
				</button>

				<button type="button" class="lg:hidden slider-arrow w-11 h-11" data-nav-toggle
				        aria-expanded="false" aria-controls="nav-panel"
				        aria-label="<?php esc_attr_e( 'Open menu', 'gangotri-expeditions' ); ?>">
					<?php ge_icon( 'menu', 'text-xl' ); ?>
				</button>
			</div>

		</div>
	</div>
</header>

<div class="nav-panel lg:hidden" id="nav-panel" data-nav-panel hidden>
	<div class="container-page h-full flex flex-col">
		<div class="flex items-center justify-between py-3">
			<span class="font-heading font-bold text-brand-800"><?php esc_html_e( 'Menu', 'gangotri-expeditions' ); ?></span>
			<button type="button" class="slider-arrow w-11 h-11" data-nav-close
			        aria-label="<?php esc_attr_e( 'Close menu', 'gangotri-expeditions' ); ?>">
				<?php ge_icon( 'x', 'text-xl' ); ?>
			</button>
		</div>

		<?php
		wp_nav_menu( array(
			'theme_location'       => 'primary',
			'container'            => 'nav',
			'container_class'      => 'flex flex-col gap-1 pt-4',
			'container_aria_label' => __( 'Mobile', 'gangotri-expeditions' ),
			'depth'                => 1,
			'fallback_cb'          => 'ge_mobile_menu_fallback',
			'items_wrap'           => '%3$s',
			'walker'               => new GE_Nav_Walker( 'py-3 text-lg font-heading font-medium text-brand-800 border-b border-brand-100' ),
		) );
		?>

		<div class="mt-auto pb-8 pt-6 grid gap-3">
			<a class="btn btn-primary btn-lg" href="tel:<?php echo esc_attr( ge_option( 'phone_raw' ) ); ?>">
				<?php ge_icon( 'phone-call' ); ?>
				<?php
				printf(
					/* translators: %s: phone number. */
					esc_html__( 'Call %s', 'gangotri-expeditions' ),
					esc_html( ge_option( 'phone' ) )
				);
				?>
			</a>
			<a class="btn btn-gold btn-lg" href="<?php echo esc_url( ge_whatsapp_url() ); ?>" rel="noopener">
				<?php ge_icon( 'whatsapp' ); ?>
				<?php esc_html_e( 'WhatsApp Us', 'gangotri-expeditions' ); ?>
			</a>
		</div>
	</div>
</div>

<main id="main" class="flex-1">
