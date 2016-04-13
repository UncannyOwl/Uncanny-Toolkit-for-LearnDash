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

			$message  = sprintf( __( 'Usernametest: %s' ), $user_login ) . "\r\n";
			$message .= sprintf( __( 'Password: %s' ), $plaintext_pass ) . "\r\n";
			$message .= wp_login_url() . "\r\n";

			wp_mail( $user_email, sprintf( __( '[%s] Your username and password' ), $blogname ), $message );
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

			$message = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'password: %s' ), $password ) . "\r\n\r\n";
			$message .= __( 'You account has been created. Log in link is:' ) . "\r\n\r\n";
			$message .= $login_page . "\r\n";

			wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info' ), $blogname ), $message );
		}
	}
}
