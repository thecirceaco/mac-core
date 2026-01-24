<?php
declare(strict_types=1);

namespace MacCore\Utils;

use WP_Taxonomy;
use WP_Term;

/**
 * Taxonomy label helpers.
 */
final class TaxonomyLabels
{
    /** @var array<string,string> */
    private static array $cacheSingular = [];

    /** @var array<string,string> */
    private static array $cachePlural = [];

    /**
     * Get singular label for a taxonomy.
     *
     * @param int|string|WP_Term|null $termOrTax
     */
    public static function singular($termOrTax = null): string
    {
        $slug = self::resolveTaxonomySlug($termOrTax);

        if ($slug === null) {
            return 'Term';
        }

        if (isset(self::$cacheSingular[$slug])) {
            return self::$cacheSingular[$slug];
        }

        $tx = get_taxonomy($slug);

        $label = $tx instanceof WP_Taxonomy
            ? (string) ($tx->labels->singular_name ?? $tx->label ?? ucfirst($slug))
            : ucfirst($slug);

        return self::$cacheSingular[$slug] = $label;
    }

    /**
     * Get plural label for a taxonomy.
     *
     * @param int|string|WP_Term|null $termOrTax
     */
    public static function plural($termOrTax = null): string
    {
        $slug = self::resolveTaxonomySlug($termOrTax);

        if ($slug === null) {
            return 'Terms';
        }

        if (isset(self::$cachePlural[$slug])) {
            return self::$cachePlural[$slug];
        }

        $tx = get_taxonomy($slug);

        $label = $tx instanceof WP_Taxonomy
            ? (string) ($tx->labels->name ?? $tx->label ?? ucfirst($slug))
            : ucfirst($slug);

        return self::$cachePlural[$slug] = $label;
    }

    /**
     * Resolve taxonomy slug from mixed input.
     *
     * @param int|string|WP_Term|null $termOrTax
     */
    private static function resolveTaxonomySlug($termOrTax): ?string
    {
        if ($termOrTax instanceof WP_Term) {
            return sanitize_key((string) $termOrTax->taxonomy);
        }

        if (is_numeric($termOrTax)) {
            $term = get_term((int) $termOrTax);
            return $term instanceof WP_Term
                ? sanitize_key((string) $term->taxonomy)
                : null;
        }

        if (is_string($termOrTax) && $termOrTax !== '') {
            $slug = sanitize_key($termOrTax);
            return taxonomy_exists($slug) ? $slug : null;
        }

        // Fallback: use category if it exists
        return taxonomy_exists('category') ? 'category' : null;
    }
}

namespace {
    if (! function_exists('mac_get_taxonomy_singular')) {
        function mac_get_taxonomy_singular($termOrTax = null): string
        {
            return \MacCore\Utils\TaxonomyLabels::singular($termOrTax);
        }
    }

    if (! function_exists('mac_get_taxonomy_plural')) {
        function mac_get_taxonomy_plural($termOrTax = null): string
        {
            return \MacCore\Utils\TaxonomyLabels::plural($termOrTax);
        }
    }
}
