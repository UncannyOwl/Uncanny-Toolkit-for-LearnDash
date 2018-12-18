<?php

namespace uncanny_learndash_toolkit;

$login = (object) [
	'config'  => (object) [
		'show_title'       => Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' ) !== 'on',
		// To-do: Create this checkbox in the settings
		'show_description' => true,
	],
	'strings' => (object) [
		'title'            => $innerText[ 'Login-Title' ],
		// To-do: Create field for description
		'description'      => 'Log in to your QuickBooks Training account to access to your courses.',
		'email_label'      => Config::get_settings_value( 'uo_login_username_label', 'FrontendLoginPlus' ),
	]
];

$login_form_args = apply_filters( 'uo_frontend_login_args', FrontendLoginPlus::fetch_login_form_args( $login->strings->email_label ) );

?>

<?php do_action( 'uo_login_before_container' ); ?>

<div id="ult-login">

	<div class="ult-box">

		<?php do_action( 'uo_login_before_title' ); ?>

		<?php if ( $login->config->show_title ){ ?>

			<div class="ult-form__title">
				<?php echo $login->strings->title; ?>
			</div>

		<?php } ?>

		<?php do_action( 'uo_login_before_description' ); ?>

		<?php if ( $login->config->show_description ){ ?>

			<div class="ult-form__description">
				<?php echo $login->strings->description; ?>
			</div>

		<?php } ?>

		<?php do_action( 'uo_login_before_form' ); ?>

		<div class="ult-form__container">

			<?php wp_login_form( $login_form_args ); ?>

		</div>

		<?php do_action( 'uo_login_after_form' ); ?>

	</div>

</div>

<?php do_action( 'uo_login_after_container' ); ?>

<?php

// Add registration link allowed
if ( get_option( 'users_can_register' ) ) {
	$show_register = Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' );
	if ( 'on' === $show_register ) {
		echo '<a class="register-link" href="' . Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ) . '" >' . $innerText['Register-Link'] . '</a>';
	}

}

?>