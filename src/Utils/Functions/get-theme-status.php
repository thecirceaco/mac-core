<?php
/**
 * Theme status wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_get_theme_status' ) ) {
	/**
	 * Check whether a supported theme is active.
	 */
	function mac_get_theme_status( string $theme = 'bricks' ): bool
	{
		return \MacCore\Utils\GetThemeStatus::get( $theme );
	}
}
