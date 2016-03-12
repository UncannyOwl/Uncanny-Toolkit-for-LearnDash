<?php

namespace uncanny_learndash_public;

if ( ! defined( 'WPINC' ) ) {
	die;
}


class FrontendLoginPlus extends Config implements RequiredFunctions {

	// Title of our new column
	private static $column_title = 'Verified';
	// Meta key that will populate in our new column
	private static $user_meta_key_col = 'uo_is_verified';


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks(){

		if ( true === self::dependants_exist() ) {

			/* CHECK IF PLUGIN NEEDS TO SET MANUAL VERIFICATION */
			$uo_manual_verification = 'no';
			$is_login_page_set = 'no';
			$settings = get_option( 'FrontendLoginPlus' );

			if ( false !== $settings ) {

				foreach ( $settings as $setting ) {

					if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
						$uo_manual_verification = 'yes';
					}

					if ( 'login_page' === $setting['name'] && '0' !== $setting['value']) {
						$is_login_page_set = 'yes';
					}

				}
			}

			if( 'yes' === $is_login_page_set ){

				/* Add Manual Verification */
				if ( 'yes' === $uo_manual_verification ) {

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
					add_action( 'wp_login', array( __CLASS__, 'user_login' ), 10, 2 );

				}

				// Create Login UI Shortcode that can be added anywhere
				add_shortcode( 'uo_login_ui', array( __CLASS__, 'uo_login_ui' ) );
				// Redirect from wp-login.php to custom login page
				add_action( 'init', array( __CLASS__, 'redirect_login_page' ) );
				// Redirect after lost password
				add_filter( 'lostpassword_redirect', array( __CLASS__, 'redirect_lost_password' ) );
				// Redirect to custom login page if login has failed
				add_action( 'wp_login_failed', array( __CLASS__, 'login_failed' ) );
				// Redirect to custom login page if username or password is empty
				add_filter( 'authenticate', array( __CLASS__, 'verify_username_password' ), 1, 3 );
				// Redirect from wp-login.php to custom login page if user logged out
				add_action( 'wp_logout', array( __CLASS__, 'logout_page' ) );
				// Custom password retrieve message
				add_filter( 'retrieve_password_message', array( __CLASS__, 'custom_retrieve_password_message' ), 10, 4 );
				// Add lost password link to login form
				add_action( 'login_form_bottom', array( __CLASS__, 'add_lost_password_link' ) );

			}

			/* Redirect Login Page */
			// Create Login Only Shortcode that can be added anywhere
			add_shortcode( 'uo_login', array( __CLASS__, 'uo_login_form' ) );

		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {

		$class_title       = __( 'Front End Login', self::get_text_domain() );
		$kb_link = null;
		$class_description = __( 'Adds a custom login form and can optionally force users to be verified by an admin before they can sign in.', self::get_text_domain() );
		$class_icon = '<i class="uo_icon_fa fa fa-sign-in"></i>';

		return array(
				'title'            => $class_title,
				'kb_link'          => $kb_link, // OR set as null not to display
				'description'      => $class_description,
				'dependants_exist' => self::dependants_exist(),
				'settings'         => self::get_class_settings( $class_title ),
				'icon'             => $class_icon,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {

		return true;
	}

	/**
	 * HTML for modal to create settings
	 * @param String
	 * @return boolean || string Return either false or settings html modal
	 *
	 */
	public static function get_class_settings( $class_title ) {

		// Get pages to populate drop down
		$args = array(
				'sort_order'  => 'asc',
				'sort_column' => 'post_title',
				'post_type'   => 'page',
				'post_status' => 'publish',
		);

		$pages     = get_pages( $args );
		$drop_down = array( [ 'value' => 0, 'text' => '- Select Page -' ] );

		foreach ( $pages as $page ) {
			array_push( $drop_down, array( 'value' => $page->ID, 'text' => $page->post_title ) );
		}

		// Create options
		$options = array(

				array(
						'type'        => 'checkbox',
						'label'       => 'Manual User Verification',
						'option_name' => 'uo_frontendloginplus_needs_verifcation',
				),

				array(
						'type'        => 'select',
						'label'       => 'Login Page',
						'select_name' => 'login_page',
						'options'     => $drop_down,
				),

		);


		// Build html
		$html = self::settings_output( array(
						'class'   => __CLASS__,
						'title'   => $class_title,
						'options' => $options,
				)
		);

		return $html;
	}

	/*
	 * Add a column to wp admin user table
	 *
	 * @return Array
	 *
	 */
	public static function add_meta_column( $columns ) {
		$columns['uo_column'] = apply_filters( 'uo_user_column_title', self::$column_title );

		return $columns;
	}

	/*
	 * Populate Column added to wo admin user table
	 *
	 * @return Array All columns
	 *
	 */
	public static function add_meta_column_content( $value, $column_name, $user_id ) {

		$user_verified_value = get_user_meta( $user_id, self::$user_meta_key_col, true );

		if ( 'uo_column' == $column_name ) {

			$message = 'Not Verified';

			if ( '1' === $user_verified_value ) {
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
		$echo    = true;
		?>

		<table class="form-table">
			<tr class="user-rich-editing-wrap">
				<th scope="row">
					<h2>Verify User</h2>
				</th>
				<td>
					<label for="rich_editing">
						<input type="checkbox" name="uo_is_verified" value="1" <?php checked( $checked, $current, $echo ); ?>/>
						Verify this user and allow them to log in
					</label>
				</td>
			</tr>

		</table>

		<?php
	}

	public static function my_save_extra_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user' ) ) {
			return false;
		}

		update_user_meta( $user_id, 'uo_is_verified', $_POST['uo_is_verified'] );

		$verified = get_user_meta( $user_id, 'uo_is_verified', true );
		$verified_email_sent = get_user_meta( $user_id, 'uo_verified_email_sent', true );

		if( $verified === '1' && 'yes' !== $verified_email_sent ){

			$user = get_userdata( $user_id );

			$blog_name = get_option('blogname');
			$admin_email = get_option('admin_email');

			$from = $blog_name . ' <' . $admin_email . '>';
			$headers[] = 'From: ' . $from;
			$headers = apply_filters( 'uo_verified_email_headers',  $headers, $user );

			$to = $user->user_email;
			$subject = $blog_name . ' - Account Verified';
			$subject = apply_filters( 'uo_verified_email_subject',  $subject, $user);
			$message = "The account for the user name '".$user->user_login."' has been verified \r\n\n";
			$message.= "Please visit ".home_url()." to login \r\n";
			$message = apply_filters( 'uo_verified_email_subject',  $message, $user );
			$mailed = wp_mail( $to, $subject, $message, $headers );

			// after wp_mail successful
			if( $mailed ){
				update_user_meta( $user_id, 'uo_verified_email_sent', 'yes' );
			}

		}

	}

	/*
	 * After login check to see if user account is disabled
	 *
	 * @since 1.0.0
	 * @param string $user_login
	 * @param object $user
	 */
	public function user_login( $user_login, $user = null ) {

		if ( ! $user ) {
			$user = get_user_by( 'login', $user_login );
		}
		if ( ! $user ) {
			// not logged in - definitely not disabled
			return;
		}

		$user_verified_value = get_user_meta( $user->ID, self::$user_meta_key_col, true );

		// Is the use logging in disabled?
		if ( '1' !== $user_verified_value ) {

			wp_logout();

			$redirect = home_url();
			wp_safe_redirect( $redirect );

		}
	}

	/**
	 * Show a notice to users who try to login and are disabled
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return string
	 */

	public function user_login_message( $message ) {
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['unverified'] ) && 1 === $_GET['unverified'] ) {
			$message = '<div id="login_error">' . apply_filters( 'uo_unverified_users_notice', __( "We haven't verified this account", self::get_text_domain() ) ) . '</div>';
		}

		return $message;
	}

	public static function uo_login_form( $atts, $content = null ) {

		if ( is_user_logged_in() ) {
			return do_shortcode( $content );
		}else {


			$username_label = (isset($atts['username-label']) ? $atts['username-label'] : __('Username', self::get_text_domain()));
			$password_label = (isset($atts['password-label']) ? $atts['password-label'] : __('Password', self::get_text_domain()));
			$rememberme_label = (isset($atts['rememberme-label']) ? $atts['rememberme-label'] : __('Remember Me', self::get_text_domain()));

			$register_link = (isset($atts['register_link']) ? $atts['register_link'] : 'no');

			if ('yes' !== $register_link) {

			}

			$placeholder = (isset($atts['placeholder']) ? $atts['placeholder'] : 'yes');
			$redirect = (isset($atts['redirect']) ? $atts['redirect'] : home_url());
			$submit_label = (isset($atts['#loginform']) ? $atts['submit-label'] : __('Log In', self::get_text_domain()));

			if ('no' !== $placeholder) {
				?>
				<script type='text/javascript'>
					jQuery(document).ready(function () {
						jQuery('#user_login').attr('placeholder', '<?php echo $username_label; ?>');
						jQuery('#user_pass').attr('placeholder', '<?php echo $password_label; ?>');
					});
				</script>
				<?php
			}


			$login_form_args = array(
					'echo' => false,
					'redirect' => $redirect,
					'form_id' => 'loginform',
					'label_username' => $username_label,
					'label_password' => $password_label,
					'label_remember' => $rememberme_label,
					'label_log_in' => $submit_label,
					'id_username' => 'user_login',
					'id_password' => 'user_pass',
					'id_remember' => 'rememberme',
					'id_submit' => 'wp-submit',
					'remember' => true,
					'value_username' => null,
					'value_remember' => true,
			);

			return wp_login_form($login_form_args);

		}

	}

	public static function uo_login_ui( $atts, $content = null ) {

		// if the slug or page title in login run the login page template
		$page_template = self::get_template( '/login-page-ui.php' );

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

		$login_page  = get_permalink( self::get_login_redirect_page_id() );
		$page_viewed = basename( $_SERVER['REQUEST_URI'] );

		if ( $page_viewed == "wp-login.php" && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			wp_safe_redirect( $login_page );
			exit;
		}
	}

	/**
	 * Redirect to custom login page if login has failed
	 */
	public static function login_failed() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'login' => 'failed' ),$login_page ) );
		exit;
	}

	/**
	 * Redirect to custom login page if useranme or password is empty
	 */
	public static function verify_username_password( $user, $username, $password ) {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		if ( '' === $username || '' === $password ) {
			wp_safe_redirect( add_query_arg( array( 'login' => 'empty' ),$login_page ) );
			exit;
		}
	}

	/**
	 * Redirect from wp-login.php to custom login page if user logged out
	 */
	public static function logout_page() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'login' => 'false' ),$login_page ) );
		exit;
	}

	public static function redirect_lost_password( $lostpassword_redirect ) {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'action' => 'forgot', 'success' => 'something' ),$login_page ) );
	}


	/**
	 * Set wp-login redirect to frontend page
	 */
	private static function get_login_redirect_page_id() {

		$page_id = 0;

		$settings = get_option( 'FrontendLoginPlus' );
		if ( false !== $settings ) {

			foreach ( $settings as $setting ) {
				if ( 'login_page' === $setting['name'] ) {
					$page_id = $setting['value'];
				}
			}
		}

		return (int) $page_id;
	}

	/**
	 * Set page template as Login Page(login-page.php) if page slug is 'login'
	 */
	public static function set_page_template( $page_template ) {
		//get lowercase version on page title
		$page_title = strtolower( get_the_title() );

		// if the slug or page title in login run the login page template
		if ( is_page( 'login' ) || 'login' === $page_title ) {
			$page_template = self::get_template( 'login-page.php' );
		}

		return $page_template;
	}

	/*
	 * Add lost password link the login form
	 */
	public static function add_lost_password_link() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		$link = add_query_arg( array( 'action' => 'lostpassword' ),$login_page );
		return '<a class="forgot-link" href="'. $link .'">' . __( 'Forgot Your Password?', Config::get_text_domain() ) . '</a>';
	}

	/*
	 * Custom email message to retrieve password
	 */
	public static function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$reset_args = array(
				'action' 	=> 'rp',
				'key'		=> $key,
				'login'		=> rawurlencode( $user_login )
		);

		$reset_link = add_query_arg( $reset_args ,$login_page );

		$new_message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$new_message .= network_home_url( '/' ) . "\r\n\r\n";
		$new_message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$new_message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$new_message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$new_message .= '<' . $reset_link . ">\r\n";

		return $new_message;
	}
}
