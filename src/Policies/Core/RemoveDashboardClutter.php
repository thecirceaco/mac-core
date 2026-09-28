<?php
/**
 * Remove default WordPress dashboard clutter.
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

final class RemoveDashboardClutter implements Service
{
	/**
	 * Dashboard widget IDs and contexts.
	 *
	 * @var array<string,string>
	 */
	private const DASHBOARD_WIDGETS = [
		'dashboard_activity'       => 'normal',
		'dashboard_right_now'      => 'normal',
		'dashboard_incoming_links' => 'normal',
		'dashboard_plugins'        => 'normal',
		'dashboard_quick_press'    => 'side',
		'dashboard_recent_drafts'  => 'side',
		'dashboard_primary'        => 'side',
		'dashboard_secondary'      => 'side',
	];

	public function __construct(
		private readonly SettingsRepositoryInterface $settings
	) {
	}

	public function register(): void
	{
		\add_action( 'wp_dashboard_setup', [ $this, 'remove_dashboard_widgets' ], 20 );
		\add_action( 'welcome_panel', [ $this, 'remove_welcome_panel' ], 0 );
		\add_filter( 'show_welcome_panel', [ $this, 'filter_show_welcome_panel' ] );
		\add_action( 'admin_head-index.php', [ $this, 'hide_empty_dashboard_containers' ] );
	}

	public function remove_dashboard_widgets(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		foreach ( self::DASHBOARD_WIDGETS as $widget_id => $context ) {
			\remove_meta_box( $widget_id, 'dashboard', $context );
		}
	}

	public function remove_welcome_panel(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		\remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}

	public function filter_show_welcome_panel( bool $show ): bool
	{
		return $this->enabled() ? false : $show;
	}

	public function hide_empty_dashboard_containers(): void
	{
		if ( ! $this->enabled() ) {
			return;
		}

		echo '<style>#dashboard-widgets .empty-container{display:none}</style>';
	}

	private function enabled(): bool
	{
		return (bool) $this->settings->get( 'core', 'remove_dashboard_clutter' );
	}
}
