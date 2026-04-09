<?php
/**
 * Disable WordPress image compression on upload.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Media;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class DisableImageCompression implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'wp_editor_set_quality', [ $this, 'force_quality' ], 10, 1 );
		\add_filter( 'jpeg_quality', [ $this, 'force_quality' ], 10, 1 );
		\add_filter( 'webp_upload_quality', [ $this, 'force_quality' ], 10, 1 );
		\add_filter( 'avif_upload_quality', [ $this, 'force_quality' ], 10, 1 );
	}

	public function force_quality( int $quality ): int
	{
		if ( ! $this->enabled() ) {
			return $quality;
		}

		return 100;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'media', 'disable_image_compression' );
	}
}
