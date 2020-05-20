<?php
namespace uncanny_learndash_toolkit;

?>
<div class="ult-wrapper ult-wrapper-login-modal ult-modal-closeda">
    <div class="ult-modal ult-login-modal">
        <span class="ult-modal-closer ult-icon ult-icon-delete"></span>
        <div class="ult-login-modal-login" id="ult-login">
            <div class="ult-login-modal-wrapper">
				<?php
				echo do_shortcode( '[uo_login_ui]' );
				?>
            </div>
        </div>
        <div class="ult-login-modal-login ult-hide" id="ult-last-pwd">
            <div class="ult-login-modal-wrapper">
				<?php
				//Introducing different templates!
				$innerText        = apply_filters( 'uo-login-inner-text', FrontendLoginPlus::fetch_inner_text(), $login );
				$template_to_load = apply_filters( 'uo-login-template', Config::get_settings_value( 'uo_frontend_login_template', 'FrontendLoginPlus', 'default' ) );
				include( Config::get_template( apply_filters( 'uo-front-login-lost-pwd-template', 'frontend-login/' . $template_to_load . '-lost-pwd.php', $template_to_load ) ) );
				?>
            </div>
        </div>
    </div>
</div>
