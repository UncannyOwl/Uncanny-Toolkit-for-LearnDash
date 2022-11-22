<?php
namespace uncanny_learndash_toolkit;

?>

<div class="wrap">
	<div class="uo-plugins-header">
		<div class="uo-plugins-header__title">
			Uncanny Toolkit for LearnDash
		</div>
		<div class="uo-plugins-header__author">
			<span><?php _e( 'by', 'uncanny-learndash-toolkit' ); ?></span>
			<a href="https://uncannyowl.com" target="_blank" class="uo-plugins-header__logo">
				<img src="<?php echo esc_url( Config::get_admin_media( 'uncanny-owl-logo.svg' ) ); ?>"
					 alt="Uncanny Owl">
			</a>
		</div>
		<?php do_action('uncanny_toolkit_dashboard_header_after'); ?>
	</div>

	<div class="uo-plugins-tabs">
		<?php include( Config::get_template( 'admin-tabs.php' ) ); ?>

		<?php

		$active_tab = 'uncanny-toolkit-plugins';

		if ( isset( $_GET['page'] ) ) {
			$active_tab = $_GET['page'];
		}

		?>
	</div>

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