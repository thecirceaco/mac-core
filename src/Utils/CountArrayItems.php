<?php
/**
 * Count array values stored in post meta.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils {

    /**
     * Count array values stored in post meta.
     */
    final class CountArrayItems
    {
        /**
         * Count items for a post meta key.
         *
         * @param string   $metaKey Post meta key.
         * @param int|null $postId  Optional. Defaults to current post ID.
         * @return int
         */
        public static function count( string $metaKey, ?int $postId = null ): int
        {
            if ( $metaKey === '' ) {
                return 0;
            }

            $postId = $postId ?: (int) \get_the_ID();
            if ( $postId <= 0 ) {
                return 0;
            }

            $value = \get_post_meta( $postId, $metaKey, true );

            if ( \is_array( $value ) ) {
                return \count( $value );
            }

            if ( $value instanceof \Countable ) {
                return \count( $value );
            }

            return 0;
        }
    }
}

namespace {
    if ( ! function_exists( 'mac_count_array_items' ) ) {
        /**
         * Count array post meta values.
         *
         * Counts items stored as arrays, such as galleries.
         *
         * Examples:
         *   mac_count_array_items('gallery_images');
         *   mac_count_array_items('gallery_images', 123);
         *
         * Builder usage:
         *   {echo:mac_count_array_items('gallery_images')}
         *   {echo:mac_count_array_items('gallery_images', {post_id})}
         *
         * @param string   $metaKey
         * @param int|null $postId
         * @return int
         */
        function mac_count_array_items( string $metaKey, ?int $postId = null ): int
        {
            return \MacCore\Utils\CountArrayItems::count( $metaKey, $postId );
        }
    }
}
