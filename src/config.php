<?php

namespace uncanny_learndash_toolkit;


class Config {
	/**
	 * @var string
	 */
	private static $version;
	/**
	 * @var string
	 */
	private static $file;
	/**
	 * @var string
	 */
	private static $basename;
	/**
	 * @var string
	 */
	private static $project_name;
	/**
	 * @var string
	 */
	private static $plugin_dir;
	/**
	 * @var string
	 */
	private static $plugin_url;

	/**
	 * @var string
	 */
	private static $css_prefix;
	/**
	 * @var array
	 */
	private static $available_plugins;
	/**
	 * @var bool
	 */
	private static $caching_on = false;

	/**
	 * @return boolean
	 */
	public static function is_caching_on() {
		return self::$caching_on;
	}

	/**
	 * @param $class_names
	 *
	 * @return array
	 */
	public static function set_available_classes( $class_names ) {
		self::$available_plugins = $class_names;
	}

	/**
	 * @return array of class names
	 */
	public static function get_active_classes() {
		if ( ! self::$available_plugins ) {
			self::$available_plugins = get_option( 'uncanny_toolkit_active_classes', array() );
			if ( empty( self::$available_plugins ) ) {
				self::$available_plugins = array();
			}
		}

		return self::$available_plugins;
	}

	/**
	 * @return mixed
	 */
	public static function get_basename() {
		if ( null === self::$basename ) {
			self::$basename = plugin_basename( self::$file );
		}

		return self::$basename;
	}

	/**
	 * @return string
	 */
	public static function get_file() {
		if ( null === self::$file ) {
			self::$file = __FILE__;
		}

		return self::$file;
	}

	/**
	 * @return string
	 */
	public static function get_plugin_dir() {
		if ( null === self::$plugin_dir ) {
			self::$plugin_dir = plugin_dir_path( self::$file );
		}

		return self::$plugin_dir;
	}

	/**
	 * @return string
	 */
	public static function get_plugin_url() {
		if ( null === self::$plugin_url ) {
			self::$plugin_url = plugin_dir_url( self::$file );
		}

		return self::$plugin_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_media( $file_name ) {
		$asset_url = plugins_url( 'assets/backend/img/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_css( $file_name ) {
		$asset_url = plugins_url( 'assets/backend/css/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_js( $file_name ) {
		$asset_url = plugins_url( 'assets/backend/js/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_media( $file_name ) {
		$asset_url = plugins_url( 'assets/site/media/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_css( $file_name ) {
		$asset_url = plugins_url( 'assets/site/css/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_site_js( $file_name ) {
		$asset_url = plugins_url( 'assets/site/js/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_vendor( $file_name ) {
		$asset_url = plugins_url( 'assets/vendor/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_template( $file_name, $file = false ) {

		if ( false === $file ) {
			$file = __FILE__;
		}

		$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/**
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_include( $file_name, $file = false ) {

		if ( false === $file ) {
			$file = __FILE__;
		}

		$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/**
	 * @return string
	 */
	public static function get_project_name() {
		if ( null === self::$project_name ) {
			self::$project_name = 'uncanny_learndash_toolkit';
		}

		return self::$project_name;
	}

	/**
	 * @param $project_name
	 */
	public static function set_project_name( $project_name ) {
		self::$project_name = $project_name;
	}

	/**
	 * @return string
	 */
	public static function get_prefix() {
		return self::get_project_name() . '_';
	}

	/**
	 * @return string
	 */
	public static function get_css_prefix() {
		if ( null === self::$css_prefix ) {
			self::$css_prefix = str_replace( '_', '-', self::get_prefix() );
		}

		return self::$css_prefix;
	}

	/**
	 * @return string
	 */
	public static function _get_prefix() {
		return '_' . self::get_prefix();
	}

	/**
	 * @return string
	 */
	public static function get_namespace() {
		return self::get_project_name();
	}

	/**
	 * @return string
	 */
	public static function get_date_formant() {
		return 'y/m/d g:i';
	}

	/**
	 * @return string
	 */
	public static function get_version() {
		if ( null === self::$version ) {
			self::$version = '1.3.7';
		}

		return self::$version;
	}

	/**
	 * @param array $array Array where there is slashes in the key
	 *
	 * @return array
	 */
	public static function stripslashes_deep( $array ) {
		$new_array = array();

		// strip slashes of all keys in array
		foreach ( $array as $key => $content ) {
			$key               = stripslashes( $key );
			$new_array[ $key ] = $content;
		}

		return $new_array;
	}

	/**
	 * Loops through array of setting values and return an link and settings html
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function settings_output( $settings ) {

		$class   = $settings['class'];// define by __CLASS__ from related php file
		$title   = $settings['title'];
		$options = $settings['options'];

		$modal_id = stripslashes( $class );
		$modal_id = str_replace( __NAMESPACE__, '', $modal_id );

		ob_start();

		?>

		<div class="ult-modal" data-settings="<?php echo $modal_id; ?>">
			<div class="ult-modal-box">
				<div class="ult-modal__header">
					<div class="ult-modal-title">
						<div class="ult-modal-title__icon"></div>
						<div class="ult-modal-title__text">
							<?php echo $title; ?>
						</div>
					</div>
				</div>
				<form method="POST" class="ult-modal-form ult-modal-form-js">
					<div class="ult-modal-options">
						<?php

						// Create options
						foreach ( $options as $content ) {

							switch ( $content['type'] ) {

								case 'html':

									?>

									<div class="ult-modal-form-row ult-modal-form-row--html <?php echo $content['class']; ?>">
										<?php echo $content['inner_html']; ?>
									</div>

									<?php

									break;

								case 'text':

									?>

									<div class="ult-modal-form-row ult-modal__field--text">
										<div class="ult-modal-form-row__label">
											<?php echo $content['label']; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<input type="text" placeholder="<?php echo $content['placeholder']; ?>" class="ult-modal-form-row__input <?php echo $content['class'] ?>" name="<?php echo $content['option_name']; ?>" data-type="text">
										</div>
									</div>

									<?php

									break;

								case 'color':

									?>

									<div class="ult-modal-form-row ult-modal__field--color">
										<div class="ult-modal-form-row__label">
											<?php echo $content['label']; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<input type="color" placeholder="<?php echo $content['placeholder']; ?>" class="ult-modal-form-row__color" name="<?php echo $content['option_name']; ?>" data-type="color">
										</div>
									</div>

									<?php

									break;

								case 'textarea':
									//Fallback method for old toolkit
									if ( version_compare( UNCANNY_TOOLKIT_VERSION, '2.4' ) >= 0 ) {
										// TinyMCE.

										$tinymce_content = self::get_settings_value( $tinymce_content['option_name'], $class );

										if ( empty( $content ) ) {
											$tinymce_content = $tinymce_content['placeholder'];
										}

										$tinymce_content = stripslashes( $tinymce_content );

										?>

										<div class="ult-modal-form-row ult-modal__field--tinymce">
											<div class="ult-modal-form-row__label">
												<?php echo $content['label']; ?>
											</div>
											<div class="ult-modal-form-row__field">
												<?php

												echo wp_editor(
													$tinymce_content,
													$content['option_name'],
													[
														'editor_class'  => 'ult-tinymce',
														'media_buttons' => false,
														'editor_height' => 275,
													] );

												?>
											</div>
										</div>

										<?php
									} else {
										?>

										<div class="ult-modal-form-row ult-modal__field--textarea">
											<div class="ult-modal-form-row__label">
												<?php echo $content['label']; ?>
											</div>
											<div class="ult-modal-form-row__field">
												<textarea class="ult-modal-form-row__textarea <?php echo $content['class']; ?>" name="<?php echo $content['option_name']; ?>" placeholder="<?php echo $content['placeholder']; ?>" type="textarea"></textarea>
											</div>
										</div>

										<?php
									}

									break;

								case 'checkbox':

									?>

									<div class="ult-modal-form-row ult-modal__field--checkbox">
										<div class="ult-modal-form-row__field">
											<label>
												<input type="checkbox" name="<?php echo $content['option_name']; ?>" class="ult-modal-form-row__checkbox" data-type="checkbox">
												<?php echo $content['label']; ?>
											</label>
										</div>
									</div>

									<?php

									break;

								case 'radio';

									?>

									<div class="ult-modal-form-row ult-modal__field--radio">
										<div class="ult-modal-form-row__label">
											<?php echo $content['label']; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<?php

											/**
											 * This type of field has an exception. We're expecting more than one input radio
											 */

											foreach ( $content['radios'] as $radio ) {
												?>

												<input type="radio" name="<?php echo $content['radio_name']; ?>" value="<?php echo $radio['value']; ?>" data-type="radio"> <?php echo $radio['text']; ?>

												<?php
											}

											?>
										</div>
									</div>

									<?php

									break;

								case 'select':

									?>

									<div class="ult-modal-form-row ult-modal__field--select">
										<div class="ult-modal-form-row__label">
											<?php echo $content['label']; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<select class="ult-modal-form-row__select" name="<?php echo $content['select_name']; ?>" data-type="select">
												<?php

												foreach ( $content['options'] as $option ) {
													?>

													<option value="<?php echo $option['value']; ?>">
														<?php echo $option['text']; ?>
													</option>

													<?php
												}

												?>
											</select>

										</div>
									</div>

									<?php

									break;
							}
						}

						?>
					</div>
					<div class="ult-modal-footer">
						<div class="ult-modal-notice"></div>
						<div class="ult-modal-actions">
							<div class="ult-modal-action">
								<div class="ult-modal-action__btn ult-modal-action__btn--secondary ult-modal-action__btn-cancel-js" data-action="cancel">
									<?php _e( 'Close', 'uncanny-learndash-toolkit' ); ?>
								</div>
							</div>
							<div class="ult-modal-action">
								<button class="ult-modal-action__btn ult-modal-action__btn--primary ult-modal-action__btn-submit-js" type="submit">
									<?php _e( 'Save module', 'uncanny-learndash-toolkit' ); ?>
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>


		<?php

		$html_options = ob_get_clean();

		return array( 'link' => $modal_link, 'modal' => $html_options );

	}

	public static function ajax_activate_deactivate_module() {

		/*
	   * If WP DEBUG is not on do NOT return any php warning, notices, and/or fatal errors.
	   * Well If it is a fatal error then this return is FUBAR anyway...
	   * We do this because some badly configured servers will return notices and warnings switch get prepended or appended to the rest response.
	   */
		if ( defined( 'WP_DEBUG' ) ) {
			if ( false === WP_DEBUG ) {
				error_reporting( 0 );
			}
		}

		$capability = apply_filters( 'toolkit_settings_module_switch_cap', 'manage_options' );

		if ( current_user_can( $capability ) ) {
			if ( isset( $_POST['value'] ) ) {
				$value          = stripslashes( $_POST['value'] );
				$active_classes = get_option( 'uncanny_toolkit_active_classes', 0 );

				if ( 0 !== $active_classes ) {
					if ( ! is_array( $active_classes ) ) {
						$active_classes = array();
					}
					if ( 'active' === $_POST['active'] ) {
						$new_classes = array_merge( array( $value => $value ), $active_classes );
					} elseif ( 'inactive' === $_POST['active'] ) {
						unset( $active_classes[ $value ] );
						$new_classes = $active_classes;
					}
					update_option( 'uncanny_toolkit_active_classes', $new_classes );
					$response = 'success';
				} else {
					$save_settings = add_option( 'uncanny_toolkit_active_classes', array( $value => $value ), 'no' );
					$response      = ( $save_settings ) ? 'success' : 'notsaved';
				}

				//echo json_encode([$new_classes, $value, $_POST ]);
				echo $response;
				wp_die();
			}
		}
	}

	/*
	 * @return string
	 */
	public static function ajax_settings_save() {


		/*
	   * If WP DEBUG is not on do NOT return any php warning, notices, and/or fatal errors.
	   * Well If it is a fatal error then this return is FUBAR anyway...
	   * We do this because some badly configured servers will return notices and warnings switch get prepended or appended to the rest response.
	   */
		if ( defined( 'WP_DEBUG' ) ) {
			if ( false === WP_DEBUG ) {
				error_reporting( 0 );
			}
		}

		$response = [
			'error'   => true,
			'message' => ''
		];

		$capability = apply_filters( 'toolkit_settings_save_cap', 'manage_options' );

		if ( current_user_can( $capability ) ) {

			if ( isset( $_POST['class'] ) ) {

				$class   = $_POST['class'];
				$options = ( isset( $_POST['options'] ) ) ? $_POST['options'] : array();

				// Delete option and add option are called instead of update option because
				// sometimes update value is equal to the existing value and a false
				// positive is returned

				delete_option( $class );

				$save_settings = add_option( $class, $options, 'no' );

				$response['error'] = ! $save_settings;

				if ( $save_settings ) {
					$response['message'] = __( 'Settings saved successfully', 'uncanny-learndash-toolkit' );
				} else {
					$response['message'] = __( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' );
				}

			} else {
				$response['message'] = __( 'Class for addon is not set', 'uncanny-learndash-toolkit' );
			}
		} else {
			$response['message'] = __( 'You must be an admin to save settings', 'uncanny-learndash-toolkit' );
		}

		echo json_encode( $response );

		wp_die();

	}

	/*
	 * @return string
	 */
	public static function ajax_settings_load() {

		/*
		* If WP DEBUG is not on do NOT return any php warning, notices, and/or fatal errors.
		* Well If it is a fatal error then this return is FUBAR anyway...
		* We do this because some badly configured servers will return notices and warnings switch get prepended or appended to the rest response.
		*/
		if ( defined( 'WP_DEBUG' ) ) {
			if ( false === WP_DEBUG ) {
				error_reporting( 0 );
			}
		}

		$capability = apply_filters( 'toolkit_settings_load_cap', 'manage_options' );

		if ( current_user_can( $capability ) ) {

			if ( isset( $_POST['class'] ) ) {

				$class = $_POST['class'];

				$settings = get_option( $class, array() );

				foreach($settings as &$setting ){
					$setting['value'] = stripslashes($setting['value']);
				}

				$response = wp_json_encode( $settings );

			} else {
				$response = 'Class for addon is not set.';
			}
		} else {

			$response = 'You must be an admin to save settings.';

		}

		echo $response;

		wp_die();

	}

	/**
	 * @param $key
	 * @param $class
	 * @param $default
	 *
	 * @return string
	 */
	public static function get_settings_value( $key, $class, $default = '', $class_settings = [] ) {

		// get module settings key
		$class = str_replace( __NAMESPACE__, '', stripslashes( $class ) );

		// Get all module settings
		$options = get_option( $class, '' );

		// set default settings if placeholder is to be used as default
		if( '%placeholder%' === $default ){
			// fallback
			//$default = '';
			foreach( $class_settings as $setting ){
				if( isset($setting['option_name']) && $key === $setting['option_name']){
					if( isset( $setting['placeholder'] )){
						$default = $setting['placeholder'];
					}
				}
			}
		}

		// Check if setting key has an associated class
		if ( ! empty( $options ) && '' !== $options ) {
			foreach ( $options as $option ) {
				if ( in_array( $key, $option, true ) ) {
					if ( '' !== $default && '' === trim( $option['value'] ) ) {
						return $default;
					}

					return stripslashes( $option['value'] );
				}
			}
		}

		return $default;
	}
/*
	public static function removeslashes( $string ) {
		$string = implode( "", explode( "\\", $string ) );

		return stripslashes( trim( $string ) );
	}*/


	/**
	 * @param string $trace
	 * @param string $trace_name
	 * @param string $file_name
	 */
	public static function trace_logs( $trace = '', $trace_name = '', $file_name = 'logs' ) {
		$timestamp   = date( 'F d, Y H:i:s' );
		$boundary    = "\n===========================<<<< {$timestamp} >>>>===========================\n";
		$log_type    = "*******************************[[[[[[[[[[ {$trace_name} ]]]]]]]]]]*******************************\n";
		$log_end     = "\n===========================<<<< TRACE END >>>>===========================\n\n";
		$final_trace = print_r( $trace, true );
		$file        = WP_CONTENT_DIR . '/uo-' . $file_name . '.log';
		error_log( $boundary . $log_type . $final_trace . $log_end, 3, $file );
	}


	/**
	 * @return bool
	 */
	public static function is_pro_active() {
		if ( in_array( 'uncanny-toolkit-pro/uncanny-toolkit-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;
		}
	}
}