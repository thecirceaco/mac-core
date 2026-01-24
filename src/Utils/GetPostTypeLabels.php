<?php
declare(strict_types=1);

namespace MacCore\Utils;

use WP_Post_Type;

/**
 * Post type label helpers.
 */
final class PostTypeLabels
{
    /** @var array<string,string> */
    private static array $cacheSingular = [];

    /** @var array<string,string> */
    private static array $cachePlural = [];

    /**
     * Get singular label for a post type.
     */
    public static function singular(?string $postType = null): string
    {
        $postType = self::resolvePostType($postType);

        if ($postType === null) {
            return 'Post';
        }

        if (isset(self::$cacheSingular[$postType])) {
            return self::$cacheSingular[$postType];
        }

        $obj = get_post_type_object($postType);

        $label = $obj instanceof WP_Post_Type
            ? (string) ($obj->labels->singular_name ?? $obj->label ?? ucfirst($postType))
            : ucfirst($postType);

        return self::$cacheSingular[$postType] = $label;
    }

    /**
     * Get plural label for a post type.
     */
    public static function plural(?string $postType = null): string
    {
        $postType = self::resolvePostType($postType);

        if ($postType === null) {
            return 'Posts';
        }

        if (isset(self::$cachePlural[$postType])) {
            return self::$cachePlural[$postType];
        }

        $obj = get_post_type_object($postType);

        $label = $obj instanceof WP_Post_Type
            ? (string) ($obj->labels->name ?? $obj->label ?? ucfirst($postType))
            : ucfirst($postType);

        return self::$cachePlural[$postType] = $label;
    }

    private static function resolvePostType(?string $postType): ?string
    {
        if (is_string($postType) && $postType !== '') {
            return sanitize_key($postType);
        }

        $current = get_post_type();

        return is_string($current) && $current !== ''
            ? sanitize_key($current)
            : null;
    }
}

namespace {
    if (! function_exists('mac_get_post_type_singular')) {
        function mac_get_post_type_singular(?string $postType = null): string
        {
            return \MacCore\Utils\PostTypeLabels::singular($postType);
        }
    }

    if (! function_exists('mac_get_post_type_plural')) {
        function mac_get_post_type_plural(?string $postType = null): string
        {
            return \MacCore\Utils\PostTypeLabels::plural($postType);
        }
    }
}
