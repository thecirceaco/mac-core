<?php
/**
 * Remove default WordPress image sizes.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Media;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class RemoveDefaultImageSizes implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'intermediate_image_sizes', [ $this, 'filter_intermediate_sizes' ] );
		\add_filter( 'intermediate_image_sizes_advanced', [ $this, 'filter_advanced_sizes' ] );
		\add_filter( 'image_size_names_choose', [ $this, 'filter_size_names' ] );
		\add_action( 'init', [ $this, 'remove_registered_sizes' ] );
	}

	public function filter_intermediate_sizes( array $sizes ): array
	{
		if ( ! $this->enabled() ) {
			return $sizes;
		}

		return \array_values( \array_diff( $sizes, $this->removed_sizes() ) );
	}

	public function filter_advanced_sizes( array $sizes ): array
	{
		if ( ! $this->enabled() ) {
			return $sizes;
		}

		foreach ( $this->removed_sizes() as $size ) {
			unset( $sizes[ $size ] );
		}

		return $sizes;
	}

	public function filter_size_names( array $sizes ): array
	{
		if ( ! $this->enabled() ) {
			return $sizes;
		}

		foreach ( $this->removed_sizes() as $size ) {
			unset( $sizes[ $size ] );
		}

		return $sizes;
	}

	public function remove_registered_sizes(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		foreach ( $this->removed_sizes() as $size ) {
			\remove_image_size( $size );
		}
	}

	/**
	 * @return array<int,string>
	 */
	private function removed_sizes(): array
	{
		$sizes = $this->settings->get( 'media', 'removed_image_sizes' );

		return \is_array( $sizes ) ? $sizes : [];
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'media', 'remove_default_image_sizes' );
	}
}
