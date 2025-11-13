<?php
namespace uncanny_learndash_toolkit;

$active_tab = 'uncanny-toolkit-plugins';
if ( isset( $_GET['page'] ) ) {
	$active_tab = $_GET['page'];
}
?>
<nav class="nav-tab-wrapper">
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-toolkit' ) ?>"
	   class="nav-tab <?php echo 'uncanny-toolkit' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'Modules', 'uncanny-learndash-toolkit' ) ?>
	</a>
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-toolkit-kb' ) ?>"
	   class="nav-tab <?php echo 'uncanny-toolkit-kb' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'Help', 'uncanny-learndash-toolkit' ) ?>
	</a>
	<a href="<?php echo admin_url( 'admin.php?page=uncanny-toolkit-plugins' ) ?>"
	   class="nav-tab <?php echo 'uncanny-toolkit-plugins' === $active_tab ? 'nav-tab-active' : ''; ?>">
		<?php _e( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ) ?>
	</a>
</nav>
