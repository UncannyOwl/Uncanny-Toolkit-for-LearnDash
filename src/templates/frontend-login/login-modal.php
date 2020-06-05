<?php
namespace uncanny_learndash_toolkit;

$uo_frontend_login_modal_background = '1';
if ( "on" === Config::get_settings_value( 'uo_frontend_login_modal_background', 'FrontendLoginPlus' ) ) {
	$uo_frontend_login_modal_background = '0';
}
add_filter( 'uo-login-template', function ( $layout ) {
	return 'layout_1';
}, 100 );
?>
<div class="ult-modal" data-id="ult-login" data-btn-dismiss="0" data-dim-background="<?php echo $uo_frontend_login_modal_background;?>">
    <div id="ult-login-modal" class="ult-login-modal--login">
        <div id="ult-login-modal__login">
            <?php echo do_shortcode( '[uo_login_ui]' ); ?>
        </div>
        <div id="ult-login-modal__forgot-password">
            <?php
            $recaptcha_key         = Config::get_settings_value( 'uo_frontend_login_recaptcha_key', 'FrontendLoginPlus' );
            $recaptcha_secrete_key = Config::get_settings_value( 'uo_frontend_login_recaptcha_secret_key', 'FrontendLoginPlus' );
            $innerText        = apply_filters( 'uo-login-inner-text', FrontendLoginPlus::fetch_inner_text(), 'not-set' );
            
            include( Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/layout_1-lost-pwd.php', 'layout_1' ) ) );

            ?>
        </div>
    </div>
</div>