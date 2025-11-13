<?php
namespace uncanny_learndash_toolkit\Includes\Two_Factor;

defined( 'ABSPATH' ) || exit;

/**
 * Manages user sessions for 2FA.
 */
class Session_Manager {

	/**
	 * Holds registered hook keys.
	 *
	 * @var array
	 */
	private static $registered_hooks = array();

	/**
	 * Checks if the hooks are registered.
	 *
	 * @param string $key The hook key to check.
	 * @return bool
	 */
	public static function is_hooks_registered( $key = '' ) {
		if ( empty( $key ) ) {
			return ! empty( self::$registered_hooks );
		}
		return in_array( $key, self::$registered_hooks, true );
	}

	/**
	 * Adds a registered hook key.
	 *
	 * @param string $key The hook key to add.
	 * @return void
	 */
	public static function add_registered_hook( $key ) {
		if ( ! in_array( $key, self::$registered_hooks, true ) ) {
			self::$registered_hooks[] = $key;
		}
	}
}
