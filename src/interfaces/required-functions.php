<?php

namespace uncanny_learndash_toolkit;

/**
 * Interface RequiredFunctions
 * @package uncanny_learndash_toolkit
 */
interface RequiredFunctions {

	/**
	 * @public
	 * @static
	 * @return mixed
	 */
	public static function get_details();

	/**
	 * @public
	 * @static
	 * @return mixed
	 */
	public static function dependants_exist();

}