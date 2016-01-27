<?php

namespace uncanny_learndash_public;

if( ! defined( 'WPINC' ) ) {
	die;
}


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

			/* Redirect Login Page */
			// Create Shortcode that can be added anywhere
			add_shortcode( 'uo_login', array( __CLASS__, 'uo_login_form') );
			// Redirect from wp-login.php to custom login page
			add_action( 'init' , array(  __CLASS__, 'redirect_login_page' ) );
			// Redirect to custom login page if login has failed
			add_action( 'wp_login_failed', array(  __CLASS__, 'login_failed' ) );
			// Redirect to custom login page if useranme or password is empty
			add_filter( 'authenticate', array(  __CLASS__, 'verify_username_password'), 1, 3  );
			// Redirect from wp-login.php to custom login page if user logged out
			add_action('wp_logout', array(  __CLASS__, 'logout_page' ) );
			// Set page template as Login Page(login_page.php) if page slug is login
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

		$class_title = __( 'FrontEnd Login', Config::get_text_domain() );
		$class_description = __( 'Addon for Gravity Forms', Config::get_text_domain() );
		$class_icon = '<span class="uo_icon_dashicon dashicons dashicons-unlock"></span>';
		return array( 'title' => $class_title, 'description' => $class_description, 'dependants_exist' => self::dependants_exist(), 'icon' => $class_icon );

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
		$user_meta_key = apply_filters( 'uo_user_meta_key_column', self::$user_meta_key_col );
		$user_verified_value = get_user_meta( $user_id, $user_meta_key, true );

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

		<h3>User Verification</h3>

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

		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		update_user_meta( $user_id, 'uo_is_verified', $_POST['uo_is_verified'] );
	}

	public static function uo_login_form( $atts ){
		$redirect = ( isset( $atts['redirect'] ) ? $atts['redirect'] : home_url() );
		$submit_label = ( isset(  $atts['submit_label'] ) ? $atts['submit_label'] : 'Log In' );


		$login_form_args = array(
				'echo'           => true,
				'redirect'       => $redirect,
				'form_id'        => 'loginform',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in'   => __( $submit_label ),
				'id_username'    => 'user_login',
				'id_password'    => 'user_pass',
				'id_remember'    => 'rememberme',
				'id_submit'      => 'wp-submit',
				'remember'       => true,
				'value_username' => NULL,
				'value_remember' => true
		);

		wp_login_form($login_form_args);

	}

	/**
	 * Redirect from wp-login.php to custom login page
	 */
	public static function redirect_login_page() {
		$login_page  = home_url( '/login/' );
		$page_viewed = basename($_SERVER['REQUEST_URI']);

		if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
			wp_redirect($login_page);
			exit;
		}
	}

	/**
	 * Redirect to custom login page if login has failed
	 */
	public static function login_failed() {
		$login_page  = home_url( '/login/' );
		wp_redirect( $login_page . '?login=failed' );
		exit;
	}

	/**
	 * Redirect to custom login page if useranme or password is empty
	 */
	public static function verify_username_password( $user, $username, $password ) {
		$login_page  = home_url( '/login/' );
		if( $username == "" || $password == "" ) {
			wp_redirect( $login_page . "?login=empty" );
			exit;
		}
	}

	/**
	 * Redirect from wp-login.php to custom login page if user logged out
	 */
	public static function logout_page() {
		$login_page  = home_url( '/login/' );
		wp_redirect( $login_page . "?login=false" );
		exit;
	}

	/**
	 * Set page template as Login Page(login_page.php) if page slug is 'login'
	 */
	public static function set_page_template( $page_template )
	{
		//get lowercase version on page title
		$page_title = strtolower( get_the_title() );

		// if the slug or page title in login run the login page template
		if ( is_page( 'login' ) || $page_title == 'login') {
			$page_template = Config::get_template( '\login_page.php' );
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