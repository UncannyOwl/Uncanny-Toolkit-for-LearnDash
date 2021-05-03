<?php

namespace uncanny_learndash_toolkit;

/**
 * Get required data to render the login form
 */

$login = ( isset( $_GET['login'] ) ) ? $_GET['login'] : 'not-set';

switch ( $login ){
	case 'failed':
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_failed_error', 'FrontendLoginPlus', esc_html__( 'Invalid username and/or password.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'empty':
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_empty_error', 'FrontendLoginPlus', esc_html__( 'Username and/or Password is empty.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'false':
		// Empty msgid. It is reserved by GNU gettext: gettext("") returns the header entry with meta information, not the empty string.
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_false_error', 'FrontendLoginPlus', esc_html__( 'You are logged out.', 'uncanny-learndash-toolkit' ) );
		break;
	case 'notverified':
		$message_warning = \uncanny_learndash_toolkit\Config::get_settings_value( 'uo_frontend_login_notverified_error', 'FrontendLoginPlus', esc_html__( 'This account is not verified.', 'uncanny-learndash-toolkit' ) );
		break;
}

$login_page      = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();
$login_page_url  = get_permalink( $login_page );
$recaptcha_key  = Config::get_settings_value( 'uo_frontend_login_recaptcha_key', 'FrontendLoginPlus' );
$recaptcha_secrete_key  = Config::get_settings_value( 'uo_frontend_login_recaptcha_secret_key', 'FrontendLoginPlus' );

if ( strpos( $login_page_url, '?' ) ) {
	$login_page_url = $login_page_url . '&';
}
else {
	$login_page_url = $login_page_url . '?';
}

$login_error = '';

if ( $message_warning ){
	$login_error = $message_warning;
}

$login_error = apply_filters( 'uo_frontend_login_error', $login_error, $login, '', $message_warning );

$login = (object) [
	'config'  => (object) [
		'show_title'       => Config::get_settings_value( 'uo_frontendloginplus_hide_title_label', 'FrontendLoginPlus' ) !== 'on',
		// To-do: Create this checkbox in the settings
		'show_description' => Config::get_settings_value( 'uo_frontendloginplus_hide_description', 'FrontendLoginPlus' ) !== 'on',
		'can_register'     => Config::get_settings_value( 'uo_frontend_show_register_link', 'FrontendLoginPlus' ) == 'on',
	],
	'urls'    => (object) [
		'login'            => $login_page_url,
		'register'         => Config::get_settings_value( 'uo_frontend_register_link', 'FrontendLoginPlus', wp_registration_url() ),
		'forgot_password'  => $login_page_url . 'action=lostpassword',
	],
	'strings' => (object) [
		'error'            => $login_error,
		'title'            => Config::get_settings_value( 'uo_frontend_login_title_label', 'FrontendLoginPlus', '%placeholder%', self::get_class_settings( '', true ) ),
		// To-do: Create field for description
		'description'      => Config::get_settings_value( 'uo_frontend_login_description', 'FrontendLoginPlus', '%placeholder%', self::get_class_settings( '', true ) ),
		'register'         => Config::get_settings_value( 'uo_frontend_register_link_text', 'FrontendLoginPlus', '%placeholder%', self::get_class_settings( '', true ) ),
		'forgot_password'  => Config::get_settings_value( 'uo_frontend_login_forgetpass_label', 'FrontendLoginPlus', '%placeholder%', self::get_class_settings( '', true ) ),
	],
];

/**
 * Modify default arguments of the WP Login form
 * Missing elements are defined in the fetch_login_from_args method of
 * the FrontendLoginPlus class: src/classes/frontend-login-plus.php
 */

$login_form_args = apply_filters( 'uo_frontend_login_args', array_merge( FrontendLoginPlus::fetch_login_form_args(), [
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

	// Validation classes
	$css_classes = [];

	// Check if there are errors
	$has_errors = ! empty( $error );

	// Add a CSS class if it has an error
	if ( $has_errors ){
		$css_classes[] = 'ult-form__validation--has-error';
	}

	// Start output
	ob_start();

	// Do uo_login_before_validation
	do_action( 'uo_login_before_validation' );

	?>

	<div class="ult-form__validation <?php echo implode( ' ', $css_classes ); ?>">
		<div class="ult-notice ult-notice--error">
			<?php do_action( 'uo_login_before_validation_message' ); ?>

			<span class="ult-notice-text"><?php echo $error; ?></span>

			<?php do_action( 'uo_login_after_validation_message' ); ?>
		</div>
	</div>

	<?php

	// End output
	$output = ob_get_clean();

	// Add output to the current content, but at the bottom
	$content .= $output;

	return $content;
}, 10, 1 );

?>

<?php do_action( 'uo_login_before_container' ); ?>

<div id="ult-login">

	<div class="ult-box">

		<div class="ult-form__content">
			
			<?php if ( isset( $reset_password_sucess ) && ! empty( $reset_password_sucess ) ){ ?>

				<?php do_action( 'uo_login_before_reset_success' ); ?>

				<div class="ult-notice ult-notice--success">
					<?php do_action( 'uo_login_before_reset_success_message' ); ?>

					<?php echo $reset_password_sucess; ?>

					<?php do_action( 'uo_login_before_reset_success_message' ); ?>
				</div>

			<?php } ?>

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

				<?php
					if ( uo_toolkit_2fa_form_exists() ) {
						uo_toolkit_2fa_render_authentication_form();
					} else {
						wp_login_form( $login_form_args );
					}
				?>

			</div>

			<?php do_action( 'uo_login_before_footer' ); ?>

		</div>

		<div class="ult-form__footer">

			<?php do_action( 'uo_login_before_forgot_password' ); ?>

			<div class="ult-form-footer__forgot-password">
				<a id="ult-form-footer-forgot-password" href="<?php echo $login->urls->forgot_password; ?>">
					<?php echo $login->strings->forgot_password; ?>
				</a>
			</div>

			<?php if ( $login->config->can_register ){ ?>

				<?php do_action( 'uo_login_before_register' ); ?>

				<div class="ult-form-footer__signup">
					<a href="<?php echo $login->urls->register; ?>"><?php echo $login->strings->register; ?></a>
				</div>

				<?php do_action( 'uo_login_after_register' ); ?>

			<?php } ?>

		</div>

		<?php do_action( 'uo_login_after_footer' ); ?>

	</div>

</div>

<script type='text/javascript'>
	jQuery(document).ready(function () {
		jQuery('#ult-login-email').attr('required', 'required')
		jQuery('#ult-login-password').attr('required', 'required')
	})
</script>

<?php do_action( 'uo_login_after_container' ); ?>