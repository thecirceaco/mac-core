<?php
/**
 * Disable admin bar for non-admin users on the frontend.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class DisableAdminBar implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'after_setup_theme', [ $this, 'maybe_disable_admin_bar' ], 10 );
	}

	public function maybe_disable_admin_bar(): void
	{
		if ( ! $this->enabled() || \is_admin() || \current_user_can( 'manage_options' ) ) {
			return;
		}

		\show_admin_bar( false );
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'disable_admin_bar_for_non_admins' );
	}
}
