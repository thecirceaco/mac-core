<?php
/**
 * Post type label helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

use WP_Post_Type;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Post type label helpers.
 */
final class GetPostTypeLabels
{
	/**
	 * Get a singular or plural label for a post type.
	 */
	public static function get( ?string $post_type = null, string $type = 'singular', string $fallback = '' ): string
	{
		$type = \strtolower( \trim( $type ) );
		$type = $type === 'plural' ? 'plural' : 'singular';

		$post_type = self::resolve_post_type( $post_type );

		if ( null === $post_type ) {
			return $fallback !== '' ? $fallback : ( $type === 'plural' ? 'Posts' : 'Post' );
		}

		$object = \get_post_type_object( $post_type );

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

	private static function resolve_post_type( ?string $post_type = null ): ?string
	{
		if ( null === $post_type || $post_type === '' ) {
			$current   = \get_post_type();
			$post_type = \is_string( $current ) ? $current : '';
		}

		$post_type = \sanitize_key( $post_type );

		return $post_type !== '' ? $post_type : null;
	}
}
