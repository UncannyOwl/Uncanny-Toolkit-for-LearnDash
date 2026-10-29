<?php
namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside Boot::uo_support_page(); variables are local to that method.

?>

<div class="wrap uncannyowl-default-design">
	<?php
	// Add admin header and tabs.
	$active_tab = 'uncanny-toolkit-plugins';
	if ( isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
		$active_tab = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
	}
	
	$tab_active = $active_tab;
	require Config::get_template( 'admin-header.php' );
	?>
	
	<?php do_action('uncanny_toolkit_dashboard_header_after'); ?>

	<?php

	if ( 'uncanny-toolkit-plugins' === $active_tab ) {
		include( 'admin-showcase.php' );
	} elseif ( 'uncanny-toolkit-kb' === $active_tab ) {
		if ( isset( $_GET['submit-a-ticket'] ) || isset( $_GET['amp;submit-a-ticket'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
			include( 'admin-help.php' );
		} else {
			include( 'admin-kb.php' );

			if ( Boot::is_pro_active() ) {

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
				$show_support_link = apply_filters( 'uo_show_support_link_toolkit_pro', true );

				if ( $show_support_link ) {
					?>
					<p class="uo-get-help">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=uncanny-toolkit-kb&submit-a-ticket=1' ) ); ?>"><?php esc_html_e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
					</p>

					<?php
				}
			} else {

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code).
					$show_support_link = apply_filters( 'uo_show_support_link_toolkit_free', true );

					if ( $show_support_link ) {
						?>

						<p class="uo-get-help">
							<a href="https://wordpress.org/support/plugin/uncanny-learndash-toolkit"
							   target="_blank"><?php esc_html_e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
						</p>

						<?php
					}
			}
		}

	} /*elseif ( 'submit-a-ticket' === $active_tab && Boot::is_pro_active() ) {
			include( 'admin-help.php' );
		} */ else {
		esc_html_e( 'Cheating, Eh?', 'uncanny-learndash-toolkit' );
	}

	?>
</div>