<?php
/**
 * Plugin kernel.
 *
 * Responsible for instantiating and registering all services.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore;

use MacCore\Contracts\Service;
use MacCore\Services\Core\AddLastLoginAdminColumn;
use MacCore\Services\Core\ApplyBranding;
use MacCore\Services\Core\ControlComments;
use MacCore\Services\Core\DisableAdminBar;
use MacCore\Services\Core\DisableAutoUpdates;
use MacCore\Services\Core\DisableSiteHealth;
use MacCore\Services\Core\RemoveDashboardClutter;
use MacCore\Services\Core\TruncateExcerpts;
use MacCore\Services\Media\AddCustomImageSizes;
use MacCore\Services\Media\AllowFontMimeTypes;
use MacCore\Services\Media\DisableImageCompression;
use MacCore\Services\Media\DisallowVideoMimeTypes;
use MacCore\Services\Media\RemoveDefaultImageSizes;
use MacCore\Services\Updater\GitHubUpdater;

final class Kernel
{
    /**
     * Prevent double bootstrapping.
     */
    private static bool $booted = false;

    /**
     * Bootstrap all plugin services.
     *
     * @return void
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        (new self())->register_services();
    }

    /**
     * Instantiate and register all services.
     *
     * @return void
     */
    private function register_services(): void
    {
        foreach ($this->get_services() as $service) {
            $service->register();
        }
    }

    /**
     * List of services to load.
     *
     * @return array<int,Service>
     */
    private function get_services(): array
    {
        return [
            // Core
            new ApplyBranding(),
            new ControlComments(),
            new DisableAdminBar(),
            new DisableAutoUpdates(),
            new DisableSiteHealth(),
            new TruncateExcerpts(),
            new RemoveDashboardClutter(),
            new AddLastLoginAdminColumn(),

            // Media
            new AddCustomImageSizes(),
            new AllowFontMimeTypes(),
            new DisableImageCompression(),
            new DisallowVideoMimeTypes(),
            new RemoveDefaultImageSizes(),

            // Updater (placeholder)
            new GitHubUpdater(),
        ];
    }
}
