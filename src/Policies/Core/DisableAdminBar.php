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

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
		if ( ! $this->enabled() || \is_admin() ) {
			return;
		}

		$target = $this->exempt_target();

		if ( $target === '' || $this->current_user_matches_target( $target ) ) {
			return;
		}

		\show_admin_bar( false );
	}

	private function current_user_matches_target( string $target ): bool
	{
		$user = \wp_get_current_user();

		if ( $user instanceof \WP_User && \in_array( $target, (array) $user->roles, true ) ) {
			return true;
		}

		return \current_user_can( $target );
	}

	private function exempt_target(): string
	{
		return \sanitize_key( (string) $this->settings->get( 'core', 'frontend_admin_bar_exempt_target' ) );
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'disable_frontend_admin_bar' );
	}
}
