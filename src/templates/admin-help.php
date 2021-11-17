<?php

namespace uncanny_learndash_toolkit;

// Get data
$status = get_option( 'uo_license_status' ); // $license_data->license will be either "valid", "invalid", "expired", "disabled"

// Check license status
$license_is_active = $status == 'valid' ? true : false;

if ( ! $license_is_active ) {

	if ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
		$compare_version = version_compare( UNCANNY_TOOLKIT_PRO_VERSION, '3.0' );

		if ( 0 > $compare_version ) {
			$license_page_link = admin_url( 'admin.php?page=uncanny-pro-license-activation' );
		} else {
			$license_page_link = admin_url( 'admin.php?page=uncanny-toolkit-license' );
		}
		?>
		<h3 style="color:#b4261b">
			<?php
			echo sprintf(
				__( 'Please <a href="%s">activate a valid license key</a> to submit a support ticket.', 'uncanny-learndash-toolkit' ),
				$license_page_link
			);
			?>
		</h3>
		<?php
	}
} elseif ( isset( $_GET['sent'] ) ) {
	?>

	<h3 style="color:#46b450">
		<?php _e( 'Your ticket has been created. Someone at Uncanny Owl will contact you regarding your issue.', 'uncanny-learndash-toolkit' ); ?>
	</h3>

	<?php

} else {
	$existing_license = trim( get_option( 'uo_license_key' ) );

	$name  = '';
	$email = '';

	if ( $existing_license ) {
		$json = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/license/' . $existing_license . '?wpnonce=' . wp_create_nonce( time() ) );
		if ( ! is_wp_error( $json ) ) {
			if ( 200 === $json['response']['code'] ) {
				$data = json_decode( $json['body'], true );

				if ( $data ) {
					$name  = $data['name'];
					$email = $data['email'];
				}
			}
		}
	}

	ob_start();

	include_once 'admin-siteinfo.php';

	$installation_information = ob_get_clean();

	?>

	<div class="uo-core">
		<div class="uo-send-ticket">
			<div class="uo-send-ticket__form">
				<div class="uo-send-ticket__title">
					<?php _e( 'Submit a Ticket', 'uncanny-learndash-toolkit' ); ?>
				</div>

				<form name="uncanny-help" method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
					<?php wp_nonce_field( 'uncanny0w1', 'is_uncanny_help' ); ?>

					<textarea class="uo-send-ticket__hidden-field"
							  name="siteinfo"><?php echo $installation_information; ?></textarea>

					<input type="hidden" value="uncanny-toolkit-kb&submit-a-ticket=1" name="page"/>

					<input type="hidden" value="submit-a-ticket" name="tab"/>

					<div class="uo-send-ticket-form__row">
						<label for="uo-fullname" class="uo-send-ticket-form__label">
							<?php _e( 'Full Name', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<input required name="fullname" id="uo-fullname" type="text" class="uo-send-ticket-form__text"
							   value="<?php echo $name; ?>">
					</div>

					<div class="uo-send-ticket-form__row">
						<label for="uo-email" class="uo-send-ticket-form__label">
							<?php _e( 'Email', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<input required name="email" id="uo-email" type="email" class="uo-send-ticket-form__text"
							   value="<?php echo $email; ?>">
					</div>

					<div class="uo-send-ticket-form__row">
						<label for="uo-website" class="uo-send-ticket-form__label">
							<?php _e( 'Site URL', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<input required name="website" id="uo-website" type="url" class="uo-send-ticket-form__text"
							   readonly value="<?php echo get_bloginfo( 'url' ); ?>">
					</div>

					<div class="uo-send-ticket-form__row">
						<label for="uo-website" class="uo-send-ticket-form__label">
							<?php _e( 'License Key', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<input required name="license_key" id="uo-website" type="text" class="uo-send-ticket-form__text"
							   readonly value="<?php echo trim( get_option( 'uo_license_key' ) ); ?>">
					</div>

					<div class="uo-send-ticket-form__row">
						<label for="uo-subject" class="uo-send-ticket-form__label">
							<?php _e( 'Subject', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<input required name="subject" id="uo-subject" type="text" class="uo-send-ticket-form__text"
							   value="">
					</div>

					<div class="uo-send-ticket-form__row">
						<label for="uo-message" class="uo-send-ticket-form__label">
							<?php _e( 'Message', 'uncanny-learndash-toolkit' ); ?>
						</label>
						<textarea required name="message" id="uo-message"
								  class="uo-send-ticket-form__textarea"></textarea>
					</div>

					<div class="uo-send-ticket-form__row">
						<input type="checkbox" value="yes" name="site-data"
							   checked="checked"> <?php _e( 'Send site data', 'uncanny-learndash-toolkit' ); ?>
					</div>

					<div class="uo-send-ticket-form__row">
						<p>
							<?php echo __( 'Emails must be enabled on your site to create a ticket using this form. If you don’t receive a confirmation email shortly after submitting this form, please log the ticket through your <a href="https://www.uncannyowl.com/my-account/submit-a-request/" target="_blank" rel="noreferrer">My Account</a> page.', 'uncanny-learndash-toolkit' ); ?>
						</p>
						<button type="submit" class="uo-send-ticket-form__submit">
							<?php _e( 'Create ticket', 'uncanny-learndash-toolkit' ); ?>
						</button>
					</div>
				</form>
			</div>
			<div class="uo-send-ticket__data">
				<div class="uo-send-ticket__title">
					<?php _e( 'Site Data', 'uncanny-learndash-toolkit' ); ?>
				</div>

				<?php echo $installation_information; ?>
			</div>
		</div>
	</div>

<?php } ?>
