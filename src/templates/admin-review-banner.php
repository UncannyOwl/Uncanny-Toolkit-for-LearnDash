<div class="notice" id="ult-review-banner" style="display: none">

	<div class="ult">
		<div class="ult-review-banner">
			<div class="ult-review-banner__title">
				<?php printf(
					/* translators: 1. Trademarked term */
					_x( 'Has the %1$s been useful for your site?', 'Reviews banner', 'uncanny-learndash-toolkit' ),
					'Uncanny Toolkit for LearnDash'
				); ?> 🙂
			</div>
			<div class="ult-review-banner__description">
				<?php _ex( '5-star reviews inspire us to make the plugin even better!', 'Reviews banner', 'uncanny-learndash-toolkit' ); ?>
			</div>
			<div class="ult-review-banner__actions">
				<a 
					href="<?php echo esc_url( $url_send_review ); ?>" 
					target="_blank" 
					class="ult-review-banner__action ult-review-banner__action--primary"
					data-action="hide-banner"
				>
					<?php

					/* translators: Non-personal infinitive verb */
					_ex( 'Add my review', 'Reviews banner', 'uncanny-learndash-toolkit' );

					?>
				</a>

				<a 
					href="<?php echo esc_url( $url_send_feedback ); ?>" 
					target="_blank" 
					class="ult-review-banner__action ult-review-banner__action--secondary"
					data-action="hide-banner"
				>
					<?php

					/* translators: Non-personal infinitive verb */
					_ex( 'Send feedback', 'Reviews banner', 'uncanny-learndash-toolkit' );

					?>
				</a>

				<a 
					href=<?php echo esc_url( $url_maybe_later ); ?>
					class="ult-review-banner__action ult-review-banner__action--secondary"
				>
					<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-learndash-toolkit' ); ?>
				</a>

				<a 
					href="<?php echo esc_url( $url_hide_forever ); ?>"
					class="ult-review-banner__action ult-review-banner__action--anchor ult-review-banner__action--no-margin-right" 
				>
					<span class="ult-review-banner__action--anchor-border"><?php _ex( 'No, thanks', 'Reviews banner', 'uncanny-learndash-toolkit' ); ?></span>
				</a>
			</div>

			<a 
				href="<?php echo esc_url( $url_maybe_later ); ?>"
				id="ult-review-banner__close"
			>
				<div class="ult-review-banner__close-tooltip" ult-tooltip-admin="<?php _ex( 'Maybe later', 'Reviews banner', 'uncanny-learndash-toolkit' ); ?>" ult-flow-admin="left"></div>
				<div class="ult-review-banner__close-icon"></div>
			</a>
		</div>
	</div>

</div>
