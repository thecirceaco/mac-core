<?php
/**
 * Post type label helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils {

use WP_Post_Type;

/**
 * Post type label helpers.
 */
final class GetPostTypeLabels
{
    public static function get( ?string $postType = null, string $type = 'singular', string $fallback = '' ): string
    {
        $type = \strtolower( \trim( $type ) );
        $type = $type === 'plural' ? 'plural' : 'singular';

        $postType = self::resolvePostType( $postType );

        if ( $postType === null ) {
            return $fallback !== '' ? $fallback : ( $type === 'plural' ? 'Posts' : 'Post' );
        }

        $object = \get_post_type_object( $postType );

        if ( ! $object instanceof WP_Post_Type ) {
            return $fallback !== '' ? $fallback : ( $type === 'plural' ? 'Posts' : 'Post' );
        }

        if ( $type === 'plural' ) {
            $label = (string) ( $object->labels->name ?? '' );
            return $label !== '' ? $label : ( $fallback !== '' ? $fallback : 'Posts' );
        }

        $label = (string) ( $object->labels->singular_name ?? '' );
        return $label !== '' ? $label : ( $fallback !== '' ? $fallback : 'Post' );
    }

    public static function singular( ?string $postType = null, string $fallback = '' ): string
    {
        return self::get( $postType, 'singular', $fallback );
    }

    public static function plural( ?string $postType = null, string $fallback = '' ): string
    {
        return self::get( $postType, 'plural', $fallback );
    }

    private static function resolvePostType( ?string $postType = null ): ?string
    {
        if ( $postType === null || $postType === '' ) {
            $current = \get_post_type();
            $postType = \is_string( $current ) ? $current : '';
        }

        $postType = \sanitize_key( $postType );

        return $postType !== '' ? $postType : null;
    }
}

}

namespace {

if ( ! function_exists( 'mac_get_post_type_label' ) ) {
    function mac_get_post_type_label( ?string $post_type = null, string $type = 'singular', string $fallback = '' ): string
    {
        return \MacCore\Utils\GetPostTypeLabels::get( $post_type, $type, $fallback );
    }
}

if ( ! function_exists( 'mac_get_post_type_singular' ) ) {
    function mac_get_post_type_singular( ?string $post_type = null, string $fallback = '' ): string
    {
        return \MacCore\Utils\GetPostTypeLabels::singular( $post_type, $fallback );
    }
}

if ( ! function_exists( 'mac_get_post_type_plural' ) ) {
    function mac_get_post_type_plural( ?string $post_type = null, string $fallback = '' ): string
    {
        return \MacCore\Utils\GetPostTypeLabels::plural( $post_type, $fallback );
    }
}

}
