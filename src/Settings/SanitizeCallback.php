<?php
/**
 * Runs an add-on field's sanitize callback.
 *
 * This file does not declare strict_types, on purpose. PHP applies the typing mode of
 * the file that makes a call, and WordPress calls sanitize callbacks from files
 * without strict_types, so a callback typed `int $value` accepts the submitted string
 * "7". Called from a strict_types file, the same callback throws a TypeError.
 *
 * @package mac-core
 */

namespace MacCore\Settings;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SanitizeCallback
{
	/**
	 * Call the callback with the submitted value, the way WordPress would.
	 */
	public static function run( callable $callback, mixed $value ): mixed
	{
		return \call_user_func( $callback, $value );
	}
}
