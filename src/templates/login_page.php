<?php
/* Template Name: Uncanny Owl Login Page */

global $user_login;

/* Login */
$login  = (isset($_GET['login']) ) ? $_GET['login'] : 'not-set';

/* Lost Password */
$lost_password = false;
if( isset($_GET['action']) ){
    if( 'lostpassword' === $_GET['action'] ){
        $lost_password = true;
    }
}

/* Sending Reset Password Link */
$reset_password_sent = false;
$reset_password_sent_success = false;
if( isset($_GET['action']) ){
    if( 'forgot' === $_GET['action'] ){
        $reset_password_sent = true;
        if( '1' === $_GET['success'] ){
            $reset_password_sent_success = true;
        }
    }
}

/* Reset Password */
$reset_password = false;
if( isset($_GET['action']) ){
    if( 'rp' === $_GET['action'] ){
        $reset_password = true;
    }
}

/* Validate Reset Password Information */
$validate_password_reset = false;
if( isset($_GET['action']) ){
    if( 'validatepasswordreset' === $_GET['action'] ){
        $validate_password_reset = true;
    }
}

switch ($login) {

    case 'failed':
        $message_error = __('Woops!', '');
        $message_warning = __('Invalid username and/or password.', '');
        break;
    case 'empty':
        $message_error = __('Woops!', '');
        $message_warning = __('Username and/or Password is empty.', '');
        break;
    case 'false':
        $message_error = __('', '');
        $message_warning = __('You are logged out.', '');
        break;
    default:
        $message_error = '';
        $message_warning = '';
}

$login_error = '<p class="login-msg"><strong>'. $message_error .'</strong> '. $message_warning .'</p>';

$login_form_args = array(
    'echo'           => true,
    'redirect'       => home_url('/wp-admin/'),
    'form_id'        => 'loginform',
    'label_username' => __( 'Username' ),
    'label_password' => __( 'Password' ),
    'label_remember' => __( 'Remember Me' ),
    'label_log_in'   => __( 'Log In' ),
    'id_username'    => 'user_login',
    'id_password'    => 'user_pass',
    'id_remember'    => 'rememberme',
    'id_submit'      => 'wp-submit',
    'remember'       => true,
    'value_username' => NULL,
    'value_remember' => true
);

get_header();

?>
    <!-- section -->
    <section class="uo_loginForm">
        <div class="uo_error">
            <?php echo $login_error; ?>
        </div>

        <?php
        if ( is_user_logged_in() ) {
            echo '<div class="uo_logout"> Hello,
                            <div class="uo_logout_user">', $user_login, '. You are already logged in.</div>
                            <a id="wp-submit" href="', wp_logout_url(), '" title="Logout">Logout</a>
                        </div>';
        } else if($lost_password){
            ?>
            <h2>Password Recovery</h2>
            <form id="lostpasswordform" name="lostpasswordform" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" method="post">
                <p>
                    <label for="user_login">Username or E-mail:</label>
                    <input size="20" type="text" name="user_login" id="user_login" value="">
                </p>

                <input type="hidden" name="redirect_to" value="/login/?action=forgot&success=1">
                <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="Get New Password" /></p>
            </form>
        <?php
        }elseif( $reset_password_sent ){
            if( $reset_password_sent_success ){
                ?>
                <p class="login-msg"><strong>Success!</strong> Check your email for a reset password link.</p>
                <?php
            }else{
                ?>
                <p class="login-msg"><strong>Woops!</strong> Password Reset Failed.</p>
                <?php
            }

        }elseif( $reset_password ){

            if( isset($_GET['key']) && isset($_GET['login']) ){
                $rp_key = $_GET['key'];
                $rp_login = $_GET['login'];
                $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
                $value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
                setcookie( $rp_cookie, $value, 0, '/login', COOKIE_DOMAIN, is_ssl(), true );

                ?>
                <h2>Reset Password</h2>
                <form name="resetpassform" id="resetpassform" action="<?php echo esc_url( network_site_url( 'login?action=validatepasswordreset', 'login_post' ) ); ?>" method="post" autocomplete="off">
                    <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>" autocomplete="off" />

                    <div class="user-pass1-wrap">
                        <p>
                            <label for="pass1"><?php _e( 'New password' ) ?></label>
                        </p>
                        <div class="wp-pwd">
                            <span class="password-input-wrapper">
                                <input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" aria-describedby="pass-strength-result" />
                            </span>
                        </div>
                    </div>
                    <p class="user-pass2-wrap">
                        <label for="pass2"><?php _e( 'Confirm new password' ) ?></label><br />
                        <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
                    </p>
                    <p class="description indicator-hint">Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; )</p>
                    <br class="clear" />
                    <input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>" />
                    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Reset Password'); ?>" /></p>
                </form>
                <?php

            }else{

                ?>
                <p class="login-msg"><strong>Woops!</strong> Password Reset Link Failed.</p>
                <?php
            }


        }elseif($validate_password_reset){
            if( isset( $_GET['issue'] ) ){

                if( $_GET['issue'] === 'invalidkey' ){
                    echo '<h2>Your password reset link is invalid.</h2>';
                }elseif( $_GET['issue'] === 'expiredkey' ){
                    echo '<h2>Your password reset link is expired.</h2>';
                }

            }else{

                $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
                if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
                    list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
                    $user = check_password_reset_key( $rp_key, $rp_login );
                    //var_dump($user);
                    if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
                        $user = false;
                    }

                } else {
                    $user = false;
                }

                if (  ! $user || is_wp_error( $user ) && ! isset( $_POST['pass1'] ) ) {

                    setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );
                    if ( $user && $user->get_error_code() === 'expired_key' )
                        wp_redirect( site_url( 'login/?action=validatepasswordreset&issue=expiredkey' ) );
                    else
                        wp_redirect( site_url( 'login/?action=validatepasswordreset&issue=invalidkey' ) );
                }

                $errors = new WP_Error();

                if ( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ){

                    echo '<h2>Your Passwords did not match. Please try agian.</h2>';

                }elseif ( isset( $_POST['pass1'] ) && !empty( $_POST['pass1'] ) ) {

                    reset_password($user, $_POST['pass1']);
                    setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/login', COOKIE_DOMAIN, is_ssl(), true );
                    ?>
                        <h2>Your password was reset successfully. Please Log-In.</h2>
                    <?php

                    wp_login_form($login_form_args);

                }

            }

        }else{
            ?>
            <h2>Login</h2>
            <?php
            wp_login_form($login_form_args);
        }
        ?>
        
    </section>
    <!-- /section -->

<?php get_footer(); ?>
