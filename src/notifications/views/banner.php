<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}
?>

<?php if ( ! empty( $notifications ) ) { ?>

	<div id="uo-notifications-wrap" class="uo-notifications">

		<h3 class="uo-notifications__title uo-settings-panel-content-subtitle">

			<span class="dashicons dashicons-bell"></span>

			<span class="notifications-count">

				<?php echo esc_html( count( $notifications ) ); ?>

			</span>

			<?php esc_html_e( 'Notifications', 'uncanny-learndash-toolkit' ); ?>

		</h3><!--.uo-notifications__title-->

		<ul class="uo-notifications-list">

			<?php $counter = 0; ?>

			<?php foreach ( $notifications as $notification ) { ?>

				<?php $counter++; ?>

				<li data-index="<?php echo esc_attr( $counter ); ?>" class="uo-notifications-list__item <?php echo 1 === $counter ? 'active' : ''; ?>">

				<div class="uo-notifications__wrap">

					<div class="uo-notifications-list__item-title">

						<h3 class="uo-settings-panel-content-subtitle">

							<?php echo esc_html( $notification['title'] ); ?>

						</h3>

						<a href="#" class="uo-notifications-action-dismiss" data-notification-id="<?php echo esc_attr( absint( $notification['id'] ) ); ?>" size="small" color="danger">

							<?php esc_html_e( 'Dismiss', 'uncanny-learndash-toolkit' ); ?>

						</a>

					</div><!--.uo-notifications-list__item-title-->

					<div class="uo-notifications-list__item-content">
						<?php
							echo wp_kses(
								$notification['content'],
								array(
									'p'      => array(),
									'br'     => array(),
									'strong' => '',
									'em'     => '',
									'a'      => array(
										'href'  => array(),
										'title' => array(),
									),
								)
							);
						?>
					</div><!--.uo-notifications-list__item-content-->

					<div class="uo-notifications-list__item-actions">

						<div class="uo-notifications-list__item-actions__links">

							<?php
							if ( ! empty( $notification['btns']['main']['url'] ) &&
									! empty( $notification['btns']['main']['text'] ) ) {
								?>

								<a class="uo-notifications--primary-button" target="_blank" href="<?php echo esc_url( $this->url_add_utm( $notification['btns']['main']['url'], $notification['title'], $notification['btns']['main']['text'] ) ); ?>">
								<?php echo esc_html( $notification['btns']['main']['text'] ); ?>
								</a>

							<?php } ?>

							<?php
							if ( ! empty( $notification['btns']['alt']['url'] ) &&
									! empty( $notification['btns']['alt']['text'] ) ) {
								?>
									<a class="uo-notifications--secondary-button" target="_blank" href="<?php echo esc_url( $this->url_add_utm( $notification['btns']['alt']['url'], $notification['title'], $notification['btns']['alt']['text'] ) ); ?>">
									<?php echo esc_html( $notification['btns']['alt']['text'] ); ?>
									</a>

							<?php } ?>

						</div>

						<?php if ( count( $notifications ) >= 2 ) { ?>

							<div class="uo-notifications-list__item-actions__controls">

								<button href="#" class="uo-notifications-controller-prev">
									&larr;
								</button>

								<button class="uo-notifications-controller-next">
									&rarr;
								</button>

							</div>

						<?php } ?>
					</div><!--.uo-notifications-list__item-actions-->

				</div><!--.uo-notifications__wrap-->

				</li><!--.uo-notifications-list__item-->

			<?php } ?>

		</ul><!--.uo-notifications-list-->

	</div><!--#uo-notifications-wrap-->

<?php } ?>
