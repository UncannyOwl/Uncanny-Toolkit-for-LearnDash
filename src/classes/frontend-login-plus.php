<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( '' !== Config::get_settings_value( 'uo_frontend_registration', 'FrontendLoginPlus' ) ) {
	include_once( Config::get_include( 'custom-user-notification.php' ) );
}


/**
 * Class FrontendLoginPlus
 * @package uncanny_custom_toolkit
 */
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
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			/* CHECK IF PLUGIN NEEDS TO SET MANUAL VERIFICATION */
			$uo_manual_verification   = 'no';
			$is_login_page_set        = 'no';
			$uo_frontend_registration = 'no';
			$settings                 = get_option( 'FrontendLoginPlus', array() );

			foreach ( $settings as $setting ) {

				if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
					$uo_manual_verification = 'yes';
				}

				if ( 'login_page' === $setting['name'] && '0' !== $setting['value'] ) {
					$is_login_page_set = 'yes';
				}

				if ( 'uo_frontend_registration' === $setting['name'] && '0' !== $setting['value'] ) {
					$uo_frontend_registration = 'yes';
				}

			}


			if ( 'yes' === $is_login_page_set ) {

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

				}

				// Set Cookies for login-page-ui.php early
				add_action( 'wp', array( __CLASS__, 'set_cookies' ) );

				add_action( 'wp', array( __CLASS__, 'maybe_set_cookies' ), 99 ); // Set cookies
				add_action( 'shutdown', array(
					__CLASS__,
					'maybe_set_cookies'
				), 0 ); // Set cookies before shutdown and ob flushing


				// Create Login UI Shortcode that can be added anywhere
				add_shortcode( 'uo_login_ui', array( __CLASS__, 'uo_login_ui' ) );
				// Redirect from wp-login.php to custom login page
				add_action( 'init', array( __CLASS__, 'redirect_login_page' ) );
				// Redirect after lost password
				add_filter( 'lostpassword_redirect', array( __CLASS__, 'redirect_lost_password' ) );
				// Redirect to custom login page if login has failed
				add_action( 'wp_login_failed', array( __CLASS__, 'login_failed' ) );

				if ( 'yes' === $uo_frontend_registration ) {
					// Redirect to custom login page after registration
					add_filter( 'registration_redirect', array( __CLASS__, 'redirect_registration' ) );
					// Redirect User after registration errors
					add_action( 'register_post', array( __CLASS__, 'redirect_registration_errors' ), 10, 3 );
				}

				// Redirect to custom login page if username or password is empty
				add_filter( 'authenticate', array( __CLASS__, 'verify_username_password' ), 10, 3 );
				// Redirect from wp-login.php to custom login page if user logged out
				add_action( 'wp_logout', array( __CLASS__, 'logout_page' ), 20, 1 );

				// Custom password retrieve message
				add_filter( 'retrieve_password_message', array(
					__CLASS__,
					'custom_retrieve_password_message',
				), 10, 4 );
				// Add lost password link to login form
				add_action( 'login_form_bottom', array( __CLASS__, 'add_lost_password_link' ) );
				// Add shortcode to page with warning if it wasn't added
				add_action( 'loop_end', array( __CLASS__, 'maybe_add_ui_shortcode' ) );
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

		$class_title       = esc_html__( 'Front End Login', 'uncanny-learndash-toolkit' );
		$kb_link           = 'https://www.uncannyowl.com/knowledge-base/front-end-login/';
		$class_description = esc_html__( 'Adds a custom login form and can optionally force users to be verified by an admin before they can sign in.', 'uncanny-learndash-toolkit' );
		$class_icon        = '<i class="uo_icon_fa fa fa-sign-in"></i>';
		$tags              = 'user-handling';
		$type              = 'free';

		return array(
			'title'            => $class_title,
			'type'             => $type,
			'tags'             => $tags,
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
	 *
	 * @param String
	 *
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

		$pages = get_pages( $args );

		$drop_down = array();
		array_push( $drop_down, array( 'value' => 0, 'text' => '- Select Page -' ) );

		foreach ( $pages as $page ) {
			array_push( $drop_down, array( 'value' => $page->ID, 'text' => $page->post_title ) );
		}

		// Create options
		$options = array(

			array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Manual User Verification', 'uncanny-learndash-toolkit' ),
				'option_name' => 'uo_frontendloginplus_needs_verifcation',
			),

			/*array(
				'type'        => 'checkbox',
				'label'       => 'Frontend Registration',
				'option_name' => 'uo_frontend_registration',
			),*/

			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Login Page', 'uncanny-learndash-toolkit' ),
				'select_name' => 'login_page',
				'options'     => $drop_down,
			),

			array(
				'type'        => 'select',
				'label'       => esc_html__( 'Login Label', 'uncanny-learndash-toolkit' ),
				'select_name' => 'uo_login_username_label',
				'options'     => array(
					array( 'value' => '', 'text' => '- Select Label -' ),
					array( 'value' => 'Username', 'text' => 'Username' ),
					array( 'value' => 'Email', 'text' => 'Email' ),
					array( 'value' => 'Username Or Email', 'text' => 'Username Or Email' ),
					array( 'value' => 'Login', 'text' => 'Login' )
				)
			),

			/*array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Hide "Login UI" when user is logged in.', 'uncanny-learndash-toolkit' ),
				'option_name' => 'hide_logged_in_ui'
			)*/

		);


		// Build html
		$html = self::settings_output( array(
			'class'   => __CLASS__,
			'title'   => $class_title,
			'options' => $options,
		) );

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

		if ( 'uo_column' === $column_name ) {

			$message = 'Not Verified';

			if ( '1' === $user_verified_value ) {
				$message = '<span style="color:green;">Verified</span>';
			}

			if ( user_can( $user_id, 'activate_plugins' ) ) {
				$message = '<span style="color:green;">Auto Verified</span>';
			}

			return $message;
		}

		return $value;
	}

	/*
	 * Add custom field to user profile
	 * @param object $user
	 */
	public static function my_show_extra_profile_fields( $user ) {
		$checked = esc_attr( get_user_meta( $user->ID, 'uo_is_verified', true ) );

		if ( user_can( $user->ID, 'activate_plugins' ) ) {
			$checked = '1';
		}


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
						<input type="checkbox" name="uo_is_verified"
						       value="1" <?php checked( $checked, $current, $echo ); ?>/>
						Verify this user and allow them to log in
					</label>
				</td>
			</tr>

		</table>

		<?php
	}

	/*
	 * Save custom fields from user profile
	 * @param int $user_id
	 *
	 */
	public static function my_save_extra_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user' ) ) {
			return false;
		}

		update_user_meta( $user_id, 'uo_is_verified', $_POST['uo_is_verified'] );

		$verified            = get_user_meta( $user_id, 'uo_is_verified', true );
		$verified_email_sent = get_user_meta( $user_id, 'uo_verified_email_sent', true );

		if ( $verified === '1' && 'yes' !== $verified_email_sent ) {

			$user = get_userdata( $user_id );

			$blog_name   = get_option( 'blogname' );
			$admin_email = get_option( 'admin_email' );

			$from      = $blog_name . ' <' . $admin_email . '>';
			$headers[] = 'From: ' . $from;
			$headers   = apply_filters( 'uo_verified_email_headers', $headers, $user );

			$to = $user->user_email;

			$subject = $blog_name . ' - Account Verified';
			$subject = apply_filters( 'uo_verified_email_subject', $subject, $user );

			$message = "Your account has been approved! \r\n\n";
			$message .= "Please visit " . home_url() . " to login \r\n";
			$message = apply_filters( 'uo_verified_email_message', $message, $user );

			$mailed = wp_mail( $to, $subject, $message, $headers );

			// after wp_mail successful

			$from      = $blog_name . ' <' . $admin_email . '>';
			$headers[] = 'From: ' . $from;
			$headers   = apply_filters( 'uo_verified_email_headers', $headers, $user );

			$to = $admin_email;

			$subject = $blog_name . ' - Account Verified';
			$subject = apply_filters( 'uo_verified_email_subject', $subject, $user );

			$message      = $user->user_email . " account has been approved! \r\n\n";
			$message      .= "Visit  " . admin_url( 'user-edit.php?user_id=' . $user->id ) . " to view / edit user. \r\n";
			$message      = apply_filters( 'uo_verified_email_message', $message, $user );
			$admin_mailed = wp_mail( $to, $subject, $message, $headers );

			// after wp_mail successful
			if ( $admin_mailed && $mailed ) {
				update_user_meta( $user_id, 'uo_verified_email_sent', 'yes' );
			}

		}

		return true;

	}

	/**
	 * Show a notice to users who try to login and are disabled
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function user_login_message( $message ) {
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['unverified'] ) && 1 === $_GET['unverified'] ) {
			$message = '<div id="login_error">' . apply_filters( 'uo_unverified_users_notice', esc_html__( "We haven't verified this account.", 'uncanny-learndash-toolkit' ) ) . '</div>';
		}

		return $message;
	}

	public static function set_cookies() {

		global $post;

		if ( null !== $post ) {

			$current_post_id = $post->ID;

			$login_page_id = self::get_login_redirect_page_id();

			if ( $current_post_id === $login_page_id ) {

				/* Set Reset Password Cookie */
				if ( isset( $_GET['action'] ) ) {
					if ( 'rp' === $_GET['action'] ) {

						if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

							$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
							$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );

							setcookie( $rp_cookie, $value, time() + 3600, '/' . get_post_field( 'post_name', $login_page_id ), COOKIE_DOMAIN, is_ssl(), true );
						}
					}
				}
			}
		}
	}

	public static function maybe_set_cookies() {

		global $post;

		if ( null !== $post ) {

			$current_post_id = $post->ID;

			$login_page_id = self::get_login_redirect_page_id();

			if ( $current_post_id === $login_page_id ) {

				/* Set Reset Password Cookie */
				if ( isset( $_GET['action'] ) ) {
					if ( 'rp' === $_GET['action'] ) {

						if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

							$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
							$value     = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
							setcookie( $rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), true );
						}
					}
				}
			}
		}


	}


	public static function uo_login_form( $atts, $content = null ) {

		/*$hide_logged_in_ui = self::get_settings_value( 'hide_logged_in_ui', __CLASS__ );

		if(is_user_logged_in() && '' === $hide_logged_in_ui ){

		}else{

		}*/

		if ( is_user_logged_in() ) {
			return do_shortcode( $content );
		} else {


			$username_label   = ( isset( $atts['username-label'] ) ? $atts['username-label'] : esc_html__( 'Username', 'uncanny-learndash-toolkit' ) );
			$password_label   = ( isset( $atts['password-label'] ) ? $atts['password-label'] : esc_html__( 'Password', 'uncanny-learndash-toolkit' ) );
			$rememberme_label = ( isset( $atts['rememberme-label'] ) ? $atts['rememberme-label'] : esc_html__( 'Remember Me', 'uncanny-learndash-toolkit' ) );

			$register_link = ( isset( $atts['register_link'] ) ? $atts['register_link'] : 'no' );

			if ( 'yes' !== $register_link ) {

			}

			$placeholder  = ( isset( $atts['placeholder'] ) ? $atts['placeholder'] : 'yes' );
			$redirect     = ( isset( $atts['redirect'] ) ? $atts['redirect'] : home_url() );
			$submit_label = ( isset( $atts['#loginform'] ) ? $atts['submit-label'] : esc_html__( 'Log In', 'uncanny-learndash-toolkit' ) );

			if ( 'no' !== $placeholder ) {
				?>
				<script type='text/javascript'>
                  jQuery(document).ready(function () {
                    jQuery('#user_login').attr('placeholder', '<?php echo $username_label; ?>')
                    jQuery('#user_pass').attr('placeholder', '<?php echo $password_label; ?>')
                  })
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
				'value_username' => null,
				'value_remember' => true,
			);

			return wp_login_form( $login_form_args );

		}

	}

	/*
	 * UO login ui shortcode
	 */
	public static function uo_login_ui() {

		// if the slug or page title in login run the login page template
		$page_template = self::get_template( '/login-page-ui.php' );
		$page_template = apply_filters( 'uo_login_ui_template', $page_template );

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

		$registering = false;
		if ( isset( $_GET['action'] ) ) {
			if ( 'register' === $_GET['action'] ) {
				$registering = true;
			}
		}
		/*
				if ( isset( $_GET['registration'] ) ) {
					if ( $_GET['registration'] === 'disabled' ) {
						wp_safe_redirect( add_query_arg( array(
							'action'   => 'register',
							'wp-error' => 'registration-disabled'
						), $login_page ) );
						exit();
					}
				}

				if ( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] ) {
					wp_safe_redirect( add_query_arg( array(
						'action'   => 'register',
						'wp-error' => 'registration-success'
					), $login_page ) );
					exit();
				}
		*/
		if ( 'wp-login.php' === $page_viewed && 'GET' === $_SERVER['REQUEST_METHOD'] && ! $registering ) {
			wp_safe_redirect( $login_page );
			exit;
		}

	}

	/**
	 * Redirect from registration to custom login page
	 */
	public static function redirect_registration() {

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$query           = array();
		$query['action'] = 'register';

		if ( isset( $_GET['checkemail'] ) && 'registered' === $_GET['checkemail'] ) {
			$query['registered'] = 'success';
		}

		wp_safe_redirect( add_query_arg( $query, $login_page ) );
		exit;
	}

	/*
	 * Redirect from registration errors to custom login page
	 * @param string   $sanitized_user_login The submitted username after being sanitized.
	 * @param string   $user_email           The submitted email.
	 * @param WP_Error $errors               Contains any errors with submitted username and email,
	 *                                       e.g., an empty field, an invalid username or email,
	 *                                       e.g., an empty field, an invalid username or email,
	 */
	public static function redirect_registration_errors( $sanitized_user_login, $user_email, $errors ) {

		if ( ! empty( $errors->errors ) ) {
			$error_code = $errors->get_error_code();
			self::redirect_registration_error( $error_code );
			exit();
		}

	}

	public static function redirect_registration_error( $error_code ) {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'action' => 'register', 'wp-error' => $error_code ), $login_page ) );
		exit;
	}

	/**
	 * Redirect to custom login page if login has failed
	 */
	public static function login_failed() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'login' => 'failed' ), $login_page ) );
		exit;
	}

	/**
	 * Redirect to custom login page if username or password is empty
	 */
	public static function verify_username_password( $user, $username, $password ) {

		$uo_manual_verification = 'no';

		$settings = get_option( 'FrontendLoginPlus', array() );

		if ( false !== $settings ) {

			foreach ( $settings as $setting ) {

				if ( 'uo_frontendloginplus_needs_verifcation' === $setting['name'] && 'on' === $setting['value'] ) {
					$uo_manual_verification = 'yes';
				}
			}
		}

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		if ( 'yes' === $uo_manual_verification ) {
			if ( is_email( $username ) ) {
				$user = get_user_by( 'email', $username );
			} else {
				$user = get_user_by( 'login', $username );
			}

			if ( $user ) {

				$user_verified_value = get_user_meta( $user->ID, self::$user_meta_key_col, true );

				// bypass admins
				if ( user_can( $user->ID, 'activate_plugins' ) ) {
					$user_verified_value = '1';
				}

				// Is the use logging in disabled?
				if ( '1' !== $user_verified_value ) {
					wp_destroy_current_session();
					wp_clear_auth_cookie();
					wp_safe_redirect( add_query_arg( array( 'login' => 'notverified' ), $login_page ) );
					exit;
				}
			} else {
				$login_page = get_permalink( self::get_login_redirect_page_id() );
				wp_safe_redirect( add_query_arg( array( 'login' => 'failed' ), $login_page ) );
				exit;
			}
		}

		$registering = false;
		if ( isset( $_GET['checkemail'] ) && 'registered' === $_GET['checkemail'] ) {
			$registering = true;
		}

		if ( '' === $username || '' === $password ) {
			if ( false === $registering ) {
				wp_safe_redirect( add_query_arg( array( 'login' => 'empty' ), $login_page ) );
				exit;
			}

		}
	}

	/**
	 * Redirect from wp-login.php to custom login page if user logged out
	 */
	public static function logout_page() {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'login' => 'false' ), $login_page ) );
		exit;
	}

	/**
	 * Redirect from wp-login.php to custom login page if user lost password
	 */
	public static function redirect_lost_password( $lostpassword_redirect ) {
		$login_page = get_permalink( self::get_login_redirect_page_id() );
		wp_safe_redirect( add_query_arg( array( 'action' => 'forgot', 'success' => 'something' ), $login_page ) );
	}


	/**
	 * Set wp-login redirect to frontend page
	 */
	public static function get_login_redirect_page_id() {

		$page_id = 0;

		$settings = get_option( 'FrontendLoginPlus', array() );

		foreach ( $settings as $setting ) {
			if ( 'login_page' === $setting['name'] ) {
				$page_id = $setting['value'];
			}
		}


		return (int) $page_id;
	}

	/*
	 * Add lost password link the login form
	 */
	public static function add_lost_password_link() {

		$login_page = get_permalink( self::get_login_redirect_page_id() );
		$link       = add_query_arg( array( 'action' => 'lostpassword' ), $login_page );

		return '<a class="forgot-link" href="' . $link . '">' . esc_html__( 'Forgot Your Password?', 'uncanny-learndash-toolkit' ) . '</a>';
	}

	/*
	 * Custom email message to retrieve password
	 */
	public static function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {

		//todo add filters and escape translations

		$login_page = get_permalink( self::get_login_redirect_page_id() );

		$reset_args = array(
			'action' => 'rp',
			'key'    => $key,
			'login'  => rawurlencode( $user_login ),
		);

		$reset_link = add_query_arg( $reset_args, $login_page );

		$new_message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$new_message .= network_home_url( '/' ) . "\r\n\r\n";
		$new_message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$new_message .= __( 'If this was a mistake or you didn\'t request a change, you can safely ignore this email.' ) . "\r\n\r\n";
		$new_message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		if ( 'text/html' == apply_filters( 'wp_mail_content_type', 'text/html' ) ) {
			$new_message .= sprintf( __( 'Reset password link: %s' ), $reset_link ) . "\r\n";
		} else {
			$new_message .= '<a href="' . $reset_link . '" >' . __( 'Reset Password' ) . "</a>\r\n";
		}

		return $new_message;
	}

	/*
	 * Prevent login lock out if frontend login is enabled but no shortcode exists on login page
	 * Add the temporary shortcode with a warning
	 */
	public static function maybe_add_ui_shortcode() {

		global $post;

		$settings = get_option( 'FrontendLoginPlus', array() );

		foreach ( $settings as $setting ) {

			if ( 'login_page' === $setting['name'] && '0' !== $setting['value'] ) {
				$login_page_id = $setting['value'];
				if ( $post->ID == (int) $login_page_id ) {
					if ( ! has_shortcode( $post->post_content, 'uo_login_ui' ) ) {
						echo '<strong>Note: This page has been set as the login page for this site.  The form below has been added for your convenience.  To hide this message, add the shortcode [uo_login_ui] to this page.</strong>';
						echo do_shortcode( '[uo_login_ui]' );
					}
				}
			}
		}
	}
}