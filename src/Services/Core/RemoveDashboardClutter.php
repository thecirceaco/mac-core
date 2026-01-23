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
     * Register WordPress hooks.
     *
     * @return void
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
            'admin_head',
            [$this, 'hide_empty_dashboard_containers']
        );
    }

    /**
     * Remove common non-comment dashboard widgets.
     *
     * @return void
     */
    public function remove_dashboard_widgets(): void
    {
        \remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
        \remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        \remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        \remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        \remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        \remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        \remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        \remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
    }

    /**
     * Remove the Welcome panel.
     *
     * @return void
     */
    public function remove_welcome_panel(): void
    {
        \remove_action( 'welcome_panel', 'wp_welcome_panel' );
    }

    /**
     * Hide empty dashboard containers for a cleaner UI.
     *
     * @return void
     */
    public function hide_empty_dashboard_containers(): void
    {
        echo '<style>#dashboard-widgets .empty-container{display:none}</style>';
    }
}
