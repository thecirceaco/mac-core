<?php
/**
 * Theme status helper.
 *
 * Detection strategy:
 * - Uses wp_get_theme()
 * - Checks both active theme and parent theme
 * - Compares against known theme slugs
 *
 * Known themes in mac projects (REFERENCE):
 * - bricks      → Bricks theme (slug: bricks)
 * - etch-theme  → Etch theme (slug: etch-theme)
 *
 * Notes:
 * - Bricks and Etch also ship plugins
 * - These helpers check THEME activation only
 *
 * Example usage:
 *   GetThemeStatus::isBricksThemeActive();
 *   GetThemeStatus::isEtchThemeActive();
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Support;

use WP_Theme;

final class GetThemeStatus
{
    /** @var array<string,bool> */
    private static array $cache = [];

    public static function isBricksThemeActive(): bool
    {
        return self::memo('bricks_theme', static function (): bool {
            return \defined('BRICKS_VERSION')
                || \function_exists('bricks_is_builder_main');
        });
    }

    public static function isEtchThemeActive(): bool
    {
        return self::memo('etch_theme', static function (): bool {
            return self::isThemeActiveBySlug('etch-theme');
        });
    }

    private static function memo(string $key, callable $compute): bool
    {
        if (\array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        return self::$cache[$key] = (bool) $compute();
    }

    private static function isThemeActiveBySlug(string $slug): bool
    {
        $slug = \sanitize_key($slug);
        if ($slug === '') {
            return false;
        }

        $theme = \wp_get_theme();
        if (! $theme instanceof WP_Theme) {
            return false;
        }

        if ($theme->stylesheet === $slug || $theme->template === $slug) {
            return true;
        }

        $parent = $theme->parent();
        if ($parent instanceof WP_Theme) {
            return $parent->stylesheet === $slug || $parent->template === $slug;
        }

        return false;
    }
}
