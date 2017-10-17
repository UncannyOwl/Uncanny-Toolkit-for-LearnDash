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
	$name             = '';
	$email            = '';
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
	global $wpdb, $wp_version;
	ob_start();
	?>
	<table style=" max-width: 750px; width: 100%; text-align: left; font-family: arial; font-size:12px;" cellspacing="0">
		<thead>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-left"><?php _e( 'Setting', 'uncanny-learndash-toolkit' ) ?></th>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-right"><?php _e( 'Value', 'uncanny-learndash-toolkit' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<tr style="background-color: #f9f9f9;">
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'PHP Version', 'uncanny-learndash-toolkit' ) ?></strong></td>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;">
			<?php
			$php_version     = phpversion();
			$version_compare = version_compare( '7.0', $php_version, '>' );
			$color           = 'green';
			if ( - 1 == $version_compare ) {
				$color = 'red';
			}
			echo '<span style="color: ' . $color . '">' . $php_version . '</span>';
			if ( defined( 'PHP_OS' ) ) {
				echo __( ' PHP_OS: ', 'uncanny-learndash-toolkit' ) . PHP_OS;
			}
			if ( defined( 'PHP_OS_FAMILY' ) ) {
				echo __( ' PHP_OS_FAMILY: ', 'uncanny-learndash-toolkit' ) . PHP_OS_FAMILY;
			}
			//echo ', Family: '. PHP_OS_FAMILY;
			if ( $version_compare == - 1 ) {
				echo ' - <a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Minimum Requirements', 'uncanny-learndash-toolkit' ) . '</a>';
			}
			?></th></tr>
		<?php if ( $wpdb->is_mysql == true ) { ?>
			<tr>
				<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;">
					<strong><?php _e( 'MySQL version', 'uncanny-learndash-toolkit' ) ?></strong></td>
				<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php
					$mysql_version = $wpdb->db_version();

					$version_compare = version_compare( '5.6', $mysql_version, '>' );
					$color           = 'green';
					if ( $version_compare == - 1 ) {
						$color = 'red';
					}
					echo '<span style="color: ' . $color . '">' . $mysql_version . '</span>';
					if ( $version_compare == - 1 ) {
						echo ' - <a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Minimum Requirements', 'uncanny-learndash-toolkit' ) . '</a>';
					}

					?>
				<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;">
			</tr>
		<?php } ?>
		</tbody>
	</table>

	<h2><?php _e( 'WordPress Settings', 'uncanny-learndash-toolkit' ); ?></h2>
	<table style=" max-width: 750px; width: 100%; text-align: left; font-family: arial; font-size:12px;" cellspacing="0">
		<thead>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-left"><?php _e( 'Setting', 'uncanny-learndash-toolkit' ) ?></th>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-right"><?php _e( 'Value', 'uncanny-learndash-toolkit' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WordPress Version', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo $wp_version; ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'Is Multisite', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo is_multisite() ? __( 'Yes', 'uncanny-learndash-toolkit' ) : __( 'No', 'uncanny-learndash-toolkit' ) ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'Site Language', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo get_locale(); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'DISABLE_WP_CRON', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'DISABLE_WP_CRON' ) ) ? DISABLE_WP_CRON : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_DEBUG', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_DEBUG' ) ) ? WP_DEBUG : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_DEBUG_DISPLAY', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_DEBUG_DISPLAY' ) ) ? WP_DEBUG_DISPLAY : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'SCRIPT_DEBUG', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'SCRIPT_DEBUG' ) ) ? SCRIPT_DEBUG : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_DEBUG_DISPLAY', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_DEBUG_DISPLAY' ) ) ? WP_DEBUG_DISPLAY : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_DEBUG_LOG', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_DEBUG_LOG' ) ) ? WP_DEBUG_LOG : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_AUTO_UPDATE_CORE', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_AUTO_UPDATE_CORE' ) ) ? WP_AUTO_UPDATE_CORE : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_MAX_MEMORY_LIMIT', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) ? WP_MAX_MEMORY_LIMIT : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'WP_MEMORY_LIMIT', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'WP_MEMORY_LIMIT' ) ) ? WP_MEMORY_LIMIT : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'DB_CHARSET', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'DB_CHARSET' ) ) ? DB_CHARSET : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'DB_COLLATE', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'DB_COLLATE' ) ) ? DB_COLLATE : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'Object Cache', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo wp_using_ext_object_cache() ? __( 'Yes', 'uncanny-learndash-toolkit' ) : __( 'No', 'uncanny-learndash-toolkit' ) ?></td>
		</tr>

		</tbody>
	</table>
	</form>
	<h2><?php _e( 'WordPress Plugins', 'uncanny-learndash-toolkit' ); ?></h2>
	<table style=" max-width: 750px; width: 100%; text-align: left; font-family: arial; font-size:12px;" cellspacing="0">
		<thead>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-left"><?php _e( 'Plugin', 'uncanny-learndash-toolkit' ) ?></th>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-right"><?php _e( 'Version', 'uncanny-learndash-toolkit' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php $plugins = get_plugins() ?>
		<?php $i = 0; ?>
		<?php foreach ( $plugins as $plugin ) { ?>
			<tr <?php if ( $i % 2 === 0 ) { ?> style="background-color: #f9f9f9;"<?php } ?>>
				<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
					<strong><?php echo $plugin['Title'] ?></strong></th>
				<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo $plugin['Version'] ?></td>
			</tr>
			<?php $i ++;
		} ?>
		</tbody>
	</table>
	<h2><?php _e( 'Learndash Settings', 'uncanny-learndash-toolkit' ); ?></h2>
	<table style=" max-width: 750px; width: 100%; text-align: left; font-family: arial; font-size:12px;" cellspacing="0">
		<thead>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-left"><?php _e( 'Setting', 'uncanny-learndash-toolkit' ) ?></th>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-right"><?php _e( 'Value', 'uncanny-learndash-toolkit' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'Version', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo LEARNDASH_VERSION; ?></td>
		</tr>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'DB Version', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo LEARNDASH_SETTINGS_DB_VERSION; ?></td>
		</tr>
		<tr style="background-color: #f9f9f9;">
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
				<strong><?php _e( 'Script Debug', 'uncanny-learndash-toolkit' ) ?></strong></th>
			<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) ) ? LEARNDASH_SCRIPT_DEBUG : __( 'not defined', 'uncanny-learndash-toolkit' ); ?></td>
		</tr>
		</tbody>
	</table>

	<?php
	$php_ini_settings = array(
		'max_execution_time',
		'max_input_time',
		'max_input_vars',
		'post_max_size',
		'max_file_uploads',
		'upload_max_filesize'
	);
	sort( $php_ini_settings );
	?>
	<h2><?php _e( 'PHP Settings', 'uncanny-learndash-toolkit' ); ?></h2>
	<table style=" max-width: 750px; width: 100%; text-align: left; font-family: arial; font-size:12px;" cellspacing="0">
		<thead>
		<tr>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-left"><?php _e( 'Setting', 'uncanny-learndash-toolkit' ) ?></th>
			<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="col" class="uo-rest-support-settings-right"><?php _e( 'Value', 'uncanny-learndash-toolkit' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php $i = 0; ?>
		<?php foreach ( $php_ini_settings as $ini_key ) { ?>
			<tr <?php if ( $i % 2 === 0 ) { ?> style="background-color: #f9f9f9;"<?php } ?>>
				<th style="width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;" scope="row">
					<strong><?php echo $ini_key ?></strong></th>
				<td style="padding: 3px; margin: 0; text-align: left; width: 50%;text-align: left; border-bottom: 1px solid #000; margin: 0;"><?php echo ini_get( $ini_key ) ?></td>
			</tr>
			<?php $i ++;
		} ?>
		</tbody>
	</table>

	<?php

	$pinfo = ob_get_contents();
	ob_end_clean();
	?>
	<textarea style="display: none;" id="siteinfo-holder"><?php echo $pinfo; ?></textarea>

	<form id="contact" name="uncanny-help" method="post" action="<?php echo admin_url( 'admin.php' ) ?>">
		<?php wp_nonce_field( 'uncanny0w1', 'is_uncanny_help' ); ?>
		<input type="hidden" value="uncanny-learnDash-toolkit-support" name="page"/>
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
			                                                                       (<a href="#sample-data">View
			                                                                                               Sample</a>)
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