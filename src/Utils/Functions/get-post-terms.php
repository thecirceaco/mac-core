<?php
/**
 * Post terms wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! \function_exists( 'mac_get_post_terms' ) ) {
	/**
	 * Get post terms in plain, link-list, or span-list form.
	 *
	 * The optional `class` argument applies to the wrapper `<ul>` for HTML formats.
	 */
	function mac_get_post_terms(
		int|string|null $post_id = null,
		string $taxonomy = 'category',
		string $format = 'plain',
		string $attr = 'name',
		string $class = '',
		string $sep = ', '
	): string {
		return \MacCore\Utils\GetPostTerms::get( $post_id, $taxonomy, $format, $attr, $class, $sep );
	}
}
