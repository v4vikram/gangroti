<?php
/**
 * Send mail over authenticated SMTP.
 *
 * Shared hosting's PHP mail() is unauthenticated and fails SPF/DKIM, so
 * WordPress notifications - the admin-email confirmation, password resets, the
 * enquiry alerts in inc/enquiry.php - either vanish or land in spam. Routing
 * wp_mail() through the domain mailbox over SMTP fixes all of them at once.
 *
 * Credentials are read from wp-config.php constants, never stored here: this
 * file is in version control and the mailbox password is not. Define the
 * constants below on the server and SMTP switches on; leave them undefined and
 * nothing changes.
 *
 *   define( 'GE_SMTP_HOST', 'mail.gangotriexpeditions.in' );
 *   define( 'GE_SMTP_PORT', 465 );
 *   define( 'GE_SMTP_SECURE', 'ssl' );  // 'ssl' for 465, 'tls' for 587
 *   define( 'GE_SMTP_USER', 'admin@gangotriexpeditions.in' );
 *   define( 'GE_SMTP_PASS', 'the-mailbox-password' );
 *   define( 'GE_SMTP_FROM', 'admin@gangotriexpeditions.in' );      // optional
 *   define( 'GE_SMTP_FROM_NAME', 'Gangotri Expeditions' );          // optional
 *
 * @package Gangotri
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the SMTP credentials have been defined.
 */
function gangotri_smtp_ready(): bool {
	return defined( 'GE_SMTP_HOST' )
		&& defined( 'GE_SMTP_USER' )
		&& defined( 'GE_SMTP_PASS' );
}

/**
 * Point PHPMailer at the SMTP server for every wp_mail() call.
 */
add_action(
	'phpmailer_init',
	static function ( $mail ): void {
		if ( ! gangotri_smtp_ready() ) {
			return;
		}

		$mail->isSMTP();
		$mail->Host       = GE_SMTP_HOST;
		$mail->Port       = defined( 'GE_SMTP_PORT' ) ? (int) GE_SMTP_PORT : 465;
		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = defined( 'GE_SMTP_SECURE' ) ? GE_SMTP_SECURE : 'ssl';
		$mail->Username   = GE_SMTP_USER;
		$mail->Password   = GE_SMTP_PASS;
	}
);

/**
 * Send as the mailbox we authenticated with. A From address on a different
 * domain than the SMTP login is the usual reason authenticated mail still
 * lands in spam.
 */
add_filter(
	'wp_mail_from',
	static function ( string $from ): string {
		if ( defined( 'GE_SMTP_FROM' ) && is_email( GE_SMTP_FROM ) ) {
			return GE_SMTP_FROM;
		}
		return gangotri_smtp_ready() ? GE_SMTP_USER : $from;
	}
);

add_filter(
	'wp_mail_from_name',
	static function ( string $name ): string {
		if ( defined( 'GE_SMTP_FROM_NAME' ) && GE_SMTP_FROM_NAME ) {
			return GE_SMTP_FROM_NAME;
		}
		return gangotri_smtp_ready() ? get_bloginfo( 'name' ) : $name;
	}
);

/**
 * Remind admins to finish the setup while the constants are still missing.
 * Without SMTP the site cannot send mail at all, and that is easy to miss
 * until a lead is lost.
 */
add_action(
	'admin_notices',
	static function (): void {
		if ( gangotri_smtp_ready() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Gangotri:', 'gangotri' ); ?></strong>
				<?php esc_html_e( 'SMTP is not configured, so the site cannot send email (enquiry alerts, password resets, admin-email confirmation). Add the GE_SMTP_* constants to wp-config.php.', 'gangotri' ); ?>
			</p>
		</div>
		<?php
	}
);
