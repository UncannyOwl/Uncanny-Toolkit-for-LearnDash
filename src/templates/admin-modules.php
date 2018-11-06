<?php
namespace uncanny_learndash_toolkit;

function make_readable_list( $items, $last_word ) {
	$count  = count( $items );
	$output = '';

	if ( ! $count === 0 ) {
		if ( $count === 1 ) {
			$output = $items[0];
		} else {
			$output = implode( ', ', array_slice( $items, 0, - 1 ) ) . ', ' . $last_word . ' ' . end( $items );
		}
	}

	return $output;
}

$dependencies = [
	'gravity-forms' => [
		'title'        => 'Gravity Forms',
		'get_url'      => '#',
		'is_installed' => false,
	],
	'learndash'     => [
		'title'        => 'LearnDash',
		'get_url'      => '#',
		'is_installed' => true,
	],
	'divi'          => [
		'title'        => 'Divi',
		'get_url'      => '#',
		'is_installed' => true,
	],
];

/**
 * A variable with some data, for now it has:
 * - has_toolkit_pro:    Boolean to determinate if the user has the Pro version installed.
 *
 * @var Array
 */
$config = [
	'has_toolkit_pro' => ( defined( 'UNCANNY_TOOLKIT_PRO_VERSION' ) ) ? true : false,
	'get_toolkit_pro' => 'https://www.uncannyowl.com/downloads/uncanny-learndash-toolkit-pro/',
	'pro_name'        => __( 'Uncanny LearnDash Toolkit Pro', 'uncanny-learndash-toolkit' ),
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

$modules = [
	[
		'title'       => 'Autocomplete Lessons & Topics',
		'description' => 'Automatically mark all lessons and topics as completed on user visit and remove Mark Complete buttons. Global settings can be overridden for individual lessons and topics.',
		'keywords'    => 'pro, learndash, topics, lessons, mark as complete',

		'is_active'    => rand( 0, 1 ) == 1, // Random boolean for debugging
		'version'       => false,
		'categories'   => [ 'learndash' ],
		'dependencies' => [ 'learndash' ],

		'documentation' => 'https://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics/',
		'settings_id'   => 'uncanny_pro_toolkitLessonTopicAutoComplete',
	],
	[
		'title'       => 'Autocomplete Lessons & Topics on Gravity Form Submission',
		'description' => 'Automatically mark LearnDash lessons and topics as completed when the user submits Gravity Forms.',
		'keywords'    => 'pro, learndash, topics, lessons, mark as complete, gravity forms, gf',

		'is_active'    => rand( 0, 1 ) == 1, // Random boolean for debugging
		'version'       => true,
		'categories'   => [ 'learndash', 'gravity-forms' ],
		'dependencies' => [ 'learndash', 'gravity-forms' ],

		'documentation' => 'http://www.uncannyowl.com/knowledge-base/gravity-forms-auto-completes-lessons-topics/',
		'settings_id'   => 'uncanny_pro_toolkitGfLessonTopicAutoComplete',
	],
	[
		'title'       => 'Autocomplete Lessons & Topics on Quiz Results Page',
		'description' => 'Automatically mark LearnDash lessons and topics as completed when the user reaches the quiz results page with a passing mark.',
		'keywords'    => 'pro, learndash, topics, lessons, mark as complete, quizzes',

		'is_active'    => rand( 0, 1 ) == 1, // Random boolean for debugging
		'version'       => true,
		'categories'   => [ 'learndash' ],
		'dependencies' => [ 'learndash' ],

		'documentation' => 'http://www.uncannyowl.com/knowledge-base/autocomplete-lessons-topics-on-quiz-completion/',
		'settings_id'   => '',
	],
	[
		'title'       => 'Days Until Course Expiry',
		'description' => 'Use this shortcode to display the number of days until the learner\'s access expires for the current course. This is a useful shortcode to include on course pages.',
		'keywords'    => 'pro, learndash, course expiration, due date',

		'is_active'    => rand( 0, 1 ) == 1, // Random boolean for debugging
		'version'       => true,
		'categories'   => [ 'learndash' ],
		'dependencies' => [ 'learndash' ],

		'documentation' => 'https://www.uncannyowl.com/knowledge-base/days-until-course-expiry/',
		'settings_id'   => '',
	],
];

/**
 * Add autoincrement ID
 */
foreach ( $modules as $key => $module ) {
	$modules[ $key ]['id'] = $key;
}

/**
 * Determinate if each module can be used
 * we're going to check if the user has pro enabled and if it has the required dependencies
 */
foreach ( $modules as $key => $module ) {

	// Define default value
	$modules[ $key ]['can_use']              = true;
	$modules[ $key ]['cant_use_notice']      = '';
	$modules[ $key ]['missing_dependencies'] = [];

	// Create variable to saved required stuff
	// We're going to save links here.
	$required_pieces = [];

	// Check if it's a Pro module but the user doesn't have the Pro plugin
	if ( 'pro' === $module['version'] && ! $config['has_toolkit_pro'] ) {
		// Add element to the list of required pieces
		$required_pieces[] = sprintf( '<a href="%s">%s</a>', $config['get_toolkit_pro'], $config['pro_name'] );

		// Add mising dependency to the module
		$modules[ $key ]['missing_dependencies'][] = [
			'type'    => 'core',
			'title'   => $config['pro_name'],
			'get_url' => $config['get_toolkit_pro'],
		];

		// And make "can_use" false
		$modules[ $key ]['can_use'] = false;
	}

	// Check if it has dependencies
	if ( ! empty( $modules[ $key ]['dependencies'] ) ) {
		// Create variable to check if we have all the required dependencies
		$has_all_dependencies = true;

		// Iterate the module dependencies
		foreach ( $modules[ $key ]['dependencies'] as $dependency ) {
			// Check in the global variable if this dependency is installed
			if ( ! $dependencies[ $dependency ]['is_installed'] ) {
				// If not then add it to the required_pieces variable
				// to show a message later
				$required_pieces[] = sprintf( '<a href="%s" target="_blank">%s</a>', $dependencies[ $dependency ]['get_url'], $dependencies[ $dependency ]['title'] );

				// Add mising dependency to the module
				$modules[ $key ]['missing_dependencies'][] = array_merge( $dependencies[ $dependency ], [ 'type' => 'external' ] );

				// And make $has_all_dependencies false
				$has_all_dependencies = false;
			}
		}

		// If it doesn't have all the required dependencies then make "can_use" false
		if ( ! $has_all_dependencies ) {
			$modules[ $key ]['can_use'] = false;

			// If it can't be used then it can't be active
			$modules[ $key ]['is_active'] = false;
		}
	}

	// Create message using $required_pieces
	$modules[ $key ]['cant_use_notice'] = make_readable_list( $required_pieces, 'and' );
}
?>


<script>

    var ultModules = <?php echo json_encode( $modules ); ?>;

</script>

<div class="container">

    <div class="ult">

        <div class="ult-directory">

            <div class="ult-directory-actions">

                <div class="ult-directory-filters">
                    <div class="ult-directory-filter ult-directory-filter--version">
                        <div class="ult-form-element">
                            <div class="ult-form-element__field">
                                <select name="version" data-name="version" class="ult-form-element__select">
                                    <option value="">All Versions</option>
                                    <option value="free">Free only</option>
                                    <option value="pro">Pro only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ult-directory-filter ult-directory-filter--categories">
                        <div class="ult-form-element">
                            <div class="ult-form-element__field">
                                <select name="category" data-name="category" class="ult-form-element__select">
                                    <option value="">All Categories</option>
                                    <option value="learndash">LearnDash</option>
                                    <option value="wordpress">General WordPress</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ult-directory-filter ult-directory-filter--statuses">
                        <div class="ult-form-element">
                            <div class="ult-form-element__field">
                                <select name="status" data-name="status" class="ult-form-element__select">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
                                   placeholder="Search modules by title, description or keywords">
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

            <div class="ult-directory-modules">

				<?php
                foreach ( self::$modules as $module ) {

					$cssClasses = [];

					if ( ! $module['can_use'] ) {
						$cssClasses[] = 'ult-directory-module--cant-use';
					}

					if ( $module['version'] ) {
						$cssClasses[] = 'ult-directory-module--is-pro';
					}

					?>

                    <div class="ult-directory-module <?php echo implode( ' ', $cssClasses ); ?>"
                         data-id="<?php echo $module['id']; ?>"
                         data-version="<?php echo $module['version']; ?>"
                         data-status="<?php echo $module['is_active'] ? 'active' : 'inactive'; ?>"
                         data-category='<?php echo json_encode( $module['categories'] ); ?>'
                    >

                        <div class="ult-directory-module-content">
							<?php if ( ! empty( $module['cant_use_notice'] ) ) { ?>

                                <div class="ult-directory-module-notice">
                                    <span class="ult-icon ult-icon--lock-alt"></span> <?php printf( 'Requires %s', $module['cant_use_notice'] ); ?>
                                </div>

							<?php } ?>

                            <div class="ult-directory-module-header">
                                <div class="ult-directory-module-header-left">
                                    <div class="ult-directory-module__title">
										<?php echo $module['title']; ?>
                                    </div>

									<?php if ( 'pro' === $module['version'] ) { ?>

                                        <a href="<?php echo $config['get_toolkit_pro']; ?>" target="_blank"
                                           class="ult-directory-module__pro-label">
                                            Pro
                                        </a>

									<?php } ?>
                                </div>

								<?php if ( $module['can_use'] ) { ?>

                                    <div class="ult-directory-module__status">

                                        <div class="ult-form-element">
                                            <div class="ult-form-checkbox ult-form-checkbox--toggle ult-form-checkbox--toggle-gutenberg">
                                                <label class="ult-form-checkbox__container">
                                                    <input type="checkbox"
                                                           class="ult-directory-module__status-toggle ult-checkbox--hidden ult-checkbox--primary"
                                                           value="<?php echo $module['settings_id']; ?>"
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

								<?php if ( $module['can_use'] ) { ?>

									<?php if ( false !== $module['has_settings'] ) { ?>

                                        <div class="ult-directory-module-settings ult-directory-module__btn ult-btn ult-btn--primary"
                                             rel="leanModal"
                                             data-settings="<?php echo $module['settings_id']; ?>">
                                            Settings
                                        </div>

									<?php } ?>

								<?php } else { ?>

									<?php foreach ( $module['missing_dependencies'] as $dependency ) { ?>

                                        <a href="<?php echo $dependency['get_url']; ?>"
                                           class="ult-btn ult-btn--secondary ult-directory-module__btn <?php printf( 'ult-directory-module__btn--%s', $dependency['type'] ); ?>"
                                           target="_blank">
											<?php printf( 'Get %s', $dependency['title'] ); ?>
                                        </a>

									<?php } ?>

								<?php } ?>

                            </div>
                        </div>

                    </div>

				<?php } ?>

            </div>

        </div>

    </div>