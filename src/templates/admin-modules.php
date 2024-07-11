<?php
namespace uncanny_learndash_toolkit;

/**
 * A variable with some data, for now it has:
 * - has_toolkit_pro:    Boolean to determinate if the user has the Pro version installed.
 *
 * @var Array
 */
$config = [
	'has_toolkit_pro' => ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) ? true : false,
	'get_toolkit_pro' => 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/',
	'pro_name'        => __( 'Uncanny Toolkit Pro for LearnDash', 'uncanny-learndash-toolkit' ),
];

/**
 * Array that defines all the Toolkit Free and Pro modules
 * Each element will have:
 * - title:                The title of the module
 * - description:            The description of the module
 * - keywords:                A list of words separated by commas to use
 *                            when using the search algorithm
 * - is_active:            Boolean to determinate if the module is active
 * - version:                Boolean to determinate if this module is pro-only
 * - categories:            An array with the module categories
 * - dependencies:            An array with the dependencies of this module
 * - documentation:        The URL where users will be sent when they press on "Get more info"
 * - settings_id:            The ID of the modal box that loads the fields
 *
 *
 * Don't define these elements, we're going to define them later.
 * - can_use:                Boolean to define if the user can use or not this module.
 * - cant_use_notice:        The message the user will see if can't use the module.
 * - missing_dependencies:    An array with all the mising dependencies for this module.
 *
 * @var Array
 */

$modules = AdminMenu::$modules;

/**
 * Sort alphabetically
 */

$add_on_titles = array();

foreach ( $modules as $key => $row ) {
	$add_on_titles[ $key ] = $row['title'];
}

array_multisort( $add_on_titles, SORT_ASC, $modules );

/**
 * If Pro is disabled then push Pro modules to the bottom
 */

// Check if it doesn't have the pro version
if ( ! defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) {
	// Create arrays to separate free and pro modules
	$free_modules = [];
	$pro_modules  = [];

	// Iterate each module
	foreach ( $modules as $module_key => $module ) {
		// Check if it's a pro module
		if ( isset( $module['is_pro'] ) && $module['is_pro'] ) {
			// Add to the pro array
			$pro_modules[ $module_key ] = $module;
		} else {
			// Add to the free array
			$free_modules[ $module_key ] = $module;
		}
	}

	// Create array, inserting all the free modules first
	$modules = array_merge( $free_modules, $pro_modules );
}

/**
 * Determinate if each module can be used
 * we're going to check if the user has pro enabled and if it has the required dependencies
 */

?>

<script>
	<?php
	$js_modules = [];

	foreach ( $modules as $module ) {
		$js_modules[] = [
			'id'          => $module['id'],
			'title'       => $module['title'],
			'description' => $module['description'],
		];
	}
	?>
	var ultModules = <?php echo json_encode( $js_modules ); ?>;
</script>

<div class="container">

	<div class="ult">

		<div class="ult-directory">

			<?php do_action( 'ult_before_directory_actions', $modules ); ?>

			<div class="ult-directory-actions">

				<div class="ult-directory-filters">
					<div class="ult-directory-filter ult-directory-filter--version">
						<div class="ult-form-element">
							<div class="ult-form-element__field">
								<select name="version" data-name="version" class="ult-form-element__select">
									<option value="">
										<?php _e( 'All versions', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="free">
										<?php _e( 'Free only', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="pro">
										<?php _e( 'Pro only', 'uncanny-learndash-toolkit' ) ?>
									</option>
								</select>
							</div>
						</div>
					</div>
					<div class="ult-directory-filter ult-directory-filter--categories">
						<div class="ult-form-element">
							<div class="ult-form-element__field">
								<select name="category" data-name="category" class="ult-form-element__select">
									<option value="">
										<?php _e( 'All categories', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="learndash">
										<?php _e( 'LearnDash', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="wordpress">
										<?php _e( 'General WordPress', 'uncanny-learndash-toolkit' ) ?>
									</option>
								</select>
							</div>
						</div>
					</div>
					<div class="ult-directory-filter ult-directory-filter--statuses">
						<div class="ult-form-element">
							<div class="ult-form-element__field">
								<select name="status" data-name="status" class="ult-form-element__select">
									<option value="">
										<?php _e( 'All statuses', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="active">
										<?php _e( 'Active', 'uncanny-learndash-toolkit' ) ?>
									</option>
									<option value="inactive">
										<?php _e( 'Inactive', 'uncanny-learndash-toolkit' ) ?>
									</option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="ult-directory-search">
					<div class="ult-form-element">
						<div class="ult-form-element__field">
							<input type="text" id="ult-directory-search-input"
								   class="ult-directory-search-input ult-form-element__text"
								   placeholder="<?php _e( 'Search modules by title, description or keywords', 'uncanny-learndash-toolkit' ) ?>">
						</div>
					</div>
				</div>

				<div class="ult-directory-layout">
					<div id="ult-directory-layout-toggle">
						<div class="ult-directory-layout-item" data-view="grid">
							<span class="ult-icon ult-icon--th"></span>
						</div>
						<div class="ult-directory-layout-item" data-view="table">
							<span class="ult-icon ult-icon--th-list"></span>
						</div>
					</div>
				</div>

			</div>

			<?php do_action( 'ult_before_directory_modules', $modules ); ?>

			<div class="ult-directory-modules">

				<?php
				foreach ( $modules as $module ) {

					$css_classes = [];

					if ( ! isset( $module['can_use'] ) || ! $module['can_use'] ) {
						$css_classes[] = 'ult-directory-module--cant-use';
					}

					if ( isset( $module['version'] ) && $module['version'] ) {
						$css_classes[] = 'ult-directory-module--is-pro';
					}

					?>

					<div class="ult-directory-module <?php echo implode( ' ', $css_classes ); ?>"
						 data-id="<?php echo isset( $module['id'] ) ? $module['id'] : md5( time() ); ?>"
						 data-version="<?php echo isset( $module['version'] ) ? $module['version'] : 'free'; ?>"
						 data-status="<?php echo isset( $module['is_active'] ) && $module['is_active'] ? 'active' : 'inactive'; ?>"
						 data-category='<?php echo isset( $module['category'] ) ? json_encode( $module['category'] ) : json_encode( array() ); ?>'
					>

						<div class="ult-directory-module-content">
							<?php if ( ! empty( $module['cant_use_notice'] ) ) { ?>

								<div class="ult-directory-module-notice">
									<div class="ult-directory-module-notice__icon">
										<span class="ult-icon ult-icon--lock-alt"></span>
									</div>
									<div class="ult-directory-module-notice__text">
										<?php echo $module['cant_use_notice']; ?>
									</div>
								</div>

							<?php } ?>

							<div class="ult-directory-module-header">
								<div class="ult-directory-module-header-left">
									<div class="ult-directory-module__title">
										<?php echo $module['title']; ?>
									</div>

									<?php if ( isset( $module['version'] ) && 'pro' === $module['version'] ) { ?>

										<?php

										// Get the link
										// Check if the module ID is defined
										if ( ! empty( $module['utm_id'] ) ) {
											$get_toolkit_pro_link = Config::utm_parameters( $config['get_toolkit_pro'], 'modules', 'pro_badge_on_title-' . $module['utm_id'] );
										} else {
											$get_toolkit_pro_link = Config::utm_parameters( $config['get_toolkit_pro'], 'modules', 'pro_badge_on_title' );
										}

										?>

										<a href="<?php echo $get_toolkit_pro_link; ?>" target="_blank"
										   class="ult-directory-module__pro-label">
											<?php _e( 'Pro', 'uncanny-learndash-toolkit' ); ?>
										</a>

									<?php } ?>
								</div>

								<?php if ( isset( $module['can_use'] ) && $module['can_use'] ) { ?>

									<div class="ult-directory-module__status">

										<div class="ult-form-element">
											<div
												class="ult-form-checkbox ult-form-checkbox--toggle ult-form-checkbox--toggle-gutenberg">
												<label class="ult-form-checkbox__container">
													<input type="checkbox"
														   class="ult-directory-module__status-toggle ult-checkbox--hidden ult-checkbox--primary"
														   value="<?php echo $module['class_name']; ?>"
														<?php echo $module['is_active'] ? 'checked="checked"' : ''; ?>
													/>
													<div class="ult-checkbox--show"></div>
												</label>
											</div>
										</div>
									</div>

								<?php } ?>
							</div>

							<div class="ult-directory-module__description">
								<?php echo $module['description']; ?>
							</div>

							<div class="ult-directory-module-actions">

								<?php if ( isset( $module['can_use'] ) && $module['can_use'] ) { ?>

									<?php if ( false !== $module['has_settings'] ) { ?>

										<div
											class="ult-directory-module-settings ult-directory-module-settings--modal ult-directory-module__btn ult-btn ult-btn--primary"
											data-settings="<?php echo $module['settings_id']; ?>">
											<?php _e( 'Settings', 'uncanny-learndash-toolkit' ); ?>
										</div>

									<?php } ?>

								<?php }

								// Check if it has a KB article
								if ( ! empty( $module['kb_link'] ) ) {

									// Get the link
									// Check if the module ID is defined
									if ( ! empty( $module['utm_id'] ) ) {
										$module_kb_link = Config::utm_parameters( $module['kb_link'], 'modules', 'learn_more-' . $module['utm_id'] );
									} else {
										$module_kb_link = Config::utm_parameters( $module['kb_link'], 'modules', 'learn_more' );
									}

									// Add the link
									?>

									<a href="<?php echo $module_kb_link; ?>" target="_blank"
									   class="ult-directory-module-settings ult-directory-module-settings--kb-link ult-directory-module__btn ult-btn ult-btn--secondary">
										<?php _e( 'Learn More', 'uncanny-learndash-toolkit' ); ?>
									</a>

								<?php } ?>

							</div>
						</div>

					</div>

				<?php } ?>

			</div>

			<?php do_action( 'ult_after_directory_modules', $modules ); ?>

		</div>

	</div>
