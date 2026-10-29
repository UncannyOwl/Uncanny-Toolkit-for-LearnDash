<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside Boot::uo_support_page(); variables are local to that method.

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
			echo wp_kses_post(
				sprintf(
					/* translators: %s: URL of the license activation page. */
					__( 'Please <a href="%s">activate a valid license key</a> to submit a support ticket.', 'uncanny-learndash-toolkit' ),
					esc_url( $license_page_link )
				)
			);
			?>
		</h3>
		<?php
	}
} elseif ( isset( $_GET['sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
	?>

	<h3 style="color:#46b450">
		<?php esc_html_e( 'Your ticket has been created. Someone at Uncanny Owl will contact you regarding your issue.', 'uncanny-learndash-toolkit' ); ?>
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

	<div class="uo-core section">
		<div class="uncannyowl-flex uncannyowl-flex-wrap">
			<div class="uncannyowl-flex-0 uncannyowl-w-33">
				 <h2>
					<?php esc_html_e( 'Submit a Ticket', 'uncanny-learndash-toolkit' ); ?>
				</h2>
				<div class="uncannyowl-card">
					<div class="uncannyowl-card-body">
						<form name="uncanny-help" method="POST" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
							<?php wp_nonce_field( 'uncanny0w1', 'is_uncanny_help' ); ?>

							<textarea class="uo-send-ticket__hidden-field"
									name="siteinfo"><?php echo esc_textarea( $installation_information ); ?></textarea>

							<input type="hidden" value="uncanny-toolkit-kb&submit-a-ticket=1" name="page"/>

							<input type="hidden" value="submit-a-ticket" name="tab"/>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-fullname" >
									<?php esc_html_e( 'Full Name', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<input required name="fullname" id="uo-fullname" type="text" class="uncannyowl-input"
									value="<?php echo esc_attr( $name ); ?>">
							</div>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-email"  >
									<?php esc_html_e( 'Email', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<input required name="email" id="uo-email" type="email" class="uncannyowl-input"
									value="<?php echo esc_attr( $email ); ?>">
							</div>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-website" >
									<?php esc_html_e( 'Site URL', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<input required name="website" id="uo-website" type="url" class="uncannyowl-input"
									readonly value="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>">
							</div>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-website" >
									<?php esc_html_e( 'License Key', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<input required name="license_key" id="uo-website" type="text" class="uncannyowl-input"
									readonly value="<?php echo esc_attr( trim( get_option( 'uo_license_key' ) ) ); ?>">
							</div>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-subject">
									<?php esc_html_e( 'Subject', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<input required name="subject" id="uo-subject" type="text" class="uncannyowl-input"
									value="">
							</div>

							<div class="uncannyowl-form-field p-0">
								<label for="uo-message" >
									<?php esc_html_e( 'Message', 'uncanny-learndash-toolkit' ); ?>
								</label>
								<textarea required name="message" id="uo-message"
										class="uncannyowl-textarea"></textarea>
							</div>

							<div class="uncannyowl-form-field p-0">
								<div class="uncannyowl-checkbox mb-0">
									<label class="uncannyowl-checkbox-label mb-0">
										<input type="checkbox" value="yes" name="site-data" checked="checked">
										<?php esc_html_e( 'Send site data', 'uncanny-learndash-toolkit' ); ?>
									</label>
								</div> 	 
							</div>

							<div class="uncannyowl-form-field p-0">
								<p class="mt-0">
									<?php echo wp_kses_post( __( 'Emails must be enabled on your site to create a ticket using this form. If you don’t receive a confirmation email shortly after submitting this form, please log the ticket through your <a href="https://www.uncannyowl.com/my-account/submit-a-request/" target="_blank" rel="noreferrer">My Account</a> page.', 'uncanny-learndash-toolkit' ) ); ?>
								</p>
								<button type="submit" class="uncannyowl-btn uncannyowl-btn--primary">
									<?php esc_html_e( 'Create ticket', 'uncanny-learndash-toolkit' ); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="uo-send-ticket__data uncannyowl-w-66 uncannyowl-flex-initial">
				<h2> <?php esc_html_e( 'Site Data', 'uncanny-learndash-toolkit' ); ?> </h2>

				<?php echo $installation_information; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup built by admin-siteinfo.php; every cell is escaped there with esc_html() / wp_kses_post(). ?>
			</div>
		</div>
	</div>

<?php } ?>
