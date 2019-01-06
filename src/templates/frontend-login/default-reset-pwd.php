<?php
namespace uncanny_learndash_toolkit;
?>

<h2><?php echo $innerText['Reset-Password-Title']; ?></h2>
<p><?php echo $innerText['Reset-Password-Desc']; ?></p>

<form name="resetpassform" id="resetpassform" action="?action=validatepasswordreset" method="post" autocomplete="off">
	<?php if ( ! empty( $error ) ){ ?> 
		<p>
			<?php echo $error; ?>
		</p>
	<?php } ?>
	
	<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>"
	       autocomplete="off"/>

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
	</div>
	<p class="user-pass2-wrap">
		<label for="pass2"><?php echo $innerText['Confirm-Password']; ?></label><br/>
		<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" required/>
	</p>

<!--	<p class="description indicator-hint">--><?php /*echo $innerText['Password-Indicator-Hint'];*/ ?><!--</p>-->
	<br class="clear"/>
	<input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>"/>
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
	                         class="button button-primary button-large"
	                         value="<?php echo $innerText['Reset-Password-Button']; ?>"/></p>
</form>
