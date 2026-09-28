<?php
/**
 * Taxonomy label wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! \function_exists( 'mac_core_get_taxonomy_label' ) ) {
	/**
	 * Get a singular or plural taxonomy label.
	 *
	 * @param int|string|\WP_Term|null $term_or_tax Taxonomy slug, term ID, term object, or null.
	 */
	function mac_core_get_taxonomy_label(
		int|string|\WP_Term|null $term_or_tax = 'category',
		string $type = 'singular',
		string $fallback = ''
	): string {
		return \MacCore\Utils\GetTaxonomyLabels::get( $term_or_tax, $type, $fallback );
	}
}
