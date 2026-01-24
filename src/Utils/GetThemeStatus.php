<?php
declare(strict_types=1);

namespace MacCore\Utils;

use WP_Theme;

/**
 * Theme status helpers.
 */
final class GetThemeStatus
{
    /** @var array<string,bool> */
    private static array $cache = [];

    /**
     * Generic theme active check by slug.
     * Matches active or parent theme.
     */
    public static function isThemeActive(string $slug): bool
    {
        $slug = \sanitize_key($slug);
        if ($slug === '') {
            return false;
        }

        if (isset(self::$cache[$slug])) {
            return self::$cache[$slug];
        }

        $theme = wp_get_theme();
        if (! $theme instanceof WP_Theme) {
            return self::$cache[$slug] = false;
        }

        if ($theme->stylesheet === $slug || $theme->template === $slug) {
            return self::$cache[$slug] = true;
        }

        $parent = $theme->parent();
        if ($parent instanceof WP_Theme) {
            if ($parent->stylesheet === $slug || $parent->template === $slug) {
                return self::$cache[$slug] = true;
            }
        }

        return self::$cache[$slug] = false;
    }

    /* -------------------------
     * Explicit helpers
     * ------------------------- */

    public static function isBricksThemeActive(): bool
    {
        return self::isThemeActive('bricks');
    }

    public static function isEtchThemeActive(): bool
    {
        return self::isThemeActive('etch-theme');
    }
}
