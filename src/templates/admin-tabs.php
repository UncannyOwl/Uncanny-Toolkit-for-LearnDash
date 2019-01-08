<?php
namespace uncanny_learndash_toolkit;

$active_tab = 'uncanny-toolkit-plugins';
if ( isset( $_GET['page'] ) ) {
	$active_tab = $_GET['page'];
}
?>
<h2 class="nav-tab-wrapper">
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
	<?php if ( Boot::is_pro_active() ) { ?>

		<?php
		if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
			$compare_version = version_compare( UNCANNY_TOOLKIT_PRO_VERSION, '3.0' );

			if ( 0 > $compare_version ) {
				?>
				<a href="<?php echo admin_url( 'admin.php?page=uncanny-pro-license-activation' ) ?>"
				   class="nav-tab <?php echo 'uncanny-toolkit-license' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'License Activation', 'uncanny-learndash-toolkit' ) ?>
				</a>
				<?php
			}else{
				?>
				<a href="<?php echo admin_url( 'admin.php?page=uncanny-toolkit-license' ) ?>"
				   class="nav-tab <?php echo 'uncanny-toolkit-license' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'License Activation', 'uncanny-learndash-toolkit' ) ?>
				</a>
				<?php
			}

		}
	}
	?>
</h2>


