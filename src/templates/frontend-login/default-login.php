<?php

namespace uncanny_learndash_toolkit;

echo $default_css;

//echo $default_js;

?>

	<div class="uo_error">
		<?php
		echo $login_error; ?>
	</div>

	<?php if ( isset( $reset_password_sucess ) ) { ?>

	<p><?php echo $reset_password_sucess; ?></p>

<?php } ?>

	<?php


$show_title        = Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' );
$show_description  = Config::get_settings_value( 'uo_frontendloginplus_hide_description', 'FrontendLoginPlus' );
$login_form_args   = apply_filters( 'uo_frontend_login_args', FrontendLoginPlus::fetch_login_form_args() );

if ( 'on' !== $show_title ) { ?>
	<h2><?php echo $innerText['Login-Title']; ?></h2>
	<?php
}
if ( 'on' !== $show_description ) { ?>

	<p class="uo-login-description">
		<?php echo $innerText['Login-Description']; ?>
	</p>

<?php }

if ( uo_toolkit_2fa_form_exists() ) {
	uo_toolkit_2fa_render_authentication_form();
} else {
	wp_login_form( $login_form_args );
}

// Add registration link allowed
if ( get_option( 'users_can_register' ) ) {
	$show_register = Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' );
	if ( 'on' === $show_register ) {
		echo '<a class="register-link" href="' . Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ) . '" >' . $innerText['Register-Link'] . '</a>';
	}

}

?>

<script type='text/javascript'>
    jQuery(document).ready(function () {
        jQuery('#user_login').attr('required', 'required')
        jQuery('#user_pass').attr('required', 'required')
    })
</script>
