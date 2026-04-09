<?php
/**
 * Disable WordPress Site Health UI.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Policies\Core;

use MacCore\Contracts\Service;
use MacCore\Settings\SettingsRepositoryInterface;

final class DisableSiteHealth implements Service
{
	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'admin_menu', [ $this, 'remove_site_health_menu' ], 99 );
		\add_action( 'network_admin_menu', [ $this, 'remove_site_health_menu' ], 99 );
		\add_action( 'load-site-health.php', [ $this, 'redirect_site_health_access' ] );
		\add_action( 'current_screen', [ $this, 'maybe_redirect_site_health_screen' ] );
		\add_action( 'wp_dashboard_setup', [ $this, 'remove_site_health_dashboard_widget' ], 20 );
	}

	public function remove_site_health_menu(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\remove_submenu_page( 'tools.php', 'site-health.php' );
	}

	public function redirect_site_health_access(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\wp_safe_redirect( \admin_url( 'index.php' ) );
		exit;
	}

	public function maybe_redirect_site_health_screen( object $screen ): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		$screen_id = (string) ( $screen->id ?? '' );

		if ( $screen_id === 'site-health' || \str_starts_with( $screen_id, 'tools_page_site-health' ) ) {
			\wp_safe_redirect( \admin_url( 'index.php' ) );
			exit;
		}
	}

	public function remove_site_health_dashboard_widget(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'disable_site_health' );
	}
}
