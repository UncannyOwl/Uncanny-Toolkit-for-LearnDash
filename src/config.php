<?php

namespace uncanny_learndash_toolkit;

/**
 * Class Config
 *
 * @package uncanny_learndash_toolkit
 */
class Config {
	/**
	 * @var array
	 */
	private static $available_plugins;

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
	 * @return bool
	 */
	public static function is_pro_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( 'uncanny-toolkit-pro/uncanny-toolkit-pro.php' );
		}

		return defined( 'UNCANNY_TOOLKIT_PRO_VERSION' );
	}

	/**
	 * Check if the module is active in Free / Pro of Toolkit
	 *
	 * @param       $module
	 * @param false $in_pro
	 *
	 * @return bool
	 */
	public static function is_toolkit_module_active( $module, $in_pro = false ) {
		if ( $in_pro && ! self::is_pro_active() ) {
			return false;
		}

		$active_classes = self::get_active_classes();

		return in_array( $module, $active_classes, false );
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_media( $file_name ) {
		return plugins_url( 'assets/backend/img/' . $file_name, __FILE__ );
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_css( $file_name ) {
		return plugins_url( 'assets/backend/css/' . $file_name, __FILE__ );
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_js( $file_name ) {
		return plugins_url( 'assets/backend/js/' . $file_name, __FILE__ );
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_vendor( $file_name ) {
		return plugins_url( 'assets/vendor/' . $file_name, __FILE__ );
	}

	/**
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_template( $file_name, $file = false ) {

		if ( ! $file ) {
			$file = __FILE__;
		}
		$template_path = apply_filters( 'uncanny_toolkit_template_path', 'uncanny-toolkit' . DIRECTORY_SEPARATOR, $file_name );
		$asset_uri     = self::locate_template( $template_path . $file_name );

		if ( empty( $asset_uri ) ) {
			$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;
		}

		return $asset_uri;
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH and wp-includes/theme-compat
	 * so that themes which inherit from a parent theme can just overload one file.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 *
	 * @return string The template filename if one is located.
	 * @since 3.1
	 */
	public static function locate_template( $template_names ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			if ( file_exists( $template_name ) ) {
				$located = $template_name;
				break;
			}
			if ( file_exists( get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template_name ) ) {
				$located = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template_name;
				break;
			}

			if ( file_exists( get_template_directory() . DIRECTORY_SEPARATOR . $template_name ) ) {
				$located = get_template_directory() . DIRECTORY_SEPARATOR . $template_name;
				break;
			}

			if ( file_exists( ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'theme-compat' . DIRECTORY_SEPARATOR . $template_name ) ) {
				$located = ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'theme-compat' . DIRECTORY_SEPARATOR . $template_name;
				break;
			}
		}

		return $located;
	}

	/**
	 * @param string $file_name File name must be prefixed with a \ (foreword slash)
	 * @param mixed $file (false || __FILE__ )
	 *
	 * @return string
	 */
	public static function get_include( $file_name, $file = false ) {

		if ( ! $file ) {
			$file = __FILE__;
		}

		$asset_uri = dirname( $file ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $file_name;

		return $asset_uri;
	}

	/**
	 * @return string
	 */
	public static function get_namespace() {
		return __NAMESPACE__;
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

		add_filter(
			'tiny_mce_before_init',
			function ( $init ) {
				$init['extended_valid_elements'] = '*[*]';
				$init['remove_linebreaks']       = false;
				$init['convert_newlines_to_brs'] = true;
				$init['remove_redundant_brs']    = false;

				return $init;
			}
		);
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

							// Show if
							$show_if = isset( $content['show_if'] ) && is_array( $content['show_if'] ) ? $content['show_if'] : '';
							$show_if = is_array( $show_if ) ? esc_attr( wp_json_encode( $show_if ) ) : '';

							// Default value
							$default_value = isset( $content['default'] ) ? $content['default'] : '';

							// If it has a "show if" defined, hide it
							$css_class = isset( $content['class'] ) ? $content['class'] : '';
							if ( ! empty( $show_if ) ) {
								$css_class = $css_class . ' ult-modal-form-row--hide';
							}

							$placeholder     = isset( $content['placeholder'] ) ? $content['placeholder'] : '';
							$inner_html      = isset( $content['inner_html'] ) ? $content['inner_html'] : '';
							$option_name     = isset( $content['option_name'] ) ? $content['option_name'] : '';
							$label           = isset( $content['label'] ) ? $content['label'] : '';
							$description     = isset( $content['description'] ) ? $content['description'] : '';
							$select_name     = isset( $content['select_name'] ) ? $content['select_name'] : '';
							$content_options = isset( $content['options'] ) ? $content['options'] : array();
							$radio_name      = isset( $content['radio_name'] ) ? $content['radio_name'] : '';
							$radio_options   = isset( $content['radios'] ) ? $content['radios'] : array();

							switch ( $content['type'] ) {
								case 'html':
									?>
									<div
										class="ult-modal-form-row ult-modal-form-row--html <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-type="html"
									>
										<?php echo $inner_html; ?>
									</div>
									<?php
									break;
								case 'text':
									?>
									<div
										class="ult-modal-form-row ult-modal__field--text <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-id="<?php echo $option_name; ?>"
										data-type="text"
									>
										<div class="ult-modal-form-row__label">
											<?php echo $label; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<input type="text" placeholder="<?php echo $placeholder; ?>"
												   class="ult-modal-form-row__input"
												   name="<?php echo $option_name; ?>" data-type="text">

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
										</div>
									</div>

									<?php

									break;

								case 'number':
									?>
									<div
										class="ult-modal-form-row ult-modal__field--text <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-id="<?php echo $option_name; ?>"
										data-type="text"
									>
										<div class="ult-modal-form-row__label">
											<?php echo $label; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<input type="number" placeholder="<?php echo $placeholder; ?>"
												   class="ult-modal-form-row__input"
												   name="<?php echo $option_name; ?>" data-type="number" min="0">

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
										</div>
									</div>

									<?php

									break;

								case 'color':
									?>
									<div
										class="ult-modal-form-row ult-modal__field--color <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-type="color"
										data-id="<?php echo $option_name; ?>"
									>
										<div class="ult-modal-form-row__label">
											<?php echo $label; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<input type="color"
												   placeholder="<?php echo $placeholder; ?>"
												   class="ult-modal-form-row__color uo-color-picker"
												   name="<?php echo $option_name; ?>"
												   data-type="color"
											>

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
										</div>
									</div>
									<?php
									break;
								case 'textarea':
									//Fallback method for old toolkit
									if ( version_compare( UNCANNY_TOOLKIT_VERSION, '2.4' ) >= 0 ) {
										// TinyMCE.

										$tinymce_content = self::get_settings_value( $option_name, $class );

										if ( empty( $tinymce_content ) ) {
											$tinymce_content = $placeholder;
										}

										$tinymce_content = stripslashes( $tinymce_content );

										?>

										<div
											class="ult-modal-form-row ult-modal__field--tinymce <?php echo $css_class; ?>"
											data-show-if="<?php echo $show_if; ?>"
											data-default="<?php echo $default_value; ?>"
											data-type="textarea"
											data-id="<?php echo $option_name; ?>"
										>
											<div class="ult-modal-form-row__label">
												<?php echo $label; ?>
											</div>
											<div class="ult-modal-form-row__field">
												<?php

												echo wp_editor(
													$tinymce_content,
													$option_name,
													array(
														'editor_class'  => 'ult-tinymce',
														'media_buttons' => false,
														'editor_height' => 275,
													)
												);
												?>

												<?php if ( ! empty( $description ) ) { ?>
													<div class="ult-modal-form-row__description">
														<?php echo $description; ?>
													</div>
												<?php } ?>
											</div>
										</div>

										<?php
									} else {
										?>
										<div
											class="ult-modal-form-row ult-modal__field--textarea <?php echo $css_class; ?>"
											data-show-if="<?php echo $show_if; ?>"
											data-default="<?php echo $default_value; ?>"
											data-type="textarea"
											data-id="<?php echo $option_name; ?>"
										>
											<div class="ult-modal-form-row__label">
												<?php echo $label; ?>
											</div>
											<div class="ult-modal-form-row__field">
												<textarea
													class="ult-modal-form-row__textarea"
													name="<?php echo $option_name; ?>"
													placeholder="<?php echo $placeholder; ?>"
													type="textarea"></textarea>

												<?php if ( ! empty( $description ) ) { ?>
													<div class="ult-modal-form-row__description">
														<?php echo $description; ?>
													</div>
												<?php } ?>
											</div>
										</div>

										<?php
									}

									break;
								case 'checkbox':
									?>
									<div
										class="ult-modal-form-row ult-modal__field--checkbox <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-type="checkbox"
										data-id="<?php echo $option_name; ?>"
									>
										<div class="ult-modal-form-row__field">
											<label>
												<input type="checkbox" name="<?php echo $option_name; ?>"
													   class="ult-modal-form-row__checkbox" data-type="checkbox">
												<?php echo $label; ?>
											</label>

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
										</div>
									</div>

									<?php
									break;
								case 'radio';
									?>
									<div
										class="ult-modal-form-row ult-modal__field--radio <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-type="radio"
										data-id="<?php echo $radio_name; ?>"
									>
										<div class="ult-modal-form-row__label">
											<?php echo $label; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<?php

											/**
											 * This type of field has an exception. We're expecting more than one input radio
											 */
											if ( $radio_options ) {
												foreach ( $radio_options as $radio ) {
													?>

													<label class="ult-modal-form-row__radio-label">
														<input type="radio" name="<?php echo $radio_name; ?>"
															   value="<?php echo $radio['value']; ?>" data-type="radio">
														<span>
														<?php echo $radio['text']; ?>
													</span>
													</label>

													<?php
												}
											}

											?>

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
										</div>
									</div>

									<?php

									break;
								case 'select':
									?>
									<div
										class="ult-modal-form-row ult-modal__field--select <?php echo $css_class; ?>"
										data-show-if="<?php echo $show_if; ?>"
										data-default="<?php echo $default_value; ?>"
										data-type="select"
										data-id="<?php echo $select_name; ?>"
									>
										<div class="ult-modal-form-row__label">
											<?php echo $label; ?>
										</div>
										<div class="ult-modal-form-row__field">
											<select class="ult-modal-form-row__select"
													name="<?php echo $select_name; ?>" data-type="select">
												<?php
												if ( ! empty( $content_options ) ) {
													foreach ( $content_options as $option ) {
														?>
														<option value="<?php echo $option['value']; ?>">
															<?php echo $option['text']; ?>
														</option>
														<?php
													}
												}
												?>
											</select>

											<?php if ( ! empty( $description ) ) { ?>
												<div class="ult-modal-form-row__description">
													<?php echo $description; ?>
												</div>
											<?php } ?>
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
							<div class="ult-modal-actions__left">
								<div class="ult-modal-action">
									<a target="_blank"
									   class="ult-modal-action__btn ult-modal-action__btn--secondary ult-modal-action__btn-help-js">
										<?php _e( 'Help', 'uncanny-learndash-toolkit' ); ?>
									</a>
								</div>
							</div>
							<div class="ult-modal-actions__right">
								<div class="ult-modal-action">
									<div
										class="ult-modal-action__btn ult-modal-action__btn--secondary ult-modal-action__btn-cancel-js"
										data-action="cancel">
										<?php _e( 'Close', 'uncanny-learndash-toolkit' ); ?>
									</div>
								</div>
								<div class="ult-modal-action">
									<button
										class="ult-modal-action__btn ult-modal-action__btn--primary ult-modal-action__btn-submit-js"
										type="submit">
										<?php _e( 'Save module', 'uncanny-learndash-toolkit' ); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>


		<?php

		$html_options = ob_get_clean();

		return array(
			'link'  => '',
			'modal' => $html_options,
		);

	}

	/**
	 *
	 */
	public static function ajax_activate_deactivate_module() {
		// Nonce verification
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uncanny_toolkit' ) ) {
			echo 'nonce failed';
			wp_die();
		}
		$capability = apply_filters( 'toolkit_settings_module_switch_cap', 'manage_options' );
		if ( ! current_user_can( $capability ) ) {
			echo __( 'You must be an admin to save settings', 'uncanny-learndash-toolkit' );
			wp_die();
		}
		if ( ! isset( $_POST['value'] ) ) {
			echo 'Value field missing.';
			wp_die();
		}
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
			$save_settings = add_option( 'uncanny_toolkit_active_classes', array( $value => $value ) );
			$response      = ( $save_settings ) ? 'success' : 'notsaved';
		}

		// If the uo dashboard module is being turned on then set the default template as 3_0
		if ( 'uncanny_pro_toolkit\\learnDashMyCourses' === $value ) {
			if ( 'active' === $_POST['active'] ) {
				update_option(
					'uncanny_pro_toolkitlearnDashMyCourses',
					array(
						array(
							'name'  => 'uo_dashboard_template',
							'value' => '3_0',
						),
					),
					'no'
				);
			}
		}

		// If the frontend login module is being turned on then check if settings are available or not.
		if ( 'uncanny_learndash_toolkit\FrontendLoginPlus' === $value ) {
			if ( 'active' === $_POST['active'] ) {
				$existing_settings = get_option( 'FrontendLoginPlus', '' );
				if ( empty( $existing_settings ) ) {
					$default_settings = array(
						array(
							'name'  => 'uo_frontendloginplus_enable_ajax_support',
							'value' => 'on',
						),
					);
					update_option( 'FrontendLoginPlus', $default_settings );
				}
			}
		}

		echo $response; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
	}

	/**
	 *
	 */
	public static function ajax_settings_save() {
		// Nonce verification
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uncanny_toolkit' ) ) {
			echo wp_json_encode(
				array(
					'error'   => true,
					'message' => 'Nonce verification failed.',
				)
			);
			wp_die();
		}
		$response = array(
			'error'   => true,
			'message' => '',
		);

		$capability = apply_filters( 'toolkit_settings_save_cap', 'manage_options' );

		if ( ! current_user_can( $capability ) ) {
			echo wp_json_encode(
				array(
					'error'   => true,
					'message' => __( 'You must be an admin to save settings', 'uncanny-learndash-toolkit' ),
				)
			);
			wp_die();
		}

		if ( ! isset( $_POST['class'] ) ) {
			echo wp_json_encode(
				array(
					'error'   => true,
					'message' => __( 'Class for addon is not set', 'uncanny-learndash-toolkit' ),
				)
			);
			wp_die();
		}

		$class   = sanitize_text_field( $_POST['class'] );
		$options = ( isset( $_POST['options'] ) ) ? $_POST['options'] : array();

		// Validate action if any module need some values to set.
		do_action( 'toolkit_settings_save_validation', $class, $options );

		// Delete option and add option are called instead of update option because
		// sometimes update value is equal to the existing value and a false
		// positive is returned

		delete_option( $class );

		$save_settings = add_option( $class, $options );

		$response['error'] = ! $save_settings;

		if ( $save_settings ) {
			$response['message'] = __( 'Settings saved successfully', 'uncanny-learndash-toolkit' );
		} else {
			$response['message'] = __( 'Something went wrong. Please, try again', 'uncanny-learndash-toolkit' );
		}

		echo wp_json_encode( $response );

		wp_die();

	}


	/**
	 *
	 */
	public static function ajax_settings_load() {
		// Nonce verification
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uncanny_toolkit' ) ) {
			echo 'Nonce verification failed.';
			wp_die();
		}

		$capability = apply_filters( 'toolkit_settings_load_cap', 'manage_options' );
		if ( current_user_can( $capability ) ) {
			if ( isset( $_POST['class'] ) ) {
				$class    = sanitize_text_field( $_POST['class'] );
				$settings = get_option( $class, array() );
				foreach ( $settings as &$setting ) {
					$setting['value'] = stripslashes( $setting['value'] );
				}
				$response = wp_json_encode( $settings );
			} else {
				$response = __( 'Class for addon is not set.', 'uncanny-learndash-toolkit' );
			}
		} else {
			$response = __( 'You must be an admin to save settings.', 'uncanny-learndash-toolkit' );
		}
		echo $response;

		wp_die();

	}

	/**
	 * @param        $key
	 * @param        $class
	 * @param string $default
	 * @param array $class_settings
	 *
	 * @return string
	 */
	public static function get_settings_value( $key, $class, $default = '', $class_settings = array() ) {

		$args = array(
			'key'            => $key,
			'class'          => $class,
			'default'        => $default,
			'class_settings' => $class_settings,
		);

		// get module settings key
		$class = str_replace( __NAMESPACE__, '', stripslashes( $class ) );

		// Get all module settings
		$options = get_option( $class, '' );

		// set default settings if placeholder is to be used as default
		if ( '%placeholder%' === $default ) {
			// fallback
			//$default = '';
			foreach ( $class_settings as $setting ) {
				if ( isset( $setting['option_name'] ) && $key === $setting['option_name'] ) {
					if ( isset( $setting['placeholder'] ) ) {
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
						return apply_filters( 'uo_toolkit_settings_config_value', $default, $key, $args );
					}

					$value = stripslashes( $option['value'] );

					return apply_filters( 'uo_toolkit_settings_config_value', $value, $key, $args );

				}
			}
		}

		return apply_filters( 'uo_toolkit_settings_config_value', $default, $key, $args );

	}


	/**
	 * @param string $trace
	 * @param string $trace_name
	 * @param string $file_name
	 */
	public static function trace_logs( $trace = '', $trace_name = '', $file_name = 'logs' ) {
		$timestamp   = date( 'F d, Y H:i:s', current_time( 'timestamp' ) );
		$boundary    = "\n===========================<<<< {$timestamp} >>>>===========================\n";
		$log_type    = "*******************************[[[[[[[[[[ {$trace_name} ]]]]]]]]]]*******************************\n";
		$log_end     = "\n===========================<<<< TRACE END >>>>===========================\n\n";
		$final_trace = print_r( $trace, true );
		$file        = WP_CONTENT_DIR . '/uo-' . $file_name . '.log';
		error_log( $boundary . $log_type . $final_trace . $log_end, 3, $file ); // phpcs:ignore
	}

	/**
	 * Add UTM parameters to a given URL
	 *
	 * @param String $url URL
	 * @param Array $custom_utm_parameters Array with the UTM parameters
	 *
	 * @return String                        URL with the UTM parameters
	 */
	public static function utm_parameters( $url, $medium = '', $content = '' ) {
		// utm_source=plugin-id
		// utm_medium=section-id
		// utm_content=element-id+unique-id

		$default_utm_parameters = array(
			'source' => defined( 'UNCANNY_TOOLKIT_PRO_PATH' ) ? 'uncanny_toolkit_pro' : 'uncanny_toolkit',
		);

		try {
			// Parse the URL
			$url_parts = parse_url( $url );

			// If URL doesn't have a query string.
			if ( isset( $url_parts['query'] ) ) {
				// Avoid 'Undefined index: query'
				parse_str( $url_parts['query'], $params );
			} else {
				$params = array();
			}

			// Add default parameters
			foreach ( $default_utm_parameters as $default_utm_parameter_key => $default_utm_parameter_value ) {
				$params[ 'utm_' . $default_utm_parameter_key ] = $default_utm_parameter_value;
			}

			// Add custom parameters
			if ( ! empty( $medium ) ) {
				$params['utm_medium'] = $medium;
			}

			if ( ! empty( $content ) ) {
				$params['utm_content'] = $content;
			}

			// Encode parameters
			$url_parts['query'] = http_build_query( $params );

			if ( function_exists( 'http_build_url' ) ) {
				// If the user has pecl_http
				$url = http_build_url( $url_parts );
			} else {
				$url_parts['path'] = ! empty( $url_parts['path'] ) ? $url_parts['path'] : '';

				$url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];
			}
		} catch ( Exception $e ) {
		}

		return $url;
	}
}
