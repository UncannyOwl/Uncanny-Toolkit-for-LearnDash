<?php
/**
 * Custom 2fa functions we can use anywhere in the code.
 *
 * @since 3.4.4
 */
defined( 'ABSPATH' ) || exit;

/**
 * Hook the 2fa bootstrap function to 'plugins_loaded'.
 */
add_action( 'plugins_loaded', 'uo_toolkit_2fa_init', 99 );

/**
 * Register the one click install enpoint and js.
 */
add_action( 'admin_init', 'uo_toolkit_2fa_oci_ajax_actions', 99 );

/**
 * Loads the 2fa dependencies.
 *
 * @return boolean True if 2FA login class is loaded. Otherwise, false.
 */
function uo_toolkit_2fa_load_dependencies() {

	$class_loaded_two_factor = false;

	// Check if Two Factor is active or not.
	if ( class_exists( '\WP2FA\Authenticator\Login' ) ) {

		// Defaults to 2.0.0.
		$wp2fa_version = '2.0.0';

		$module = UNCANNY_TOOLKIT_DIR . '/src/includes/class-frontend-login-plus-2fa.php';

		if ( defined( 'WP_2FA_VERSION' ) ) {
			$wp2fa_version = WP_2FA_VERSION;
		}

		// Load the legacy class for wp2fa version below 2.0.0.
		if ( version_compare( $wp2fa_version, '2.0.0', '<' ) ) {
			$module = UNCANNY_TOOLKIT_DIR . '/src/includes/class-frontend-login-plus-2fa-legacy.php';
		}

		// Load another unsupported class for wp2fa version below 2.0.0.
		if ( version_compare( $wp2fa_version, '2.4', '<' ) ) {
			$module = UNCANNY_TOOLKIT_DIR . '/src/includes/class-frontend-login-plus-2fa-2-3.php';
		}

		if ( version_compare( $wp2fa_version, '2.5', '<' ) ) {
			$module = UNCANNY_TOOLKIT_DIR . '/src/includes/class-frontend-login-plus-2fa-2-4.php';
		}

		if ( version_compare( $wp2fa_version, '2.6', '<' ) ) {
			$module = UNCANNY_TOOLKIT_DIR . '/src/includes/class-frontend-login-plus-2fa-2-5.php';
		}

		// Load front-end login 2fa.
		require_once $module;

		$class_loaded_two_factor = true;

	}

	return $class_loaded_two_factor;

}

/**
 * Initialize the Two-Factor Integration.
 *
 * @return \uncanny_learndash_toolkit\Frontend_Login_Plus_2fa The instance of '\uncanny_learndash_toolkit\Frontend_Login_Plus_2fa'.
 */
function uo_toolkit_2fa_init() {

	$integration = false;

	// Load the dependencies.
	$loaded = uo_toolkit_2fa_load_dependencies();

	// Check if \Two_Factor_Core class exists.
	if ( $loaded ) {
		$integration = new \uncanny_learndash_toolkit\Frontend_Login_Plus_2fa(
			new \WP2FA\Authenticator\Login()
		);
	}

	return $integration;

}

/**
 * Initialize an instance of \uncanny_learndash_toolkit\Auto_Plugin_Install.
 *
 * Only creates new instance of Auto_Plugin_Install if it doesn't exists.
 *
 * @return \uncanny_learndash_toolkit\Auto_Plugin_Install The instance of '\uncanny_learndash_toolkit\Auto_Plugin_Install'.
 */
function uo_toolkit_oci_instance() {

	// Load the library.
	require_once UNCANNY_TOOLKIT_DIR . '/src/uncanny-one-click-installer/class-auto-plugin-install.php';

	// We're using static here to avoid multiple instances of auto plugin install class.
	static $oci_instance = false;

	if ( class_exists( '\uncanny_one_click_installer\Auto_Plugin_Install' ) ) {
		// Checking if $oci_instance is already instantiated before.
		if ( $oci_instance instanceof \uncanny_one_click_installer\Auto_Plugin_Install ) {
			return $oci_instance;
		}
		$oci_instance = new \uncanny_one_click_installer\Auto_Plugin_Install();
	}

	// Return the instance.
	return $oci_instance;

}

/**
 * Use this function to create an endpoint for the OCI button.
 *
 * This usually goes into class construct file or anywhere as long is its called before 'wp_enqueue_scripts' hook.
 *
 * @return boolean True after create_ajax method. Otherwise, false.
 */
function uo_toolkit_2fa_oci_ajax_actions() {

	$page = filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW );

	// Allow when requesting.
	if ( ! wp_doing_ajax() ) {

		// Bail out if called from non admin page.
		if ( ! is_admin() ) {
			return false;
		}

		// Bail out if called from non 'uncanny-toolkit' page.
		if ( 'uncanny-toolkit' !== $page ) {
			return false;
		}
	}

	if ( uo_toolkit_oci_instance() ) {

		uo_toolkit_oci_instance()->create_ajax();

		return true;

	}

	return false;

}

/**
 * Use this function to create an OCI button.
 *
 * This usually goes into your template file.
 *
 * @return mixed The button HTML. Otherwise, false.
 */
function uo_toolkit_2fa_oci_button() {

	if ( uo_toolkit_oci_instance() ) {

		ob_start();
		?>
		<div class="ult-modal-form-row__label">
			<?php esc_html_e( 'Two-Factor authentication', 'uncanny-learndash-toolkit' ); ?>
		</div>

		<?php if ( class_exists( '\WP2FA\Authenticator\Login' ) ) : ?>
			<div class="ult-modal-form-row__field">
				<p class="ult-modal-form-row__description">
					<?php esc_html_e( 'Uncanny Toolkit integrates with the free WP 2FA plugin to support two factor login authentication.', 'uncanny-learndash-toolkit' ); ?>
				</p>
				<p class="ult-modal-form-row__description">
					<?php $login_page = \uncanny_learndash_toolkit\FrontendLoginPlus::get_settings_value( 'login_page', 'FrontendLoginPlus', '' ); ?>
					<strong>
						<?php
						if ( ! empty( $login_page ) ) :
							echo sprintf(
								/* translators: %s will be replaced with a "string" - state of the plugin */
								esc_html__( 'WP 2FA is currently %s.', 'uncanny-learndash-toolkit' ),
								'<span style="color:#669b07">' . esc_html__( 'active', 'uncanny-learndash-toolkit' ) . '</span>'
							);
							else :
								echo sprintf(
									/* translators: %s will be replaced with a "string" - state of the plugin */
									esc_html__( 'Integration is currently %s. Choose a login page in the Front End Logins settings above to use WP 2FA with the front end login.', 'uncanny-learndash-toolkit' ),
									'<span style="color:#aaaaaa">' . esc_html__( 'inactive', 'uncanny-learndash-toolkit' ) . '</span>'
								);
							endif;
							?>
					</strong>
				</p>

				<p class="ult-modal-form-row__description">

					<?php esc_html_e( 'Go to', 'uncanny-learndash-toolkit' ); ?>

					<a target="_blank"
						href="<?php echo esc_url( admin_url( 'admin.php?page=wp-2fa-policies' ) ); ?>"
						title="<?php esc_attr_e( 'WP-ADMIN > Settings > Two-factor Authentication', 'uncanny-learndash-toolkit' ); ?>">
						<?php esc_html_e( 'WP 2FA Settings', 'uncanny-learndash-toolkit' ); ?></a>

					<?php esc_html_e( 'to configure 2FA settings.', 'uncanny-learndash-toolkit' ); ?>

				</p>

				<p class="ult-modal-form-row__description">
					<?php
					echo sprintf(
						/* translators: %1$s The uninstall text %2$s The WP 2FA plugin label */
						esc_html__( '%1$s the %2$s plugin to remove the integration.', 'uncanny-learndash-toolkit' ),
						'<a style="color: #b32d2e;" target="_blank" href="' . esc_url( admin_url( 'plugins.php?plugin_status=active' ) ) . '" title="' . esc_attr__( 'Show active plugins', 'uncanny-learndash-toolkit' ) . '">' . esc_html__( 'Uninstall/Disable', 'uncanny-learndash-toolkit' ) . '</a>',
						'<code>' . esc_html__( 'WP 2FA – Two-factor', 'uncanny-learndash-toolkit' ) . '</code>'
					);

					?>
				</p>
			</div>
		<?php else : ?>
			<!-- Two Factor Fields Start -->
			<div class="ult-modal-form-row__field">

				<p class="ult-modal-form-row__description">
					<?php esc_html_e( 'Uncanny Toolkit integrates with the free WP 2FA plugin to support two factor login authentication.', 'uncanny-learndash-toolkit' ); ?>
				</p>

				<?php echo uo_toolkit_oci_instance()->button( 'wp-2fa' ); //phpcs:ignore ?>

				<p class="ult-modal-form-row__description">

					<small>

						<a href="https://wordpress.org/plugins/wp-2fa/" title="<?php esc_attr_e( 'WP 2FA', 'uncanny-learndash-toolkit' ); ?>" target="_blank">
							<?php esc_html_e( 'WP 2FA', 'uncanny-learndash-toolkit' ); ?>
						</a>

						<?php esc_html_e( 'is a free two-factor authentication plugin for WordPress. WP 2FA is a third-party plugin managed by', 'uncanny-learndash-toolkit' ); ?>

						<a href="https://profiles.wordpress.org/wpwhitesecurity/" title="<?php esc_attr_e( 'WP White Security', 'uncanny-learndash-toolkit' ); ?>" target="_blank">
							<?php esc_html_e( 'WP White Security.', 'uncanny-learndash-toolkit' ); ?>
						</a>

					</small>

				</p>
			</div>
			<!-- Two Factor Fields End -->
			<?php
		endif;

		return ob_get_clean();

	}

	return false;

}

/**
 * Check whether Two_Factor_Core exists and if the request is set to show the form.
 *
 * @return boolean True if Two_Factor_Core exists and GET request for '2fa_authentication' is set.
 */
function uo_toolkit_2fa_form_exists() {

	$auth = filter_input( INPUT_GET, '2fa_authentication', FILTER_UNSAFE_RAW );

	return ( ! empty( $auth ) ) && class_exists( '\WP2FA\Authenticator\Login' );

}

/**
 * Renders the HTML form of the 2FA form.
 *
 * @return string The 2FA login form.
 */
function uo_toolkit_2fa_render_authentication_form() {

	$two_factor = new \uncanny_learndash_toolkit\Frontend_Login_Plus_2fa(
		new \WP2FA\Authenticator\Login()
	);

	$two_factor->login_form();

}

/**
 * Returns a string that can be used as an id for the authenticator form.
 *
 * @return string 'ult-2fa-validation-form--templated' when the selected template is 'Boxed with Shadow'. Otherwise, 'ult-2fa-validation-form--default'.
 */
function uo_toolkit_2fa_get_form_id() {

	$form_id = 'ult-2fa-validation-form--default';

	$uo_frontend_login_template = \uncanny_learndash_toolkit\FrontendLoginPlus::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', '' );

	if ( 'default' !== $uo_frontend_login_template ) {
		$form_id = 'ult-2fa-validation-form--templated';
	}

	return $form_id;

}

add_filter( 'uncanny_one_click_install_button_class', 'uo_toolkit_uncanny_one_click_install_button_class', 10, 2 );

/**
 * Adds new classes to the WP2FA button.
 *
 * @param array $classes The accepted classes.
 * @return array $classes The classes collection.
 */
function uo_toolkit_uncanny_one_click_install_button_class( $classes, $plugin_info ) {
	if ( 'wp-2fa' === $plugin_info->slug ) {
		$classes[] = 'ult-modal-action__btn--secondary';
		$classes[] = 'ult-modal-action__btn';
	}
	return $classes;
}


add_filter( 'uncanny_one_click_install_plugin_initial_text', 'uo_toolkit_uncanny_one_click_install_plugin_initial_text', 10, 2 );

/**
 * Change the `initial` text of WP 2FA Plugin.
 *
 * @param string $text The current plugin text.
 * @param object $plugin_info The plugin info fetch from wp.org plugins directory.
 *
 * @return string The new button text.
 */
function uo_toolkit_uncanny_one_click_install_plugin_initial_text( $text, $plugin_info ) {
	if ( 'wp-2fa' === $plugin_info->slug ) {
		return 'Install WP 2FA plugin'; // Do not translate plugin name.
	}

	return $text;
}

add_filter( 'uncanny_one_click_install_plugin_installed_text', 'uo_toolkit_uncanny_one_click_install_plugin_installed_text', 10, 2 );

/**
 * Change the `installed` text of WP 2FA Plugin.
 *
 * @param string $text The current plugin text.
 * @param object $plugin_info The plugin info fetch from wp.org plugins directory.
 *
 * @return string The new button text.
 */
function uo_toolkit_uncanny_one_click_install_plugin_installed_text( $text, $plugin_info ) {

	if ( 'wp-2fa' === $plugin_info->slug ) {
		return 'Activate WP 2FA plugin'; // Do not translate plugin name.
	}

	return $text;

}

add_filter( 'uncanny_one_click_install_plugin_active_text', 'uo_toolkit_uncanny_one_click_install_plugin_active_text', 10, 2 );

/**
 * Change the `active` text of WP 2FA Plugin.
 *
 * @param string $text The current plugin text.
 * @param object $plugin_info The plugin info fetch from wp.org plugins directory.
 *
 * @return string The new button text.
 */
function uo_toolkit_uncanny_one_click_install_plugin_active_text( $text, $plugin_info ) {
	if ( 'wp-2fa' === $plugin_info->slug ) {
		return 'WP 2FA plugin is Active'; // Do not translate plugin name.
	}

	return $text;
}
