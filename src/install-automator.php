<?php

namespace uncanny_learndash_toolkit;

/**
 * Class AdminMenu
 *
 * @package uncanny_learndash_toolkit
 */
class InstallAutomator extends Boot {

	/**
	 * class constructor
	 */
	public function __construct() {
		// Setup Theme Options Page Menu in Admin
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_try_automator_page' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_init', array( $this, 'install_callback' ), 99 );
		}

	}

	/**
	 * add_try_automator_page
	 *
	 * @return void
	 */
	public function add_try_automator_page() {
		// Check if Automator is already installed
		if ( defined( 'AUTOMATOR_BASE_FILE' ) ) {
			return;
		}
		// Check if we have to render the item
		if ( ! apply_filters( 'ult_admin_sidebar_try_automator_add', true ) ) {
			return;
		}

		// Get the item text
		$menu_item_text = apply_filters( 'ult_admin_sidebar_try_automator_text', __( 'Automation', 'uncanny-learndash-toolkit' ) );

		// Create the link content
		$menu_item_html = '<span class="ult-sidebar-featured-item">' . apply_filters( 'ult_admin_sidebar_try_automator_inner_html', '<span class="ult-sidebar-featured-item__text">' . $menu_item_text . '</span>' ) . '</span>';

		// Add the subpage menu
		add_submenu_page(
			'uncanny-toolkit',
			$menu_item_text,
			$menu_item_html,
			'manage_options',
			'install-automator',
			array( $this, 'install_automator_page' ),
			1
		);

	}


	/**
	 * enqueue_styles
	 *
	 * @param  mixed $hook
	 * @return void
	 */
	public function enqueue_styles( $hook ) {

		// Target Uncanny Automator installation page
		if ( strpos( $hook, 'install-automator' ) ) {

			// Remove all notices
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'admin_notices' );

			wp_enqueue_style( 'install-automator', Config::get_admin_css( 'install-automator.css' ), array(), UNCANNY_TOOLKIT_VERSION );
			wp_enqueue_style( 'google-fonts-css', 'https://fonts.googleapis.com/css?family=Nunito%3A700%2C800&#038', array(), UNCANNY_TOOLKIT_VERSION );

			wp_enqueue_script( 'recipe-simulator', Config::get_admin_js( 'recipe-simulator.js' ), array( 'jquery' ), UNCANNY_TOOLKIT_VERSION, true );
		}
	}


	/**
	 * install_automator_page
	 *
	 * @return void
	 */
	public function install_automator_page() {
		$one_click_install = new \uncanny_one_click_installer\Auto_Plugin_Install();
		?>
		<div class="wrap uo-install-automator">

			<div class="uo-install-automator__header">
				<h1>
					<?php esc_html_e( 'Take your site to the next level with Uncanny Automator', 'uncanny-learndash-toolkit' ); ?>
				</h1>
				<p><?php esc_html_e( 'Finding Uncanny Toolkit useful for your LearnDash site?', 'uncanny-learndash-toolkit' ); ?></br>
				<?php esc_html_e( "You'll love", 'uncanny-learndash-toolkit' ); ?> <strong><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-toolkit' ); ?></strong>. <?php esc_html_e( 'Best of all,', 'uncanny-learndash-toolkit' ); ?> <strong><?php esc_html_e( "it's free!", 'uncanny-learndash-toolkit' ); ?></strong></p>

				<div class="uo-install-automator__box">
						<div class="uo-install-automator__logo">
							<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/uncanny-automator-icon.svg' ) ); ?>" alt="Uncanny Automator logo" />
						</div>
						<div class="uo-install-automator__text">
							<h3><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-toolkit' ); ?></h3>
							<span><?php esc_html_e( 'By the creators of Uncanny Toolkit', 'uncanny-learndash-toolkit' ); ?></span>
							<span class="uo-install-automator__rating">
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
							</span>
						</div>
						<div class="uo-install-automator__button">
							<?php echo $one_click_install->button( 'uncanny-automator', admin_url( 'post-new.php?post_type=uo-recipe' ) ); ?>
						</div>
					</div>
			</div>
			<div class="uo-install-automator__body">

				<h2><?php esc_html_e( 'How it works', 'uncanny-learndash-toolkit' ); ?></h2>

				<p><?php esc_html_e( 'Use Uncanny Automator to get your favourite plugins and apps talking to each other —', 'uncanny-learndash-toolkit' ); ?>
				<strong><?php esc_html_e( 'without hiring a developer to write custom code.', 'uncanny-learndash-toolkit' ); ?></strong></p>
				<p><?php esc_html_e( 'Here are a few examples:', 'uncanny-learndash-toolkit' ); ?></p>
					<ul class="uo-install-automator__examples">
						<li>
							<?php
							echo wp_kses(
								sprintf(
									__(
										'When a user completes a course and fills out an evaluation form, add a row with the form entries to a %1$sGoogle Sheet%2$s and add the user to alumni %1$sBuddyBoss%2$s and %1$sLearnDash%2$s groups.',
										'uncanny-learndash-toolkit'
									),
									'<strong>',
									'</strong>'
								),
								array(
									'strong' => array(),
								)
							);
							?>
							<div class="uo-install-automator__requirements">
								<label><?php esc_html_e( 'Requires', 'uncanny-learndash-toolkit' ); ?></label>
								<ul>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/google-sheet-icon.svg' ) ); ?>" alt="Google Sheets icon" /></span> Google Sheets</li>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/buddyboss-icon.svg' ) ); ?>" alt="BuddyBoss icon" /></span> BuddyBoss</li>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/learndash-icon.svg' ) ); ?>" alt="Learndash icon" /></span> LearnDash</li>
								</ul>
							</div>

						</li>
						<li>
							<?php
							echo wp_kses(
								sprintf(
									__(
										'When a new blog post is published, automatically post it to %1$sTwitter%2$s and email the post to a %1$sMailChimp%2$s list.',
										'uncanny-learndash-toolkit'
									),
									'<strong>',
									'</strong>'
								),
								array(
									'strong' => array(),
								)
							);
							?>
							<div class="uo-install-automator__requirements">
								<label><?php esc_html_e( 'Requires', 'uncanny-learndash-toolkit' ); ?></label>
								<ul>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/twitter-icon.svg' ) ); ?>" alt="Twitter icon" /></span> Twitter</li>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/mailchimp-icon.svg' ) ); ?>" alt="Mailchimp icon" /></span> Mailchimp</li>
								</ul>
							</div>

						</li>
						<li>
							<?php
								echo wp_kses(
									sprintf(
										__(
											'When a user purchases a product, register them for a live event in %1$sEvents Calendar%2$s add them to a %1$sLearnDash%2$s group and send a %1$sWooCommerce%2$s coupon code for their next order.',
											'uncanny-learndash-toolkit'
										),
										'<strong>',
										'</strong>'
									),
									array(
										'strong' => array(),
									)
								);
							?>
							<div class="uo-install-automator__requirements">
								<label><?php esc_html_e( 'Requires', 'uncanny-learndash-toolkit' ); ?></label>
								<ul>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/the-events-calendar-icon.svg' ) ); ?>" alt="Events Calendar icon" /></span> Events Calendar</li>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/learndash-icon.svg' ) ); ?>" alt="Learndash icon" /></span> LearnDash</li>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/woocommerce-icon.svg' ) ); ?>" alt="WooCommerce icon" /></span> WooCommerce</li>
								</ul>
							</div>
						</li>
						<li>
							<?php
							echo wp_kses(
								sprintf(
									__(
										'When a user clicks a button, register them for a %1$sZoom%2$s webinar, reset their progress in one course and enroll them in another course.',
										'uncanny-learndash-toolkit'
									),
									'<strong>',
									'</strong>'
								),
								array(
									'strong' => array(),
								)
							);
							?>
							<div class="uo-install-automator__requirements">
								<label><?php esc_html_e( 'Requires', 'uncanny-learndash-toolkit' ); ?></label>
								<ul>
									<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/integrations/zoom-icon.svg' ) ); ?>" alt="Zoom icon" /></span> Zoom Meetings</li>
								</ul>
							</div>
						</li>
					</ul>
				<?php
				$utm = '?utm_source=';
				if ( defined( 'UNCANNY_TOOLKIT_PRO_PATH' ) ) {
					$utm .= 'uncanny_toolkit_pro';
				} else {
					$utm .= 'uncanny_toolkit_free';
				}
				$utm .= '&utm_medium=try_automator&utm_content=all_of_the_most_popular_plugins';
				?>
					<div class="uo-install-automator__recipes">
						<div class="header">
							<strong><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-toolkit' ); ?></strong> <?php esc_html_e( 'supports', 'uncanny-learndash-toolkit' ); ?> <a href="https://automatorplugin.com/integrations/<?php echo $utm; ?>" target="_blank"> <?php esc_html_e( 'all of the most popular WordPress plugins', 'uncanny-learndash-toolkit' ); ?> <span class="external-link"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-link-blue.svg' ) ); ?>" alt="External link icon" /></span></a> <?php esc_html_e( "and we're adding new integrations all the time. The possibilities are limitless.", 'uncanny-learndash-toolkit' ); ?>
						</div>
						<div class="triggers">
							<div class="uo-recipe-simulator">
								<div class="uo-recipe-simulator__title"><?php esc_html_e( 'Choose any combination of triggers', 'uncanny-learndash-toolkit' ); ?></div>
								<div class="uo-recipe-simulator__box">
									<div class="uo-recipe-simulator__items">
									<ul>
										<li><?php esc_html_e( 'Users complete a lesson', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users are added to a group', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users fill out a form', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users register for an event', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users buy a product', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users complete a course', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Users fail a quiz', 'uncanny-learndash-toolkit' ); ?></li>
									</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="actions">
							<div class="uo-recipe-simulator">
								<div class="uo-recipe-simulator__title"><?php esc_html_e( '...to initiate any combination of actions', 'uncanny-learndash-toolkit' ); ?></div>
								<div class="uo-recipe-simulator__box">
									<div class="uo-recipe-simulator__items">
									<ul>
										<li><?php esc_html_e( 'Add users to a group', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Send an email', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Mark a lesson complete', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Unlock a new course', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Reset course progress', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Trigger a Zapier webhook', 'uncanny-learndash-toolkit' ); ?></li>
										<li><?php esc_html_e( 'Add a tag in Infusionsoft', 'uncanny-learndash-toolkit' ); ?></li>
									</ul>
									</div>
								</div>
							</div>
						</div>

						<script>

							// Global JS variable to init the JS
							window.hasRecipeSimulator = true;

						</script>

						<div class="robot">
							<span class="robot"><img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/uncanny-automator-present-pose.svg' ) ); ?>" alt="External link icon" /></span>
						</div>

					</div>

					<p>
						<?php esc_html_e( 'Build better experiences for your users while saving money on custom development. And save', 'uncanny-learndash-toolkit' ); ?>
						<strong><?php esc_html_e( 'your', 'uncanny-learndash-toolkit' ); ?></strong>
						<?php esc_html_e( 'time by automating routine tasks — all with no code.', 'uncanny-learndash-toolkit' ); ?>
					</p>

					<div class="uo-install-automator__box">
						<div class="uo-install-automator__logo">
							<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/uncanny-automator-icon.svg' ) ); ?>" alt="Uncanny Automator logo" />
						</div>
						<div class="uo-install-automator__text">
							<h3><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-toolkit' ); ?></h3>
							<span><?php esc_html_e( 'By the creators of Uncanny Toolkit', 'uncanny-learndash-toolkit' ); ?></span>
							<span class="uo-install-automator__rating">
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
								<img src="<?php echo esc_url( Config::get_admin_media( 'install-automator/icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
							</span>
						</div>
						<div class="uo-install-automator__button">
							<?php echo $one_click_install->button( 'uncanny-automator', esc_url( admin_url( 'post-new.php?post_type=uo-recipe' ) ) ); ?>
						</div>
					</div>

			</div>



		</div>
		<?php
	}

	/**
	 * install_callback
	 *
	 * @return void
	 */
	public function install_callback() {
		$one_click_install = new \uncanny_one_click_installer\Auto_Plugin_Install();
		$one_click_install->create_ajax();
	}
}
