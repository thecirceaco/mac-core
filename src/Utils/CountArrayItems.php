<?php
/**
 * Count array values stored in post meta.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Count array values stored in post meta.
 */
final class CountArrayItems
{
	/**
	 * Count items for a post meta key.
	 */
	public static function count( string $meta_key, ?int $post_id = null ): int
	{
		if ( $meta_key === '' ) {
			return 0;
		}

		if ( null === $post_id || $post_id <= 0 ) {
			$post_id = (int) \get_the_ID();
		}

		if ( $post_id <= 0 ) {
			return 0;
		}

		$value = \get_post_meta( $post_id, $meta_key, true );

		if ( \is_array( $value ) ) {
			return \count( $value );
		}

		if ( $value instanceof \Countable ) {
			return \count( $value );
		}

		return 0;
	}
}
