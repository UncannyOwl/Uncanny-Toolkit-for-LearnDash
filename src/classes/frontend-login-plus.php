<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}


use ReflectionClass;


class FrontendLoginPlus extends Config implements RequiredFunctions{

	// Title of our new column
	private static $column_title = 'Verified';
	// Meta key that will populate in our new column
	private static $user_meta_key_col = 'uo_is_verified';
	/**
	 * class constructor
	 *
	 */
	public function __construct() {

		if( true === self::dependants_exist()){

			/* CHECK IF PLUGIN NEEDS TO SET MANUAL VERIFICATION */
			$uo_manual_verification = 'no';
			$settings = get_option('FrontendLoginPlus');
			if( false !== $settings){

				foreach( $settings as $setting){
					if( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ){
						$uo_manual_verification = 'yes';
					}

				}

			}

			/* Add Manual Verification */
			if ( 'yes' === $uo_manual_verification ){

				// Add new column to wp admin user list that states if the user is or isn't verified
				add_filter( 'manage_users_columns', array( __CLASS__, 'add_meta_column' ) );
				// Populate the new column
				add_filter( 'manage_users_custom_column', array( __CLASS__, 'add_meta_column_content' ), 10, 3 );

				// Add Custom fields to user profile : user is view their own profile
				add_action( 'show_user_profile', array( __CLASS__, 'my_show_extra_profile_fields' ) );
				// Add Custom fields to user profile : user is view another user's profile
				add_action( 'edit_user_profile', array( __CLASS__, 'my_show_extra_profile_fields' ) );
				// Save custom fields from user profiles : user is view their own profile
				add_action( 'personal_options_update', array( __CLASS__, 'my_save_extra_profile_fields' ) );
				// Save custom fields from user profile : user is view another user's profile
				add_action( 'edit_user_profile_update', array( __CLASS__, 'my_save_extra_profile_fields' ) );

				// Prevent specific user from logging in
				add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );

			}

			/* Redirect Login Page */
			// Create Login Only Shortcode that can be added anywhere
			add_shortcode( 'uo_login', array( __CLASS__, 'uo_login_form') );
			// Create Login UI Shortcode that can be added anywhere
			add_shortcode( 'uo_login_ui', array( __CLASS__, 'uo_login_ui') );
			// Redirect from wp-login.php to custom login page
			add_action( 'init' , array(  __CLASS__, 'redirect_login_page' ) );
			// Redirect to custom login page if login has failed
			add_action( 'wp_login_failed', array(  __CLASS__, 'login_failed' ) );
			// Redirect to custom login page if username or password is empty
			add_filter( 'authenticate', array(  __CLASS__, 'verify_username_password'), 1, 3  );
			// Redirect from wp-login.php to custom login page if user logged out
			add_action('wp_logout', array(  __CLASS__, 'logout_page' ) );
			// !!!!REMOVED AND CHANGED TO SHORTCODE Set page template as Login Page(login_page.php) if page slug is login
			add_filter( 'page_template', array(  __CLASS__, 'set_page_template' ) );
			// Custom password retrieve message
			add_filter( 'retrieve_password_message', array(  __CLASS__, 'custom_retrieve_password_message' ), 10, 4 );
			// Add lost password link to login form
			add_action( 'login_form_bottom', array(  __CLASS__, 'add_lost_password_link' ) );
			/*add_filter( 'wp_mail_from_name', function( $name ) {
				return 'Insert Name';
			});*/

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return Array
	 */
	public static function get_details() {

		$class_title = __( 'FrontEnd Login', self::get_text_domain() );
		$class_description = __( 'If user turns off manuel user verification, all unverified user will be able to login', self::get_text_domain() );
		$class_icon = '<span class="uo_icon_dashicon dashicons dashicons-unlock"></span>';
		return array(
						'title' => $class_title,
						'description' => $class_description,
						'dependants_exist' => self::dependants_exist(),
						'settings' => self::get_class_settings( $class_title ),
						'icon' => $class_icon );

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist(){
		return true;
	}

	/**
	 * HTML for modal to create settings
	 * @param String
	 * @return boolean || string Return either false or settings html modal
	 *
	 */
	public static function get_class_settings( $class_title ){

		// Get pages to populate drop down
		$args = array(
				'sort_order' => 'asc',
				'sort_column' => 'post_title',
				'post_type' => 'page',
				'post_status' => 'publish'
		);

		$pages = get_pages($args);
		$drop_down = array( ['value' => 0, 'text' => '- Select Page -'] );

		foreach( $pages as $page ){
			array_push( $drop_down, array( 'value' => $page->ID, 'text' => $page->post_title ) );
		}

		// Create options
		$options = array(

			array(
				'type' => 'checkbox',
				'label' => 'Manual User Verification',
				'option_name' => 'uo_frontendloginplus_needs_verifcation'
			),

			array(
				'type' => 'select',
				'label' => 'Login Page',
				'select_name' => 'login_page',
				'options' => $drop_down
			),

		);


		// Build html
		$html = self::settings_output(array(
				'class' => __CLASS__,
				'title' => $class_title,
				'options' => $options
		));
		return $html;
	}

	/*
	 * Add a column to wp admin user table
	 *
	 * @return Array
	 *
	 */
	public static function add_meta_column($columns) {
		$columns['uo_column'] = apply_filters( 'uo_user_column_title', self::$column_title );
		return $columns;
	}

	/*
	 * Populate Column added to wo admin user table
	 *
	 * @return Array All columns
	 *
	 */
	public static function add_meta_column_content( $value, $column_name, $user_id) {

		$user_verified_value = get_user_meta( $user_id, self::$user_meta_key_col, true );

		if ( 'uo_column' == $column_name ){

			$message = 'Not Verified';

			if( '1' === $user_verified_value ){
				$message = '<span style="color:green;">Verified</span>';
			}

			return $message;
		}

		return $value;
	}

	/*
	 * Add custom field to user profile
	 */
	public static function my_show_extra_profile_fields( $user ) {
		$checked = esc_attr( get_user_meta( $user->ID, 'uo_is_verified', true ) );
		$current = true;
		$echo = true;
		?>

		<table class="form-table">
			<tr class="user-rich-editing-wrap">
				<th scope="row">
					<h2>Verify User</h2>
				</th>
				<td>
					<label for="rich_editing">
						<input type="checkbox" name="uo_is_verified" value="1" <?php checked( $checked, $current, $echo ); ?>/>
						Once user is verified they will be allowed to login
					</label>
				</td>
			</tr>

		</table>

		<?php
	}

	public static function my_save_extra_profile_fields( $user_id ) {

		if ( !current_user_can( 'edit_user' ) )
			return false;

		update_user_meta( $user_id, 'uo_is_verified', $_POST['uo_is_verified'] );

	}

	/*
	 * After login check to see if user account is disabled
	 *
	 * @since 1.0.0
	 * @param string $user_login
	 * @param object $user
	 */
	public function user_login( $user_login, $user = null ) {

		if ( !$user ) {
			$user = get_user_by('login', $user_login);
		}
		if ( !$user ) {
			// not logged in - definitely not disabled
			return;
		}

		$user_verified_value = get_user_meta( $user->ID, self::$user_meta_key_col, true );

		// Is the use logging in disabled?
		if ( '1' !== $user_verified_value ) {

			wp_logout();

			$redirect = home_url( );
			$redirect = apply_filters( 'uo_unverified_users_redirect', $redirect );
			wp_redirect( $redirect );

		}
	}

	/**
	 * Show a notice to users who try to login and are disabled
	 *
	 * @since 1.0.0
	 * @param string $message
	 * @return string
	 */

	public function user_login_message( $message ) {
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['unverified'] ) && $_GET['unverified'] == 1 )
			$message =  '<div id="login_error">' . apply_filters( 'uo_unverified_users_notice', __( "We haven't verified this account", self::get_text_domain() ) ) . '</div>';
		return $message;
	}

	public static function uo_login_form( $atts, $content = null ){

		if( is_user_logged_in() ){
			do_shortcode( $content );
		}

		$username_label = ( isset( $atts['username-label'] ) ? $atts['username-label'] : __( 'Username', self::get_text_domain() ) );
		$password_label = ( isset( $atts['password-label'] ) ? $atts['password-label'] : __( 'Password', self::get_text_domain() ) );
		$rememberme_label = ( isset( $atts['rememberme-label'] ) ? $atts['rememberme-label'] : __(  'Remember Me', self::get_text_domain() ) );

		$register_link = ( isset( $atts['register_link'] ) ? $atts['register_link'] : 'no' );

		if( 'yes' !== $register_link ){
			?>
			<script type='text/javascript'>
				jQuery( document ).ready(function() {
					jQuery('#user_login').attr( 'placeholder', '<?php echo $username_label; ?>' );
					jQuery('#user_pass').attr( 'placeholder', '<?php echo $password_label; ?>' );
				});
			</script>
			<?php
		}

		$placeholder = ( isset( $atts['placeholder'] ) ? $atts['placeholder'] : 'yes' );
		$redirect = ( isset( $atts['redirect'] ) ? $atts['redirect'] : home_url() );
		$submit_label = ( isset(  $atts['submit-label'] ) ? $atts['submit-label'] : __( 'Log In', self::get_text_domain() ) );

		if( 'no' !== $placeholder ){
			?>
			<script type='text/javascript'>
				jQuery( document ).ready(function() {
					jQuery('#user_login').attr( 'placeholder', '<?php echo $username_label; ?>' );
					jQuery('#user_pass').attr( 'placeholder', '<?php echo $password_label; ?>' );
				});
			</script>
			<?php
		}


		$login_form_args = array(
				'echo'           => false,
				'redirect'       => $redirect,
				'form_id'        => 'loginform',
				'label_username' => $username_label,
				'label_password' => $password_label,
				'label_remember' => $rememberme_label,
				'label_log_in'   => $submit_label,
				'id_username'    => 'user_login',
				'id_password'    => 'user_pass',
				'id_remember'    => 'rememberme',
				'id_submit'      => 'wp-submit',
				'remember'       => true,
				'value_username' => NULL,
				'value_remember' => true
		);

		return wp_login_form($login_form_args);

	}

	public static function uo_login_ui( $atts, $content = null )
	{

		// if the slug or page title in login run the login page template
		$page_template = self::get_template( '/login_page_ui.php' );

		//Render Template
		ob_start();
		include $page_template;
		$login_ui = ob_get_clean();
		return $login_ui;

	}

	/**
	 * Redirect from wp-login.php to custom login page
	 */
	public static function redirect_login_page() {

		$redirect_page_id = self::get_login_redirect_page_id();

		// If the login page is not set by user use default wp-login.php
		if( 0 !== $redirect_page_id) {

			$login_page = get_permalink(self::get_login_redirect_page_id());

			$page_viewed = basename($_SERVER['REQUEST_URI']);

			if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
				wp_redirect($login_page);
				exit;
			}

		}

	}

	/**
	 * Redirect to custom login page if login has failed
	 */
	public static function login_failed() {

		$redirect_page_id = self::get_login_redirect_page_id();

		// If the login page is not set by user use default wp-login.php
		if( 0 !== $redirect_page_id) {
			$login_page = get_permalink($redirect_page_id);
			wp_redirect($login_page . '?login=failed');
			exit;
		}

	}

	/**
	 * Redirect to custom login page if username or password is empty
	 */
	public static function verify_username_password( $user, $username, $password ) {

		$redirect_page_id = self::get_login_redirect_page_id();

		// If the login page is not set by user use default wp-login.php
		if( 0 !== $redirect_page_id) {

			$login_page = get_permalink($redirect_page_id);

			if ($username == "" || $password == "") {
				wp_redirect($login_page . "?login=empty");
				exit;
			}

		}

	}

	/**
	 * Redirect from wp-login.php to custom login page if user logged out
	 */
	public static function logout_page() {

		$redirect_page_id = self::get_login_redirect_page_id();

		// If the login page is not set by user use default wp-login.php
		if( 0 !== $redirect_page_id) {
			$login_page = get_permalink(self::get_login_redirect_page_id());
			wp_redirect($login_page . "?login=false");
			exit;
		}

	}

	/**
	 * Set wp-login redirect to frontend page
	 */
	private static function get_login_redirect_page_id(){

		$page_id = 0;

		$settings = get_option('FrontendLoginPlus');
		if( false !== $settings){

			foreach( $settings as $setting){
				if( 'login_page' === $setting['name'] ){
					$page_id = $setting['value'];
				}

			}

		}

		return (int)$page_id;
	}

	/**
	 * Set page template as Login Page(login_page.php) if page slug is 'login'
	 * REMOVE THIS FUNCTION IS REPLACED BY A SHORTCODE
	 */
	public static function set_page_template( $page_template )
	{
		//get lowercase version on page title
		$page_title = strtolower( get_the_title() );

		// if the slug or page title in login run the login page template
		if ( is_page( 'login' ) || $page_title == 'login') {
			$page_template = self::get_template( '/login_page.php' );
		}
		return $page_template;
	}

	/*
	 * Add lost password link the login form
	 */
	public static function add_lost_password_link() {
		return '<a class="forgot-link" href="/login/?action=lostpassword">Forgot Your Password?</a>';
	}

	/*
	 * Custom email message to retrieve password
	 */
	public static function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {
		$new_message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
		$new_message .= network_home_url( '/' ) . "\r\n\r\n";
		$new_message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$new_message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$new_message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$new_message .= '<' . network_site_url("login/?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
		return $new_message;
	}

}