<?php
/**
 * Taxonomy label helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils {

use WP_Taxonomy;
use WP_Term;

/**
 * Taxonomy label helpers.
 */
final class GetTaxonomyLabels
{
    /**
     * Get a singular or plural label for a taxonomy.
     *
     * @param int|string|WP_Term|null $termOrTax Taxonomy slug, term ID, term object, or null for category.
     */
    public static function get(
        int|string|WP_Term|null $termOrTax = 'category',
        string $type = 'singular',
        string $fallback = ''
    ): string {
        $type = \strtolower( \trim( $type ) );
        $type = $type === 'plural' ? 'plural' : 'singular';

        $taxonomy = self::resolveTaxonomySlug( $termOrTax );

        if ( $taxonomy === null ) {
            return $fallback !== '' ? $fallback : ( $type === 'plural' ? 'Terms' : 'Term' );
        }

        $object = \get_taxonomy( $taxonomy );

        if ( ! $object instanceof WP_Taxonomy ) {
            return $fallback !== '' ? $fallback : ( $type === 'plural' ? 'Terms' : 'Term' );
        }

        if ( $type === 'plural' ) {
            $label = (string) ( $object->labels->name ?? '' );
            return $label !== '' ? $label : ( $fallback !== '' ? $fallback : 'Terms' );
        }

        $label = (string) ( $object->labels->singular_name ?? '' );
        return $label !== '' ? $label : ( $fallback !== '' ? $fallback : 'Term' );
    }

    /**
     * @param int|string|WP_Term|null $termOrTax
     */
    public static function singular( int|string|WP_Term|null $termOrTax = 'category', string $fallback = '' ): string
    {
        return self::get( $termOrTax, 'singular', $fallback );
    }

    /**
     * @param int|string|WP_Term|null $termOrTax
     */
    public static function plural( int|string|WP_Term|null $termOrTax = 'category', string $fallback = '' ): string
    {
        return self::get( $termOrTax, 'plural', $fallback );
    }

    /**
     * @param int|string|WP_Term|null $termOrTax
     */
    private static function resolveTaxonomySlug( int|string|WP_Term|null $termOrTax = 'category' ): ?string
    {
        if ( $termOrTax instanceof WP_Term ) {
            $taxonomy = \sanitize_key( (string) $termOrTax->taxonomy );
            return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
        }

        if ( \is_int( $termOrTax ) || ( \is_string( $termOrTax ) && \is_numeric( $termOrTax ) ) ) {
            $term = \get_term( (int) $termOrTax );

            if ( $term instanceof WP_Term ) {
                $taxonomy = \sanitize_key( (string) $term->taxonomy );
                return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
            }

            return null;
        }

        if ( $termOrTax === null || $termOrTax === '' ) {
            $termOrTax = 'category';
        }

        $taxonomy = \sanitize_key( (string) $termOrTax );

        return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
    }
}

}

namespace {

if ( ! function_exists( 'mac_get_taxonomy_label' ) ) {
    function mac_get_taxonomy_label( string $taxonomy = 'category', string $type = 'singular', string $fallback = '' ): string
    {
        return \MacCore\Utils\GetTaxonomyLabels::get( $taxonomy, $type, $fallback );
    }
}

if ( ! function_exists( 'mac_get_taxonomy_singular' ) ) {
    function mac_get_taxonomy_singular( int|string|\WP_Term|null $term_or_tax = 'category', string $fallback = '' ): string
    {
        return \MacCore\Utils\GetTaxonomyLabels::singular( $term_or_tax, $fallback );
    }
}

if ( ! function_exists( 'mac_get_taxonomy_plural' ) ) {
    function mac_get_taxonomy_plural( int|string|\WP_Term|null $term_or_tax = 'category', string $fallback = '' ): string
    {
        return \MacCore\Utils\GetTaxonomyLabels::plural( $term_or_tax, $fallback );
    }
}

}
