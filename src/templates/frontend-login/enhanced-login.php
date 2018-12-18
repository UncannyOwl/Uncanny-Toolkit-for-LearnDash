<?php

namespace uncanny_learndash_toolkit;

/**
 * Get required data to render the login form
 */

$login = (object) [
	'config'  => (object) [
		'show_title'       => Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' ) !== 'on',
		// To-do: Create this checkbox in the settings
		'show_description' => true,
		'can_register'     => get_option( 'users_can_register' ) && Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' ) == 'on',
	],
	'urls'    => (object) [
		'register'         => Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ),
	],
	'strings' => (object) [
		'error'            => $login_error,
		'title'            => $innerText[ 'Login-Title' ],
		// To-do: Create field for description
		'description'      => 'Log in to your QuickBooks Training account to access to your courses.',
		'email_label'      => Config::get_settings_value( 'uo_login_username_label', 'FrontendLoginPlus' ),
		'register'         => $innerText['Register-Link'],
		'forgot_password'  => __( 'I forgot my Password', 'uncanny-learndash-toolkit' )
	]
];

/**
 * Modify default arguments of the WP Login form
 * Missing elements are defined in the fetch_login_from_args method of
 * the FrontendLoginPlus class: src/classes/frontend-login-plus.php
 */

$login_form_args = apply_filters( 'uo_frontend_login_args', array_merge( FrontendLoginPlus::fetch_login_form_args( $login->strings->email_label ), [
	'form_id'     => 'ult-login-form',
	'id_username' => 'ult-login-email',
	'id_password' => 'ult-login-password',
	'id_remember' => 'ult-login-remember',
	'id_submit'   => 'ult-login-submit',
]));

/**
 * Change "Forgot Password" text
 */

add_filter( 'login_form_bottom', function( $content ){
	// Remove Forgot Password link
	$content = preg_replace( '/(<a(?:.*?)class="forgot-link"(?:.*?)>)(?:.*?)(<\/a>)/is', '', $content );

	// Return content
	return $content;
}, 10, 1 );

/**
 * Add error messages above the submit button
 */

add_filter( 'login_form_middle', function( $content ){
	// Get submission error
	$error = FrontendLoginPlus::get_error();

	// Check if there are errors
	$has_errors = ! empty( $error );

	// Do only if there are errors
	if ( $has_errors ){
		// Start output
		ob_start();

		// Do uo_login_before_validation
		do_action( 'uo_login_before_validation' );

		?>

		<div class="ult-form__validation">
			<div class="ult-notice ult-notice--error">
				<?php do_action( 'uo_login_before_validation_message' ); ?>

				<?php echo $error; ?>

				<?php do_action( 'uo_login_after_validation_message' ); ?>
			</div>
		</div>
		
		<?php

		// End output
		$output = ob_get_clean();

		// Add output to the current content, but at the bottom
		$content .= $output;
	}

	return $content;
}, 10, 1 );

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

		<?php do_action( 'uo_login_before_register' ); ?>

		<?php if ( $login->config->can_register ){ ?>

			<div class="ult-form__footer">
				<div class="ult-form-footer__forgot-password">
					<a href="<?php echo wp_lostpassword_url(); ?>">
						<?php echo $login->strings->forgot_password; ?>
					</a>
				</div>
				<div class="ult-form-footer__signup">
					<?php printf( 'New User? %s', sprintf( '<a href="%s">%s</a>', $login->urls->register, $login->strings->register ) ); ?>
				</div>
			</div>

		<?php } ?>

		<?php do_action( 'uo_login_after_register' ); ?>

	</div>

</div>

<?php do_action( 'uo_login_after_container' ); ?>