<?php
/**
 * Theme status helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

use WP_Theme;

/**
 * Theme status helpers.
 */
final class GetThemeStatus
{
	/**
	 * Supported theme keys and the slugs they map to.
	 *
	 * @var array<string,string>
	 */
	private const THEMES = [
		'bricks' => 'bricks',
		'etch'   => 'etch-theme',
	];

	/** @var array<string,bool> */
	private static array $cache = [];

	/**
	 * Check whether one supported theme key is active.
	 */
	public static function get( string $theme = 'bricks' ): bool
	{
		$theme = \sanitize_key( $theme );

		if ( $theme === '' || ! isset( self::THEMES[ $theme ] ) ) {
			return false;
		}

		if ( isset( self::$cache[ $theme ] ) ) {
			return self::$cache[ $theme ];
		}

		$theme_object = \wp_get_theme();

		if ( ! $theme_object instanceof WP_Theme ) {
			return self::$cache[ $theme ] = false;
		}

		$slug = self::THEMES[ $theme ];

		if ( $theme_object->stylesheet === $slug || $theme_object->template === $slug ) {
			return self::$cache[ $theme ] = true;
		}

		$parent = $theme_object->parent();

		if ( $parent instanceof WP_Theme ) {
			if ( $parent->stylesheet === $slug || $parent->template === $slug ) {
				return self::$cache[ $theme ] = true;
			}
		}

		return self::$cache[ $theme ] = false;
	}
}
