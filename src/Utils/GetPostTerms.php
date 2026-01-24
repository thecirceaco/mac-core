<?php
declare(strict_types=1);

namespace MacCore\Utils;

use WP_Term;

/**
 * Post terms helpers.
 */
final class GetPostTerms
{
    /**
     * Linked term list (HTML).
     *
     * Example output:
     *   <a href="...">Category</a>, <a href="...">News</a>
     */
    public static function html(int $postId, string $taxonomy, string $sep = ', '): string
    {
        $postId = $postId > 0 ? $postId : (int) get_the_ID();
        if ($postId <= 0 || $taxonomy === '') {
            return '';
        }

        $terms = get_the_terms($postId, $taxonomy);
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $items = [];

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $url = get_term_link($term);
            if (is_wp_error($url) || ! is_string($url) || $url === '') {
                continue;
            }

            $label = $term->name ?? $term->slug ?? (string) $term->term_id;

            $items[] = sprintf(
                '<a href="%s" rel="tag">%s</a>',
                esc_url($url),
                esc_html($label)
            );
        }

        return implode($sep, $items);
    }

    /**
     * Plain-text term list.
     *
     * $attr can be: name | slug | term_id
     */
    public static function plain(
        int $postId,
        string $taxonomy,
        string $sep = ', ',
        string $attr = 'name'
    ): string {
        $postId = $postId > 0 ? $postId : (int) get_the_ID();
        if ($postId <= 0 || $taxonomy === '') {
            return '';
        }

        $terms = get_the_terms($postId, $taxonomy);
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $attr = strtolower($attr);
        if (! in_array($attr, ['name', 'slug', 'term_id'], true)) {
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

            if ($value !== '') {
                $items[] = $value;
            }
        }

        return implode($sep, $items);
    }
}

namespace {
    if (! function_exists('mac_get_post_terms_html')) {
        function mac_get_post_terms_html(
            int $post_id,
            string $taxonomy,
            string $sep = ', '
        ): string {
            return \MacCore\Utils\Terms::html($post_id, $taxonomy, $sep);
        }
    }

    if (! function_exists('mac_get_post_terms_plain')) {
        function mac_get_post_terms_plain(
            int $post_id,
            string $taxonomy,
            string $sep = ', ',
            string $attr = 'name'
        ): string {
            return \MacCore\Utils\Terms::plain($post_id, $taxonomy, $sep, $attr);
        }
    }
}
