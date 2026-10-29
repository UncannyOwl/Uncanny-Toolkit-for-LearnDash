<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php do_action( 'uo_reset_before_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
<?php do_action( 'uo_reset_before_title' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
<h2><?php echo wp_kses_post( $innerText['Reset-Password-Title'] ); ?></h2>
<?php do_action( 'uo_reset_before_description' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
<p><?php echo wp_kses_post( $innerText['Reset-Password-Desc'] ); ?></p>
<?php do_action( 'uo_reset_before_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
<form name="resetpassform" id="resetpassform" action="?action=validatepasswordreset" method="post" autocomplete="off">
	<?php if ( ! empty( $error ) ) { ?>
		<?php do_action( 'uo_reset_before_error' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
        <p>
			<?php do_action( 'uo_reset_before_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
			<?php echo wp_kses_post( $error ); ?>
			<?php do_action( 'uo_reset_after_error_message' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
        </p>
	<?php } ?>

    <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"
           autocomplete="off"/>
	<?php do_action( 'uo_reset_before_password' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
    <div class="user-pass1-wrap">
        <p>
            <label for="pass1"><?php echo wp_kses_post( $innerText['New-Password'] ); ?></label>
        </p>

        <div class="wp-pwd">
                            <span class="password-input-wrapper">
                                <input type="password" data-reveal="1"
                                       data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1"
                                       id="pass1" class="input" size="20" value="" autocomplete="off"
                                       aria-describedby="pass-strength-result" required/>
                            </span>
        </div>
        <div class="wp-pwd">
			<?php do_action( 'uo_reset_password_actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
        </div>
    </div>
	<?php do_action( 'uo_reset_before_confirm_password' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
    <p class="user-pass2-wrap">
        <label for="pass2"><?php echo wp_kses_post( $innerText['Confirm-Password'] ); ?></label><br/>
        <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" required/>
    </p>
    <div class="wp-pwd">
		<?php do_action( 'uo_reset_confirm_password_actions' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
    </div>
	<?php do_action( 'uo_reset_before_captcha' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
    <!--	<p class="description indicator-hint">--><?php /*echo $innerText['Password-Indicator-Hint'];*/ ?><!--</p>-->
    <br class="clear"/>
    <input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
	<?php do_action( 'uo_reset_before_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
                             class="button button-primary button-large"
                             value="<?php echo esc_attr( $innerText['Reset-Password-Button'] ); ?>"/></p>
	<?php do_action( 'uo_reset_after_submit' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
</form>
<?php do_action( 'uo_reset_after_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
<?php do_action( 'uo_reset_after_container' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public Toolkit hook kept for backward compatibility (used by Toolkit Pro and site code). ?>
