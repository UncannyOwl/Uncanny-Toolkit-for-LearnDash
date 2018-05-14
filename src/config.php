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
		$asset_url = plugins_url( 'assets/admin/media/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_css( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/css/' . $file_name, __FILE__ );

		return $asset_url;
	}

	/**
	 * @param string $file_name
	 *
	 * @return string
	 */
	public static function get_admin_js( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/js/' . $file_name, __FILE__ );

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

		//create unique clean html id from class name
		$modal_id = stripslashes( $class );
		$modal_id = str_replace( __NAMESPACE__, '', $modal_id );

		$modal_link = '<a class="uo_settings_link" rel="leanModal" href="#' . $modal_id . '"><span class="dashicons dashicons-admin-generic"></span></a>';

		ob_start();

		//Wrapper Start - open div.uo_setting, open div.uo_settings_options
		?>

		<div id="<?php echo $modal_id; ?>" class="uo_settings">

			<div class="uo_settings_header">
				<!--<h2>Settings: <?php /*echo $title; */ ?></h2>-->
				<h2><?php echo $title; ?></h2>
			</div>

			<div class="sk-folding-cube">
				<div class="sk-cube1 sk-cube"></div>
				<div class="sk-cube2 sk-cube"></div>
				<div class="sk-cube4 sk-cube"></div>
				<div class="sk-cube3 sk-cube"></div>
			</div>

			<div class="uo_settings_options">

				<?php

				// Create options
				foreach ( $options as $content ) {
					switch ( $content['type'] ) {

						case 'html':
							echo '<div class="uo_settings_single ' . $content['class'] . '">' . $content['inner_html'] . '</div>';
							break;

						case 'text':
							echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input"><input placeholder="' . $content['placeholder'] . '" class="uo_settings_form_field ' . $content['class'] . '" name="' . $content['option_name'] . '" type="text" /></div></div>';
							break;

						case 'color':
							echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input"><input class="uo_settings_form_field" name="' . $content['option_name'] . '" type="color" /></div></div>';
							break;

						case 'textarea':
							//Fallback method for old toolkit
							if ( version_compare( UNCANNY_TOOLKIT_VERSION, '2.4' ) >= 0 ) {
								$settings        = array(
									'media_buttons' => false,
									'editor_height' => 275,
								);
								$initial_content = self::get_settings_value( $content['option_name'], $class );
								if ( empty( $initial_content ) ) {
									$initial_content = $content['placeholder'];
								}
								$initial_content = stripslashes( $initial_content );
								echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input">';
								wp_editor( $initial_content, $content['option_name'], $settings );
								echo '</div></div>';
							} else {
								echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input"><textarea rows="7" cols="65" class="uo_settings_form_field ' . $content['class'] . '" name="' . $content['option_name'] . '" id="' . $content['option_name'] . '" placeholder="' . $content['placeholder'] . '" ></textarea></div></div>';
							}
							break;

						case 'checkbox':
							echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input"><input class="uo_settings_form_field" name="' . $content['option_name'] . '" type="checkbox" /></div></div>';
							break;

						case 'radio';

							$inputs = '';
							foreach ( $content['radios'] as $radio ) {
								$inputs .= '<input class="uo_settings_form_field" type="radio" name="' . $content['radio_name'] . '" value="' . $radio['value'] . '">' . $radio['text'] . ' ';
							}
							echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div><div class="uo_settings_input">' . $inputs . '</div></div>';
							break;

						case 'select':
							$options = '';
							foreach ( $content['options'] as $option ) {
								$options .= '<option value="' . $option['value'] . '"> ' . $option['text'] . '</option>';
							}
							echo '<div class="uo_settings_single"><div class="uo_settings_label">' . $content['label'] . '</div>
								<div class="uo_settings_input"><select class="uo_settings_form_field" name="' . $content['select_name'] . '" >' . $options . '</select></div>
							</div>';
							break;

					}
				}

				//Wrapper End - create button, close div.uo_setting, close div.uo_settings_options
				?>
				<button class="uo_save_settings">Save Settings</button>

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
					if ( 1 === intval( $_POST['active'] ) ) {
						$new_classes = array_merge( array( $value => $value ), $active_classes );
					} elseif ( 0 === intval( $_POST['active'] ) ) {
						unset( $active_classes[ $value ] );
						$new_classes = $active_classes;
					}
					update_option( 'uncanny_toolkit_active_classes', $new_classes );
					$response = 'success';
				} else {
					$save_settings = add_option( 'uncanny_toolkit_active_classes', array( $value => $value ), 'no' );
					$response      = ( $save_settings ) ? 'success' : 'notsaved';
				}

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

		$capability = apply_filters( 'toolkit_settings_save_cap', 'manage_options' );

		if ( current_user_can( $capability ) ) {

			if ( isset( $_POST['class'] ) ) {

				$class   = $_POST['class'];
				$options = ( isset( $_POST['options'] ) ) ? $_POST['options'] : array();

				// Delete option and add option are called instead of update option because
				// sometimes update value is equal to the existing value and a false
				// positive is returned

				delete_option( $class );
				//self::trace_logs( $options, 'options', 'save' );
				$save_settings = add_option( $class, $options, 'no' );

				$response = ( $save_settings ) ? 'success' : 'notsaved';

			} else {
				$response = 'Class for addon is not set.';
			}
		} else {

			$response = 'You must be an admin to save settings.';

		}

		echo stripslashes( $response );

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

				$response = wp_json_encode( $settings );

			} else {
				$response = 'Class for addon is not set.';
			}
		} else {

			$response = 'You must be an admin to save settings.';

		}

		//echo stripslashes( $response );
		echo $response;

		wp_die();

	}

	/**
	 * @param $key
	 * @param $class
	 *
	 * @return string
	 */
	public static function get_settings_value( $key, $class ) {

		$class   = str_replace( __NAMESPACE__, '', stripslashes( $class ) );
		$options = get_option( $class, '' );

		if ( ! empty( $options ) && '' !== $options ) {
			foreach ( $options as $option ) {
				if ( in_array( $key, $option, true ) ) {
					return $option['value'];
				}
			}
		}

		return '';
	}


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