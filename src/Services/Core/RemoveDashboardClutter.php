<?php
/**
 * Remove default WordPress dashboard clutter.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

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

    /**
     * Register WordPress hooks.
     */
    public function register(): void
    {
        \add_action(
            'wp_dashboard_setup',
            [$this, 'remove_dashboard_widgets'],
            20
        );

        \add_action(
            'welcome_panel',
            [$this, 'remove_welcome_panel'],
            0
        );

        \add_filter(
            'show_welcome_panel',
            '__return_false'
        );

        \add_action(
            'admin_head-index.php',
            [$this, 'hide_empty_dashboard_containers']
        );
    }

    /**
     * Remove common non-comment dashboard widgets.
     */
    public function remove_dashboard_widgets(): void
    {
        foreach ( self::DASHBOARD_WIDGETS as $widget_id => $context ) {
            \remove_meta_box( $widget_id, 'dashboard', $context );
        }
    }

    /**
     * Remove the Welcome panel.
     */
    public function remove_welcome_panel(): void
    {
        \remove_action( 'welcome_panel', 'wp_welcome_panel' );
    }

    /**
     * Hide empty dashboard containers for a cleaner UI.
     */
    public function hide_empty_dashboard_containers(): void
    {
        echo '<style>#dashboard-widgets .empty-container{display:none}</style>';
    }
}
