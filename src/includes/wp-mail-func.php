<?php

function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
	// create mock PHPMailer object to handle any filter and action hook listeners
	$mailer = new \uncanny_learndash_toolkit\DisableEmailsPHPMailerMock();

	return $mailer->wpmail( $to, $subject, $message, $headers, $attachments );
}