<div class="notice" id="ult-review-banner" style="display: none">
	
	<div class="ult">
		<div class="ult-review-banner">
			<div class="ult-review-banner-left">
				<div class="ult-review-banner__robot">
					<img src="<?php echo \uncanny_automator\Utilities::get_media( 'admin/robot-feedback.svg' ); ?>">
				</div>
			</div>
			<div class="ult-review-banner-right">
				<div class="ult-review-banner__title">
					<?php _ex( 'Automator\'s robot would love to get your opinion', 'Reviews banner', 'uncanny-automator' ); ?>
				</div>
				<div class="ult-review-banner__description">
					<?php _ex( 'The robot\'s favorite food is 5-star reviews!', 'Reviews banner', 'uncanny-automator' ); ?>
				</div>
				<div class="ult-review-banner__actions">
					<a href="<?php echo $url_send_review; ?>" target="_blank" class="ult-review-banner__action uap-review-banner__action--primary" data-action="hide-forever">
						<?php

						/* translators: Non-personal infinitive verb */
						_ex( 'Add my review', 'Reviews banner', 'uncanny-automator' );

						?>
					</a>
					
					<a href="<?php echo $url_send_feedback; ?>" target="_blank" class="ult-review-banner__action uap-review-banner__action--secondary" data-action="hide-forever">
						<?php

						/* translators: Non-personal infinitive verb */
						_ex( 'Send feedback', 'Reviews banner', 'uncanny-automator' );

						?>
					</a>
					
					<div class="ult-review-banner__action uap-review-banner__action--secondary" data-action="maybe-later">
						<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-automator' ); ?>
					</div>
					
					<div class="ult-review-banner__action uap-review-banner__action--anchor uap-review-banner__action--no-margin-right" data-action="hide-forever">
						<span class="ult-review-banner__action--anchor-border"><?php _ex( 'Nah, a robot doesn\'t have feelings anyway', 'Reviews banner', 'uncanny-automator' ); ?></span>
						<span class="ult-review-banner__disclaimer"><?php _ex( '(Plot twist, it does)', 'Reviews banner', 'uncanny-automator' ); ?></span>
					</div>
				</div>
				<div id="ult-review-banner__close">
					<div class="ult-review-banner__close-tooltip" ult-tooltip-admin="<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-automator' ); ?>" ult-flow-admin="left"></div>
					<div class="ult-review-banner__close-icon"></div>
				</div>
			</div>
		</div>
	</div>

</div>