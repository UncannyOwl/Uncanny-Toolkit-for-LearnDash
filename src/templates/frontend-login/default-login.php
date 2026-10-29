<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside FrontendLoginPlus::uo_login_ui(); variables are local to that method.

echo $default_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin-built <style> block from FrontendLoginPlus::uo_login_ui() (filter uo-login-default-css); CSS must not be HTML-escaped.

//echo $default_js;

?>

	<div class="uo_error">
		<?php
		echo wp_kses_post( $login_error ); ?>
	</div>

	<?php if ( isset( $reset_password_sucess ) ) { ?>

	<p><?php echo wp_kses_post( $reset_password_sucess ); ?></p>

<?php } ?>

	<?php


$show_title        = Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' );
$show_description  = Config::get_settings_value( 'uo_frontendloginplus_hide_description', 'FrontendLoginPlus' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
$login_form_args   = apply_filters( 'uo_frontend_login_args', FrontendLoginPlus::fetch_login_form_args() );

if ( 'on' !== $show_title ) { ?>
	<h2><?php echo wp_kses_post( $innerText['Login-Title'] ); ?></h2>
	<?php
}
if ( 'on' !== $show_description ) { ?>

	<p class="uo-login-description">
		<?php echo wp_kses_post( $innerText['Login-Description'] ); ?>
	</p>

<?php }

if ( uo_toolkit_2fa_form_exists() ) {
	uo_toolkit_2fa_render_authentication_form();
} else {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook fired intentionally so other plugins can hook into this login form.
	do_action( 'login_form' );
	wp_login_form( $login_form_args );
}

// Add registration link allowed
if ( get_option( 'users_can_register' ) ) {
	$show_register = Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' );
	if ( 'on' === $show_register ) {
		echo '<a class="register-link" href="' . esc_url( Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ) ) . '" >' . wp_kses_post( $innerText['Register-Link'] ) . '</a>';
	}

}

?>

<script type='text/javascript'>
    jQuery(document).ready(function () {
        jQuery('#user_login').attr('required', 'required')
        jQuery('#user_pass').attr('required', 'required')
    })
</script>
