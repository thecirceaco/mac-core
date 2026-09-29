<?php
/**
 * Plugin status wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! \function_exists( 'mac_core_get_plugin_status' ) ) {
	/**
	 * Check whether a supported plugin stack entry is active.
	 *
	 * Builders such as Bricks can call it without an argument, so the key isn't typed:
	 * a missing key, or one that isn't a string, is never active.
	 *
	 * @param mixed $plugin Supported plugin key, like `acf` or `wsf`.
	 */
	function mac_core_get_plugin_status( mixed $plugin = '' ): bool
	{
		return \is_string( $plugin ) && \MacCore\Utils\GetPluginStatus::get( $plugin );
	}
}
