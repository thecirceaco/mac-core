<?php
/**
 * Disable WordPress Site Health UI.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class DisableSiteHealth implements Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Remove Tools → Site Health (single-site admin).
        \add_action(
            'admin_menu',
            [$this, 'remove_site_health_menu'],
            99
        );

        // Remove Tools → Site Health (network admin).
        \add_action(
            'network_admin_menu',
            [$this, 'remove_site_health_menu'],
            99
        );

        // Block direct access to site-health.php.
        \add_action(
            'load-site-health.php',
            [$this, 'redirect_site_health_access']
        );

        // Catch edge cases via screen detection.
        \add_action(
            'current_screen',
            [$this, 'maybe_redirect_site_health_screen']
        );

        // Remove Site Health dashboard widget.
        \add_action(
            'wp_dashboard_setup',
            [$this, 'remove_site_health_dashboard_widget'],
            20
        );
    }

    /**
     * Remove Site Health submenu from Tools.
     *
     * @return void
     */
    public function remove_site_health_menu(): void
    {
        \remove_submenu_page( 'tools.php', 'site-health.php' );
    }

    /**
     * Redirect direct access to Site Health screens.
     *
     * @return void
     */
    public function redirect_site_health_access(): void
    {
        \wp_safe_redirect( \admin_url( 'index.php' ) );
        exit;
    }

    /**
     * Redirect Site Health screens detected via current_screen.
     *
     * @param object $screen Current screen object.
     * @return void
     */
    public function maybe_redirect_site_health_screen( object $screen ): void
    {
        $screen_id = (string) ( $screen->id ?? '' );

        if (
            $screen_id === 'site-health' ||
            \str_starts_with( $screen_id, 'tools_page_site-health' )
        ) {
            \wp_safe_redirect( \admin_url( 'index.php' ) );
            exit;
        }
    }

    /**
     * Remove Site Health dashboard widget.
     *
     * @return void
     */
    public function remove_site_health_dashboard_widget(): void
    {
        \remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
    }
}
