<?php
/**
 * Disable all WordPress automatic updates.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class DisableAutoUpdates implements Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Disable the automatic updater subsystem entirely.
        \add_filter( 'automatic_updater_disabled', '__return_true' );

        // Never force auto-updates for any item.
        \add_filter( 'wp_is_auto_update_forced_for_item', '__return_false', 10, 2 );

        // Disable auto-updates for core, plugins, and themes.
        \add_filter( 'auto_update_core', '__return_false', 10, 2 );
        \add_filter( 'auto_update_plugin', '__return_false', 10, 2 );
        \add_filter( 'auto_update_theme', '__return_false', 10, 2 );
    }
}
