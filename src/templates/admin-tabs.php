<?php
namespace uncanny_learndash_toolkit;

$active_tab = 'uncanny-learnDash-plugins';
if ( isset( $_GET['page'] ) ) {
	$active_tab = $_GET['page'];
}
?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-learnDash-toolkit' ) ?>" class="nav-tab <?php echo 'uncanny-learnDash-toolkit' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'Modules', 'uncanny-learndash-toolkit' ) ?>
	</a>
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-learnDash-toolkit-kb' ) ?>" class="nav-tab <?php echo 'uncanny-learnDash-toolkit-kb' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'Help', 'uncanny-learndash-toolkit' ) ?>
	</a>
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-learnDash-plugins' ) ?>" class="nav-tab <?php echo 'uncanny-learnDash-plugins' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ) ?>
	</a>
	<?php if ( Boot::is_pro_active() ) { ?>
		<a href="<?php echo admin_url( 'admin.php?page=uncanny-pro-license-activation' ) ?>" class="nav-tab <?php echo 'uncanny-pro-license-activation' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php _e( 'License Activation', 'uncanny-learndash-toolkit' ) ?>
		</a>
	<?php }
	?>
</h2>


