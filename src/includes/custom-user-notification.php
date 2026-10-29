<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wp_new_user_notification' ) ) {

	if ( version_compare( $GLOBALS['wp_version'], '4.3', '<' ) ) {
		// This definition is for WP versions before 4.3. 4.3 broke this function badly, and 4.4 did no better at fixing it.
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			if ( empty( $plaintext_pass ) ) {
				return;
			}

			$user 		= get_userdata( $user_id );
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			/* translators: %s: User login name. */
			$message  = sprintf( __( 'Usernametest: %s', 'default' ), $user_login ) . "\r\n"; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
			/* translators: %s: User password. */
			$message .= sprintf( __( 'Password: %s', 'default' ), $plaintext_pass ) . "\r\n"; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
			$message .= wp_login_url() . "\r\n";

			/* translators: %s: Site title. */
			wp_mail( $user_email, sprintf( __( '[%s] Your username and password', 'default' ), $blogname ), $message ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
		}
	} else {
		// This definition is for WP versions after 4.3.
		function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
			if ( $deprecated !== null ) {
				_deprecated_argument( __FUNCTION__, '4.3.1' );
			}

			// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notifcation.
			if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
				return;
			}

			global $wpdb, $wp_hasher;
			$user = get_userdata( $user_id );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			// Generate something random for a password reset key.
			$password = wp_generate_password( 10, false );

			wp_set_password( $password, $user_id );

			$login_page = get_permalink( \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id() );

			/* translators: %s: User login name. */
			$message = sprintf( __( 'Username: %s', 'default' ), $user->user_login ) . "\r\n\r\n"; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Core string translated by WordPress.
			/* translators: %s: User password. */
			$message .= sprintf( __( 'password: %s', 'default' ), $password ) . "\r\n\r\n"; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
			$message .= __( 'You account has been created. Log in link is:', 'default' ) . "\r\n\r\n"; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
			$message .= $login_page . "\r\n";

			/* translators: %s: Site title. */
			wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info', 'default' ), $blogname ), $message ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Legacy wp_new_user_notification() string; default domain keeps existing translations applying.
		}
	}
}
