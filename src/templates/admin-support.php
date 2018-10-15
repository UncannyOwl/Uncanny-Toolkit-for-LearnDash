<?php
namespace uncanny_learndash_toolkit;

?>
<div class="wrap">
	<div class="uo-admin-header">

		<a href="https://www.uncannyowl.com/" target="_blank">
			<img src="<?php use uncanny_ceu\Utilities;

			echo esc_url( self::get_admin_media( 'Uncanny-Owl-logo.png' ) ); ?>"/>
		</a>

		<hr class="uo-underline">

		<h2><?php esc_html_e( 'Thank you for using the Uncanny LearnDash Toolkit!', 'uncanny-learndash-toolkit' ); ?></h2>

		<?php
		$active_tab = 'showcase';
		if ( isset( $_GET['tab'] ) ) {
			$active_tab = $_GET['tab'];
		}
		?>

		<h2 class="nav-tab-wrapper">
			<a href="?page=uncanny-learnDash-toolkit-support&tab=showcase" class="nav-tab <?php echo 'showcase' === $active_tab ? 'nav-tab-active' : ''; ?>">
				Showcase
			</a>
			<a href="?page=uncanny-learnDash-toolkit-support&tab=knowledge-base" class="nav-tab <?php echo 'knowledge-base' === $active_tab ? 'nav-tab-active' : ''; ?>">
				Knowledge Base
			</a>
			<?php if ( Boot::is_pro_active() ) { ?>
				<a href="?page=uncanny-learnDash-toolkit-support&tab=submit-a-ticket" class="nav-tab <?php echo 'submit-a-ticket' === $active_tab ? 'nav-tab-active' : ''; ?>">
					Submit a Ticket
				</a>
			<?php } ?>
		</h2>
		<div class="page content contents rest-data">
			<?php
			if ( 'showcase' === $active_tab ) {
				include( 'admin-showcase.php' );
			} elseif ( 'knowledge-base' === $active_tab ) {
				include( 'admin-kb.php' );
			} elseif ( 'submit-a-ticket' === $active_tab && Boot::is_pro_active() ) {
				include( 'admin-help.php' );
			} else {
				esc_html_e( 'Cheating, Eh?', 'uncanny-learndash-toolkit' );
			}
			?>
		</div>
	</div>