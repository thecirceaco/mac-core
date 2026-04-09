<?php
/**
 * MAC Core admin menu icon helper.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Admin;

final class MenuIcon
{
	/**
	 * Return the MAC Core admin menu icon as a data URI.
	 */
	public static function url(): string
	{
		static $icon_url = null;

		if ( null !== $icon_url ) {
			return $icon_url;
		}

		$icon_file = \MAC_CORE_PATH . 'assets/img/mac-logomark-fill.svg';

		if ( ! \is_readable( $icon_file ) ) {
			$icon_url = '';
			return $icon_url;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading a bundled local SVG asset, not a remote URL.
		$svg = \file_get_contents( $icon_file );

		if ( ! \is_string( $svg ) || $svg === '' ) {
			$icon_url = '';
			return $icon_url;
		}

		$minified_svg = \preg_replace( '/>\s+</', '><', $svg );
		if ( \is_string( $minified_svg ) && $minified_svg !== '' ) {
			$svg = $minified_svg;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- WordPress admin menu icons accept base64-encoded SVG data URIs.
		$icon_url = 'data:image/svg+xml;base64,' . \base64_encode( $svg );

		return $icon_url;
	}
}
