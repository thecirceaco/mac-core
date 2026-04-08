<?php
/**
 * Post terms helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils {

use WP_Term;

/**
 * Post terms helpers.
 */
final class GetPostTerms
{
    /**
     * Unified post terms output.
     *
     * @param int|string|null $postId   Post ID. Defaults to the current post.
     * @param string          $taxonomy Taxonomy slug.
     * @param string          $format   Output format: plain, links, or spans.
     * @param string          $attr     Term attribute: name, slug, or term_id.
     * @param string          $class    Optional list item class for HTML formats.
     * @param string          $sep      Separator for plain output.
     */
    public static function get(
        int|string|null $postId = null,
        string $taxonomy = 'category',
        string $format = 'plain',
        string $attr = 'name',
        string $class = '',
        string $sep = ', '
    ): string {
        $postId = $postId !== null && $postId !== '' ? $postId : \get_the_ID();

        if (\is_numeric($postId)) {
            $postId = (int) $postId;
        }

        if (! \is_int($postId) || $postId <= 0 || $taxonomy === '') {
            return '';
        }

        $terms = \get_the_terms($postId, $taxonomy);
        if (empty($terms) || \is_wp_error($terms)) {
            return '';
        }

        $format = \strtolower(\trim($format));
        $attr   = \strtolower(\trim($attr));

        if (! \in_array($format, ['plain', 'links', 'spans'], true)) {
            $format = 'plain';
        }

        if (! \in_array($attr, ['name', 'slug', 'term_id'], true)) {
            $attr = 'name';
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $value = $attr === 'term_id'
                ? (string) (int) $term->term_id
                : (string) ($term->{$attr} ?? '');

            if ($value === '') {
                continue;
            }

            if ($format === 'plain') {
                $items[] = \esc_html($value);
                continue;
            }

            $liClass = $class !== '' ? ' class="' . \esc_attr($class) . '"' : '';

            if ($format === 'links') {
                $url = \get_term_link($term);

                if (\is_wp_error($url) || ! \is_string($url) || $url === '') {
                    continue;
                }

                $items[] = \sprintf(
                    '<li%s><a href="%s" rel="tag">%s</a></li>',
                    $liClass,
                    \esc_url($url),
                    \esc_html($value)
                );

                continue;
            }

            $items[] = \sprintf(
                '<li%s><span>%s</span></li>',
                $liClass,
                \esc_html($value)
            );
        }

        if ($format === 'plain') {
            return \implode(\esc_html($sep), $items);
        }

        if ($items === []) {
            return '';
        }

        return '<ul>' . \implode('', $items) . '</ul>';
    }

    public static function html(
        int|string|null $postId = null,
        string $taxonomy = 'category',
        string $class = ''
    ): string {
        return self::get($postId, $taxonomy, 'links', 'name', $class);
    }

    public static function plain(
        int|string|null $postId = null,
        string $taxonomy = 'category',
        string $sep = ', ',
        string $attr = 'name'
    ): string {
        return self::get($postId, $taxonomy, 'plain', $attr, '', $sep);
    }
}

}

namespace {

if (! function_exists('mac_get_post_terms')) {
    function mac_get_post_terms(
        int|string|null $post_id = null,
        string $taxonomy = 'category',
        string $format = 'plain',
        string $attr = 'name',
        string $class = '',
        string $sep = ', '
    ): string {
        return \MacCore\Utils\GetPostTerms::get($post_id, $taxonomy, $format, $attr, $class, $sep);
    }
}

if (! function_exists('mac_get_post_terms_html')) {
    function mac_get_post_terms_html(
        int|string|null $post_id = null,
        string $taxonomy = 'category',
        string $class = ''
    ): string {
        return \MacCore\Utils\GetPostTerms::html($post_id, $taxonomy, $class);
    }
}

if (! function_exists('mac_get_post_terms_plain')) {
    function mac_get_post_terms_plain(
        int|string|null $post_id = null,
        string $taxonomy = 'category',
        string $sep = ', ',
        string $attr = 'name'
    ): string {
        return \MacCore\Utils\GetPostTerms::plain($post_id, $taxonomy, $sep, $attr);
    }
}

}
