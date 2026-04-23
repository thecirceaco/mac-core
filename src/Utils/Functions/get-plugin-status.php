<?php
/**
 * Plugin status wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_core_get_plugin_status' ) ) {
	/**
	 * Check whether a supported plugin stack entry is active.
	 */
	function mac_core_get_plugin_status( string $plugin ): bool
	{
		return \MacCore\Utils\GetPluginStatus::get( $plugin );
	}
}
