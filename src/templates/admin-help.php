<?php

namespace uncanny_learndash_toolkit;

?>
	<?php
if ( isset( $_GET['sent'] ) ) {
	?>
	<h3>Your ticket has been created. Someone at Uncanny Owl will contact you regarding your issue.</h3>
	<?php
} else {
	$existing_license = trim( get_option( 'uo_license_key' ) );
	//$existing_license = 'becf794485526516987e2ac71aadbbae';
	$name  = '';
	$email = '';
	if ( $existing_license ) {
		$json = wp_remote_get( 'https://www.uncannyowl.com/wp-json/uncanny-rest-api/v1/license/' . $existing_license . '?wpnonce=' . wp_create_nonce( time() ) );
		if ( 200 === $json['response']['code'] ) {
			$data = json_decode( $json['body'], true );
			if ( $data ) {
				$name  = $data['name'];
				$email = $data['email'];
			}
		}
	}

	ob_start();

	include_once( 'admin-siteinfo.php' );

	$pinfo = ob_get_clean();

	?>
	<textarea style="display: none;" id="siteinfo-holder"><?php echo $pinfo; ?></textarea>

	<form id="contact" name="uncanny-help" method="post" action="<?php echo admin_url( 'admin.php' ) ?>">
		<?php wp_nonce_field( 'uncanny0w1', 'is_uncanny_help' ); ?>
		<input type="hidden" value="uncanny-toolkit-kb&submit-a-ticket=1" name="page"/>
		<input type="hidden" value="submit-a-ticket" name="tab"/>
		<h3>Submit a Ticket</h3>
		<fieldset>
			<input name="fullname" placeholder="Your name" type="text" tabindex="1" required value="<?php echo $name; ?>" autofocus>
		</fieldset>
		<fieldset>
			<input name="email" placeholder="Your Email Address" type="email" value="<?php echo $email; ?>" tabindex="2" required>
		</fieldset>
		<fieldset>
			<input name="website" placeholder="Your Web Site starts with http://" type="url" value="<?php echo get_bloginfo( 'url' ) ?> " tabindex="4" required>
		</fieldset>
		<fieldset>
			<textarea name="message" placeholder="Type your Message Here...." tabindex="5" required></textarea>
			<textarea id="siteinfo" style="display: none;" name="siteinfo"><?php echo $pinfo; ?></textarea>
		</fieldset>
		<fieldset>
			<input type="checkbox" value="yes" name="site-data" checked="checked"> Send Site Data
		</fieldset>
		<fieldset>
			<button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Submit</button>
		</fieldset>
	</form>

	<div id="sample-data">
		<h3>Sample Data</h3>
		<?php
		echo $pinfo;
		?>
	</div>
<?php } ?>