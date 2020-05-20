<div class="notice" id="ult-review-banner" style="display: none">
	
	<div class="ult">
		<div class="ult-review-banner">
			<div class="ult-review-banner__title">
				<?php _ex( 'Enjoying Uncanny LearnDash Toolkit?', 'Reviews banner', 'uncanny-automator' ); ?> 🙂
			</div>
			<div class="ult-review-banner__description">
				<?php _ex( '5-star reviews inspire us to make the plugin even better!', 'Reviews banner', 'uncanny-automator' ); ?>
			</div>
			<div class="ult-review-banner__actions">
				<a href="<?php echo $url_send_review; ?>" target="_blank" class="ult-review-banner__action ult-review-banner__action--primary" data-action="hide-forever">
					<?php

					/* translators: Non-personal infinitive verb */
					_ex( 'Add my review', 'Reviews banner', 'uncanny-automator' );

					?>
				</a>
				
				<a href="<?php echo $url_send_feedback; ?>" target="_blank" class="ult-review-banner__action ult-review-banner__action--secondary" data-action="hide-forever">
					<?php

					/* translators: Non-personal infinitive verb */
					_ex( 'Send feedback', 'Reviews banner', 'uncanny-automator' );

					?>
				</a>
				
				<div class="ult-review-banner__action ult-review-banner__action--secondary" data-action="maybe-later">
					<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-automator' ); ?>
				</div>
				
				<div class="ult-review-banner__action ult-review-banner__action--anchor ult-review-banner__action--no-margin-right" data-action="hide-forever">
					<span class="ult-review-banner__action--anchor-border"><?php _ex( 'No, thanks', 'Reviews banner', 'uncanny-automator' ); ?></span>
				</div>
			</div>
			<div id="ult-review-banner__close">
				<div class="ult-review-banner__close-tooltip" ult-tooltip-admin="<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-automator' ); ?>" ult-flow-admin="left"></div>
				<div class="ult-review-banner__close-icon"></div>
			</div>
		</div>
	</div>

</div>