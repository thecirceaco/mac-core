<?php
/**
 * Taxonomy label helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

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
	 * @param int|string|WP_Term|null $term_or_tax Taxonomy slug, term ID, term object, or null for category.
	 */
	public static function get(
		int|string|WP_Term|null $term_or_tax = 'category',
		string $type = 'singular',
		string $fallback = ''
	): string {
		$type = \strtolower( \trim( $type ) );
		$type = $type === 'plural' ? 'plural' : 'singular';

		$taxonomy = self::resolve_taxonomy_slug( $term_or_tax );

		if ( null === $taxonomy ) {
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
	 * @param int|string|WP_Term|null $term_or_tax
	 */
	private static function resolve_taxonomy_slug( int|string|WP_Term|null $term_or_tax = 'category' ): ?string
	{
		if ( $term_or_tax instanceof WP_Term ) {
			$taxonomy = \sanitize_key( (string) $term_or_tax->taxonomy );

			return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
		}

		if ( \is_int( $term_or_tax ) || ( \is_string( $term_or_tax ) && \is_numeric( $term_or_tax ) ) ) {
			$term = \get_term( (int) $term_or_tax );

			if ( $term instanceof WP_Term ) {
				$taxonomy = \sanitize_key( (string) $term->taxonomy );

				return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
			}

			return null;
		}

		if ( null === $term_or_tax || $term_or_tax === '' ) {
			$term_or_tax = 'category';
		}

		$taxonomy = \sanitize_key( (string) $term_or_tax );

		return $taxonomy !== '' && \taxonomy_exists( $taxonomy ) ? $taxonomy : null;
	}
}
