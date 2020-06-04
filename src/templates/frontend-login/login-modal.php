<?php
namespace uncanny_learndash_toolkit;
?>

<div class="ult-modal" data-id="ult-login" data-btn-dismiss="0">
    <div id="ult-login-modal" class="ult-login-modal--login">
        <div id="ult-login-modal__login">
            <?php echo do_shortcode( '[uo_login_ui]' ); ?>
        </div>
        <div id="ult-login-modal__forgot-password">
            <?php

            $innerText        = apply_filters( 'uo-login-inner-text', FrontendLoginPlus::fetch_inner_text(), $login );

            $template_to_load = apply_filters( 'uo-login-template', Config::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', 'default' ) );
            
            include( Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) ) );

            ?>
        </div>
    </div>
</div>