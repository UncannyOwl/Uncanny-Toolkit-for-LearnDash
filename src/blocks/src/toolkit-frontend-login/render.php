<?php
/**
 * Dynamic render for uncanny-toolkit/frontend-login.
 *
 * @var array $attributes Block attributes provided by WordPress.
 */

if ( ! class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus' ) ) {
	return;
}

if ( true !== \uncanny_learndash_toolkit\FrontendLoginPlus::dependants_exist() ) {
	return;
}

echo \uncanny_learndash_toolkit\FrontendLoginPlus::uo_login_ui();
