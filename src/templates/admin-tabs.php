<?php
namespace uncanny_learndash_toolkit;

$active_tab = 'uncanny-toolkit-plugins';
if ( isset( $_GET['page'] ) ) {
	$active_tab = $_GET['page'];
}
?>
<h2 class="nav-tab-wrapper">
	<div class="ult">
		<div class="ult-admin-nav">
			<div class="ult-admin-nav-items">
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
			</div>

			<div class="ult-admin-nav-social-icons">
				<a href="<?php echo Config::utm_parameters( 'https://www.facebook.com/UncannyOwl/', 'header', 'social_link' ); ?>"
				   target="_blank" class="ult-admin-nav-social-icon ult-admin-nav-social-icon--facebook"
				   ult-tooltip-admin="<?php _e( 'Follow us on Facebook', 'uncanny-learndash-toolkit' ); ?>">
					<span class="ult-icon ult-icon-facebook"></span>
				</a>
				<a href="<?php echo Config::utm_parameters( 'https://twitter.com/UncannyOwl', 'header', 'social_link' ); ?>"
				   target="_blank" class="ult-admin-nav-social-icon ult-admin-nav-social-icon--twitter"
				   ult-tooltip-admin="<?php _e( 'Follow us on Twitter', 'uncanny-learndash-toolkit' ); ?>">
					<span class="ult-icon ult-icon-twitter"></span>
				</a>
				<a href="<?php echo Config::utm_parameters( 'https://www.linkedin.com/company/uncannyowl', 'header', 'social_link' ); ?>"
				   target="_blank" class="ult-admin-nav-social-icon ult-admin-nav-social-icon--linkedin"
				   ult-tooltip-admin="<?php _e( 'Follow us on LinkedIn', 'uncanny-learndash-toolkit' ); ?>">
					<span class="ult-icon ult-icon-linkedin"></span>
				</a>
			</div>
		</div>
	</div>
</h2>
