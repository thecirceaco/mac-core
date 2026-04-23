<?php
/**
 * Post terms helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

use WP_Term;

/**
 * Post terms helpers.
 */
final class GetPostTerms
{
	private const WRAPPER_CLASS = 'mac-core-terms';

	private const ITEM_CLASS = 'mac-core-terms__item';

	private const LINK_CLASS = 'mac-core-terms__link';

	private const TEXT_CLASS = 'mac-core-terms__text';

	/**
	 * Unified post terms output.
	 *
	 * @param int|string|null $post_id  Post ID. Defaults to the current post.
	 * @param string          $taxonomy Taxonomy slug.
	 * @param string          $format   Output format: plain, links, or spans.
	 * @param string          $attr     Term attribute: name, slug, or term_id.
	 * @param string          $class    Optional wrapper list class for HTML formats.
	 * @param string          $sep      Separator for plain output.
	 */
	public static function get(
		int|string|null $post_id = null,
		string $taxonomy = 'category',
		string $format = 'plain',
		string $attr = 'name',
		string $class = '',
		string $sep = ', '
	): string {
		$post_id = null !== $post_id && $post_id !== '' ? $post_id : \get_the_ID();

		if ( \is_numeric( $post_id ) ) {
			$post_id = (int) $post_id;
		}

		if ( ! \is_int( $post_id ) || $post_id <= 0 || $taxonomy === '' ) {
			return '';
		}

		$terms = \get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || \is_wp_error( $terms ) ) {
			return '';
		}

		$format = \strtolower( \trim( $format ) );
		$attr   = \strtolower( \trim( $attr ) );

		if ( ! \in_array( $format, [ 'plain', 'links', 'spans' ], true ) ) {
			$format = 'plain';
		}

		if ( ! \in_array( $attr, [ 'name', 'slug', 'term_id' ], true ) ) {
			$attr = 'name';
		}

		$items = [];

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$value = $attr === 'term_id'
				? (string) (int) $term->term_id
				: (string) ( $term->{$attr} ?? '' );

			if ( $value === '' ) {
				continue;
			}

			if ( $format === 'plain' ) {
				$items[] = \esc_html( $value );
				continue;
			}

			if ( $format === 'links' ) {
				$url = \get_term_link( $term );

				if ( \is_wp_error( $url ) || ! \is_string( $url ) || $url === '' ) {
					continue;
				}

				$items[] = \sprintf(
					'<li class="%s"><a class="%s" href="%s" rel="tag">%s</a></li>',
					\esc_attr( self::ITEM_CLASS ),
					\esc_attr( self::LINK_CLASS ),
					\esc_url( $url ),
					\esc_html( $value )
				);

				continue;
			}

			$items[] = \sprintf(
				'<li class="%s"><span class="%s">%s</span></li>',
				\esc_attr( self::ITEM_CLASS ),
				\esc_attr( self::TEXT_CLASS ),
				\esc_html( $value )
			);
		}

		if ( $format === 'plain' ) {
			return \implode( \esc_html( $sep ), $items );
		}

		if ( $items === [] ) {
			return '';
		}

		$wrapper_classes = [ self::WRAPPER_CLASS ];
		$class           = \trim( $class );

		if ( $class !== '' ) {
			$wrapper_classes[] = $class;
		}

		return '<ul class="' . \esc_attr( \implode( ' ', $wrapper_classes ) ) . '">' . \implode( '', $items ) . '</ul>';
	}
}
