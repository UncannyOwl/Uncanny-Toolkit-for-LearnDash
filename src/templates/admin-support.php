<?php
namespace uncanny_learndash_toolkit;

?>
<div class="wrap">
	<div class="uo-admin-header">

		<a href="https://www.uncannyowl.com/" target="_blank">
			<img src="<?php use uncanny_ceu\Utilities;

			echo esc_url( Config::get_admin_media( 'Uncanny-Owl-logo.png' ) ); ?>"/>
		</a>

		<hr class="uo-underline">

		<h2><?php esc_html_e( 'Thank you for using the Uncanny LearnDash Toolkit!', 'uncanny-learndash-toolkit' ); ?></h2>

		<?php include( Config::get_template( 'admin-tabs.php' ) ) ?>
		<?php $active_tab = 'uncanny-learnDash-plugins';
		if ( isset( $_GET['page'] ) ) {
			$active_tab = $_GET['page'];
		}
		?>

		<div class="page content contents rest-data">
			<?php
			if ( 'uncanny-learnDash-plugins' === $active_tab ) {
				include( 'admin-showcase.php' );
			} elseif ( 'uncanny-learnDash-toolkit-kb' === $active_tab ) {
				if ( isset( $_GET['submit-a-ticket'] ) ) {
					include( 'admin-help.php' );
				} else {
					include( 'admin-kb.php' );
				}
				if ( Boot::is_pro_active() ) {
					?>
					<p class="uo-get-help">
						<a href="<?php echo admin_url( 'admin.php?page=uncanny-learnDash-toolkit-kb&submit-a-ticket=1' ); ?>"><?php _e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
					</p>
					<?php
				} else {
					?>
					<p class="uo-get-help">
						<a href="https://wordpress.org/support/plugin/uncanny-learndash-toolkit" target="_blank"><?php _e( 'I can\'t find the answer to my question.', 'uncanny-learndash-toolkit' ) ?></a>
					</p>
					<?php
				}
			} /*elseif ( 'submit-a-ticket' === $active_tab && Boot::is_pro_active() ) {
				include( 'admin-help.php' );
			} */ else {
				esc_html_e( 'Cheating, Eh?', 'uncanny-learndash-toolkit' );
			}
			?>
		</div>
	</div>