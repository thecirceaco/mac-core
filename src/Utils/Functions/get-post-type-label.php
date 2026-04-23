<?php
/**
 * Post type label wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_core_get_post_type_label' ) ) {
	/**
	 * Get a singular or plural post type label.
	 */
	function mac_core_get_post_type_label( ?string $post_type = null, string $type = 'singular', string $fallback = '' ): string
	{
		return \MacCore\Utils\GetPostTypeLabels::get( $post_type, $type, $fallback );
	}
}
