<?php
/**
 * Count array items wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_core_count_array_items' ) ) {
	/**
	 * Count array post meta values.
	 */
	function mac_core_count_array_items( string $meta_key, ?int $post_id = null ): int
	{
		return \MacCore\Utils\CountArrayItems::count( $meta_key, $post_id );
	}
}
