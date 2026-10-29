<?php
/**
 * Dynamic render for uncanny-toolkit/frontend-login.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\FrontendLoginPlus::dependants_exist() ) {
	return;
}

echo \uncanny_learndash_toolkit\FrontendLoginPlus::uo_login_ui(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same markup as the [uo_login_ui] shortcode, built by FrontendLoginPlus::uo_login_ui() and its templates (escaped there); wp_kses_post() would strip the form fields.
