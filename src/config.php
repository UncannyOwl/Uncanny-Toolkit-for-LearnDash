<?php

namespace uncanny_learndash_public;


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
	public static function is_caching_on()
	{
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
	public static function get_available_classes() {
		if ( ! self::$available_plugins ) {
			self::$available_plugins = get_option( 'uncanny_public_active_classes', array() );
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
	 * @return string
	 */
	public static function get_admin_media( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/media/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @param string $file_name
	 * @return string
	 */
	public static function get_admin_css( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/css/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @param string $file_name
	 * @return string
	 */
	public static function get_admin_js( $file_name ) {
		$asset_url = plugins_url( 'assets/admin/js/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @param string $file_name
	 * @return string
	 */
	public static function get_site_media( $file_name ) {
		$asset_url = plugins_url( 'assets/site/media/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @param string $file_name
	 * @return string
	 */
	public static function get_site_css( $file_name ) {
		$asset_url = plugins_url( 'assets/site/css/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @param string $file_name
	 * @return string
	 */
	public static function get_site_js( $file_name ) {
		$asset_url = plugins_url( 'assets/site/js/'.$file_name, __FILE__ );
		return $asset_url;
	}
	/**
	 * @return string
	 */
	public static function get_project_name() {
		if ( null === self::$project_name ){
			self::$project_name = 'uncanny_learndash_public';
		}
		return  self::$project_name;
	}

	/**
	 * @param $project_name
	 */
	public static function set_project_name( $project_name ){
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
		if ( null === self::$css_prefix ){
			self::$css_prefix = str_replace( '_', '-', self::get_prefix() );
		}
		return  self::$css_prefix;
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
	public static function get_text_domain() {
		return self::get_project_name();
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
			self::$version = '1.0';
		}

		return self::$version;
	}

	/**
	 * @return array
	 */
	public static function stripslashes_deep($value)
	{
		$new_array = array();

		foreach( $value as $key => $content ){
			$key = stripslashes( $key );
			$new_array[ $key ] = $content;
		}

		return $new_array;
	}

}
