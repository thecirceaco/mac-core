<?php
/**
 * Format datetime wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_format_datetime' ) ) {
	/**
	 * Format a preset date/time value for templates or builders.
	 */
	function mac_format_datetime(
		?string $preset = null,
		?string $view = null,
		int|string|null $post_id = null
	): string {
		return \MacCore\Utils\FormatDatetime::format( $preset, $view, $post_id );
	}
}
