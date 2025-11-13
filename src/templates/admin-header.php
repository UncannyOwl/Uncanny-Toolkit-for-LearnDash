<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Variables:
 * $tab_active   The ID of the active tab
 */

$tabs = array(
	(object) array(
		'id'   => 'uncanny-toolkit',
		'href' => admin_url( 'admin.php?page=uncanny-toolkit' ),
		'name' => esc_html__( 'Modules', 'uncanny-learndash-toolkit' ),
	),
	(object) array(
		'id'   => 'uncanny-toolkit-kb',
		'href' => admin_url( 'admin.php?page=uncanny-toolkit-kb' ),
		'name' => esc_html__( 'Help', 'uncanny-learndash-toolkit' ),
	),
	(object) array(
		'id'   => 'uncanny-toolkit-plugins',
		'href' => admin_url( 'admin.php?page=uncanny-toolkit-plugins' ),
		'name' => esc_html__( 'LearnDash Plugins', 'uncanny-learndash-toolkit' ),
	),
);

?>

<div class="uncannyowl-header">
	<div class="uncannyowl-header-top">
		<div class="uncannyowl-header-top__content">
			<div class="uncannyowl-header-top__title">
				<?php esc_html_e( 'Uncanny Toolkit for LearnDash', 'uncanny-learndash-toolkit' ); ?>
			</div>
			<div class="uncannyowl-header-top__author">
				<span><?php esc_html_e( 'by', 'uncanny-learndash-toolkit' ); ?></span>
				<a href="<?php echo Config::utm_parameters( 'https://uncannyowl.com', 'header', 'logo' ); ?>" target="_blank" class="uncannyowl-header-top__logo uncannyowl-header-top__logo--svg">
					UncannyOwl
				</a>
			</div>
		</div>
		<div class="uncannyowl-header-top__social">
			<a href="https://www.facebook.com/UncannyOwl/" target="_blank"
				class="uncannyowl-header-social-icon"
				title="<?php esc_attr_e( 'Follow us on Facebook', 'uncanny-learndash-toolkit' ); ?>">
				<span class="uncannyowl-icon uncannyowl-icon--facebook"></span>
			</a>

			<a href="https://twitter.com/UncannyOwl" target="_blank"
				class="uncannyowl-header-social-icon"
				title="<?php esc_attr_e( 'Follow us on Twitter', 'uncanny-learndash-toolkit' ); ?>">
				<span class="uncannyowl-icon uncannyowl-icon--twitter"></span>
			</a>

			<a href="https://www.linkedin.com/company/uncannyowl" target="_blank"
				class="uncannyowl-header-social-icon"
				title="<?php esc_attr_e( 'Follow us on LinkedIn', 'uncanny-learndash-toolkit' ); ?>">
				<span class="uncannyowl-icon uncannyowl-icon--linkedin"></span>
			</a>
		</div>
	</div>

	<nav class="uncannyowl-nav-tab-wrapper">
		<?php foreach ( $tabs as $tab ) { ?>

			<?php

			// Define the extra CSS classes of the tab.
			$css_classes = array();

			// Check if it's the current tab.
			if ( $tab_active == $tab->id ) {
				$css_classes[] = 'uncannyowl-nav-tab-active';
			}

			?>

			<a href="<?php echo $tab->href; ?>" class="uncannyowl-nav-tab <?php echo implode( ' ', $css_classes ); ?>">
				<?php echo $tab->name; ?>
			</a>

		<?php } ?>
	</nav>
</div> 