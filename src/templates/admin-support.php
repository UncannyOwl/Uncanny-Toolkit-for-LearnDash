<?php
namespace uncanny_learndash_toolkit;

?>

<div class="wrap uncannyowl-default-design">
	<?php
	// Add admin header and tabs.
	$active_tab = 'uncanny-toolkit-plugins';
	if ( isset( $_GET['page'] ) ) {
		$active_tab = $_GET['page'];
	}
	
	$tab_active = $active_tab;
	require Config::get_template( 'admin-header.php' );
	?>
	
	<?php do_action('uncanny_toolkit_dashboard_header_after'); ?>

	<?php

	if ( 'uncanny-toolkit-plugins' === $active_tab ) {
		include( 'admin-showcase.php' );
	} elseif ( 'uncanny-toolkit-kb' === $active_tab ) {
		if ( isset( $_GET['submit-a-ticket'] ) || isset( $_GET['amp;submit-a-ticket'] ) ) {
			include( 'admin-help.php' );
		} else {
			include( 'admin-kb.php' );

			if ( Boot::is_pro_active() ) {

				$show_support_link = apply_filters( 'uo_show_support_link_toolkit_pro', true );

				if ( $show_support_link ) {
					?>
					<p class="uo-get-help">
						<a href="<?php echo admin_url( 'admin.php?page=uncanny-toolkit-kb&submit-a-ticket=1' ); ?>"><?php _e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
					</p>

					<?php
				}
			} else {

					$show_support_link = apply_filters( 'uo_show_support_link_toolkit_free', true );

					if ( $show_support_link ) {
						?>

						<p class="uo-get-help">
							<a href="https://wordpress.org/support/plugin/uncanny-learndash-toolkit"
							   target="_blank"><?php _e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
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