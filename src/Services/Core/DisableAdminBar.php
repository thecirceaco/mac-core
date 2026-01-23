<?php
/**
 * Disable admin bar for non-admin users on the frontend.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;

final class DisableAdminBar implements Service
{
    /**
     * Register WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        \add_action(
            'after_setup_theme',
            [$this, 'maybe_disable_admin_bar'],
            10
        );
    }

    /**
     * Disable the admin bar on the frontend for non-admin users.
     *
     * @return void
     */
    public function maybe_disable_admin_bar(): void
    {
        if ( \is_admin() ) {
            return;
        }

        if ( \current_user_can( 'administrator' ) ) {
            return;
        }

        \show_admin_bar( false );
    }
}
