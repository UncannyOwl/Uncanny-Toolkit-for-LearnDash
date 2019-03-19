<?php

namespace uncanny_learndash_toolkit;

?>
<?php do_action( 'uo_reset_before_container' ); ?>
<?php do_action( 'uo_reset_before_title' ); ?>
<h2><?php echo $innerText['Reset-Password-Title']; ?></h2>
<?php do_action( 'uo_reset_before_description' ); ?>
<p><?php echo $innerText['Reset-Password-Desc']; ?></p>
<?php do_action( 'uo_reset_before_form' ); ?>
<form name="resetpassform" id="resetpassform" action="?action=validatepasswordreset" method="post" autocomplete="off">
	<?php if ( ! empty( $error ) ) { ?>
		<?php do_action( 'uo_reset_before_error' ); ?>
        <p>
			<?php do_action( 'uo_reset_before_error_message' ); ?>
			<?php echo $error; ?>
			<?php do_action( 'uo_reset_after_error_message' ); ?>
        </p>
	<?php } ?>

    <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"
           autocomplete="off"/>
	<?php do_action( 'uo_reset_before_password' ); ?>
    <div class="user-pass1-wrap">
        <p>
            <label for="pass1"><?php echo $innerText['New-Password']; ?></label>
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
			<?php do_action( 'uo_reset_password_actions' ); ?>
        </div>
    </div>
	<?php do_action( 'uo_reset_before_confirm_password' ); ?>
    <p class="user-pass2-wrap">
        <label for="pass2"><?php echo $innerText['Confirm-Password']; ?></label><br/>
        <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" required/>
    </p>
    <div class="wp-pwd">
		<?php do_action( 'uo_reset_confirm_password_actions' ); ?>
    </div>
	<?php do_action( 'uo_reset_before_captcha' ); ?>
    <!--	<p class="description indicator-hint">--><?php /*echo $innerText['Password-Indicator-Hint'];*/ ?><!--</p>-->
    <br class="clear"/>
    <input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
	<?php do_action( 'uo_reset_before_submit' ); ?>
    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
                             class="button button-primary button-large"
                             value="<?php echo $innerText['Reset-Password-Button']; ?>"/></p>
	<?php do_action( 'uo_reset_after_submit' ); ?>
</form>
<?php do_action( 'uo_reset_after_form' ); ?>
<?php do_action( 'uo_reset_after_container' ); ?>
