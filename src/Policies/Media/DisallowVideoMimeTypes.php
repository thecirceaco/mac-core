<?php
/**
 * Disallow common video MIME types from uploads.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Media;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class DisallowVideoMimeTypes implements Service
{
	/**
	 * @var array<int,string>
	 */
	private const BLOCKED_EXTENSIONS = ['mp4', 'mov', 'webm', 'avi', 'mkv', 'wmv', 'm4v'];

	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'upload_mimes', [ $this, 'disallow_video_mimes' ] );
	}

	public function disallow_video_mimes( array $mimes ): array
	{
		if ( ! $this->enabled() ) {
			return $mimes;
		}

		foreach ( \array_keys( $mimes ) as $key ) {
			$extensions = \explode( '|', \strtolower( (string) $key ) );

			if ( \array_intersect( $extensions, $this->blocked_extensions() ) ) {
				unset( $mimes[ $key ] );
			}
		}

		return $mimes;
	}

	/**
	 * @return array<int,string>
	 */
	private function blocked_extensions(): array
	{
		return self::BLOCKED_EXTENSIONS;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'media', 'block_video_uploads' );
	}
}
