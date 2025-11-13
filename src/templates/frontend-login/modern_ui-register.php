<?php

namespace uncanny_learndash_toolkit;
include_once( 'default-register.php' );

// Add Modern UI class if selected
if ( FrontendLoginPlus::is_modern_ui() ) {
	echo '<script>jQuery(document).ready(function() { jQuery("#ult-login").addClass("uo-modern-ui"); });</script>';
	// Enqueue color overrides for Modern UI
	FrontendLoginPlus::enqueue_modern_ui_colors();
}
?>