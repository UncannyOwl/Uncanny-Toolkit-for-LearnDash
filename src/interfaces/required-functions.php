<?php

namespace uncanny_learndash_public;

/**
 * Interface RequiredFunctions
 * @package uncanny_learndash_public
 */
interface RequiredFunctions {
	/**
	 *
	 *
	 * @static
	 * @return mixed
	 */
	static function get_details();

	/**
	 *
	 *
	 * @static
	 * @return mixed
	 */
	static function dependants_exist();
	//static function get_class_settings();
}