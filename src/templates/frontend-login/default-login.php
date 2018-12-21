<?php

namespace uncanny_learndash_toolkit;

echo $default_css;

//echo $default_js;

?>

<div class="uo_error">
	<?php
	echo $login_error; ?>
</div>

<?php if ( isset( $reset_password_sucess ) ){ ?>

<p><?php echo $reset_password_sucess; ?></p>

<?php } ?>

<?php

$user_name_label   = Config::get_settings_value( 'uo_login_username_label', 'FrontendLoginPlus' );
$show_label        = Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' );
$login_form_args   = apply_filters( 'uo_frontend_login_args', FrontendLoginPlus::fetch_login_form_args( $user_name_label ) );
$login_description = Config::get_settings_value( 'uo_frontend_login_description', 'FrontendLoginPlus', esc_html__( 'Login to your account to access your courses.', 'uncanny-learndash-toolkit' ) );

if ( 'on' !== $show_label ) { ?>
	<h2><?php echo $innerText['Login-Title']; ?></h2>

	<?php if ( ! empty( $login_description ) ){ ?>
		
		<p class="uo-login-description">
			<?php echo $login_description; ?>
		</p>

	<?php } ?>

	<?php
}
wp_login_form( $login_form_args );

// Add registration link allowed
if ( get_option( 'users_can_register' ) ) {
	$show_register = Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' );
	if ( 'on' === $show_register ) {
		echo '<a class="register-link" href="' . Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ) . '" >' . $innerText['Register-Link'] . '</a>';
	}

}