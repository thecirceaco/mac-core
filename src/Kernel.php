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
use MacCore\Services\Core\ApplyAdminBranding;
use MacCore\Services\Core\DisableAdminBar;
use MacCore\Services\Core\DisableAutoUpdates;
use MacCore\Services\Core\DisableSiteHealth;
use MacCore\Services\Core\CustomizeExcerpts;
use MacCore\Services\Core\AddLastLoginAdminColumn;
use MacCore\Services\Core\RemoveDashboardClutter;
use MacCore\Services\Media\AddCustomImageSizes;
use MacCore\Services\Media\RemoveDefaultImageSizes;
use MacCore\Services\Media\DisableImageCompression;
use MacCore\Services\Media\AllowFontMimeTypes;
use MacCore\Services\Media\DisallowVideoMimeTypes;
use MacCore\Services\Comments\EnforceCommentPolicy;
use MacCore\Services\Comments\CustomizeCommentsAdminUi;
use MacCore\Services\Updater\GitHubUpdater;

final class Kernel
{
    /**
     * Register all plugin services.
     *
     * @return void
     */
    public function boot(): void
    {
        foreach ($this->get_services() as $service) {
            $service->register();
        }
    }

    /**
     * Instantiate all services.
     *
     * @return array<int,Service>
     */
    private function get_services(): array
    {
        return [
            // Core
            new ApplyAdminBranding(),
            new DisableAdminBar(),
            new DisableAutoUpdates(),
            new DisableSiteHealth(),
            new CustomizeExcerpts(),
            new AddLastLoginAdminColumn(),
            new RemoveDashboardClutter(),

            // Media
            new AddCustomImageSizes(),
            new RemoveDefaultImageSizes(),
            new DisableImageCompression(),
            new AllowFontMimeTypes(),
            new DisallowVideoMimeTypes(),

            // Comments
            new EnforceCommentPolicy(),
            new CustomizeCommentsAdminUi(),

            // Updater (placeholder, no logic yet)
            new GitHubUpdater(),
        ];
    }
}
