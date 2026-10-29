<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="error">
	<p><?php esc_html_e("Emails are not disabled! Something else has already declared wp_mail(), so Disable Emails cannot stop emails being sent!", 'uncanny-learndash-toolkit'); ?></p>
</div>
