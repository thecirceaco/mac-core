<?php
/**
 * Register custom image sizes.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Media;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class AddCustomImageSizes implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'after_setup_theme', [ $this, 'register_image_sizes' ], 10 );
		\add_filter( 'image_size_names_choose', [ $this, 'add_sizes_to_media_selector' ] );
	}

	public function register_image_sizes(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\add_theme_support( 'post-thumbnails' );

		foreach ( $this->widths() as $width ) {
			\add_image_size( 'mac_image_' . $width, $width, 9999, false );
		}
	}

	public function add_sizes_to_media_selector( array $sizes ): array
	{
		if ( ! $this->enabled() ) {
			return $sizes;
		}

		foreach ( $this->widths() as $width ) {
			$sizes[ 'mac_image_' . $width ] = 'mac-image-' . $width;
		}

		return $sizes;
	}

	/**
	 * @return array<int,int>
	 */
	private function widths(): array
	{
		$widths = $this->settings->get( 'media', 'custom_image_widths' );

		return \is_array( $widths ) ? $widths : [];
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'media', 'custom_image_sizes_enabled' );
	}
}
