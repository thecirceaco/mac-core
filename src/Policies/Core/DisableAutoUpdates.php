<?php
/**
 * Disable all WordPress automatic updates.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class DisableAutoUpdates implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_filter( 'automatic_updater_disabled', [ $this, 'filter_automatic_updater_disabled' ] );
		\add_filter( 'wp_is_auto_update_forced_for_item', [ $this, 'filter_forced_auto_updates' ], 10, 2 );
		\add_filter( 'auto_update_core', [ $this, 'filter_auto_update' ], 10, 2 );
		\add_filter( 'auto_update_plugin', [ $this, 'filter_auto_update' ], 10, 2 );
		\add_filter( 'auto_update_theme', [ $this, 'filter_auto_update' ], 10, 2 );
	}

	public function filter_automatic_updater_disabled( bool $disabled ): bool
	{
		return $this->enabled() ? true : $disabled;
	}

	public function filter_forced_auto_updates( bool $forced, mixed $item ): bool
	{
		return $this->enabled() ? false : $forced;
	}

	public function filter_auto_update( mixed $update, mixed $item ): mixed
	{
		return $this->enabled() ? false : $update;
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'disable_auto_updates' );
	}
}
