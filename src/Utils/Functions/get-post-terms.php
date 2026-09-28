<?php
/**
 * Post terms wrapper.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! \function_exists( 'mac_core_get_post_terms' ) ) {
	/**
	 * Get post terms in plain, link-list, or span-list form.
	 *
	 * The optional `class` argument applies to the wrapper `<ul>` for HTML formats.
	 * If `links` is requested but term links are not usable, the helper falls back to the no-link HTML variant.
	 */
	function mac_core_get_post_terms(
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
