<?php
/**
 * Count array items wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! \function_exists( 'mac_core_count_array_items' ) ) {
	/**
	 * Count array post meta values.
	 *
	 * Builders such as Bricks pass every argument as a string and can leave one out, so
	 * the arguments aren't typed: a missing or empty key counts 0, a missing or empty
	 * post ID means the current post, and a post ID that isn't a number counts 0.
	 *
	 * @param mixed $meta_key Post meta key.
	 * @param mixed $post_id  Post ID. Defaults to the current post.
	 */
	function mac_core_count_array_items( mixed $meta_key = '', mixed $post_id = null ): int
	{
		if ( ! \is_string( $meta_key ) || $meta_key === '' ) {
			return 0;
		}

		if ( null === $post_id || $post_id === '' ) {
			return \MacCore\Utils\CountArrayItems::count( $meta_key );
		}

		if ( ! \is_numeric( $post_id ) ) {
			return 0;
		}

		return \MacCore\Utils\CountArrayItems::count( $meta_key, (int) $post_id );
	}
}
