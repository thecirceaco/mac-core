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

use MacCore\Admin\AdminPage;
use MacCore\Admin\PluginListingLinks;
use MacCore\Contracts\Service;
use MacCore\Licensing\LicensingService;
use MacCore\Policies\Core\AddDeveloperBranding;
use MacCore\Policies\Core\AddLastLoginColumn;
use MacCore\Policies\Core\ControlComments;
use MacCore\Policies\Core\DisableAdminBar;
use MacCore\Policies\Core\DisableAutoUpdates;
use MacCore\Policies\Core\DisableNativePosts;
use MacCore\Policies\Core\DisableSiteHealth;
use MacCore\Policies\Core\RemoveDashboardClutter;
use MacCore\Policies\Core\SetExcerptLength;
use MacCore\Policies\Media\AddCustomImageSizes;
use MacCore\Policies\Media\AllowFontMimeTypes;
use MacCore\Policies\Media\DisableImageCompression;
use MacCore\Policies\Media\DisallowVideoMimeTypes;
use MacCore\Policies\Media\RemoveDefaultImageSizes;
use MacCore\Settings\SettingsController;
use MacCore\Settings\SettingsSchema;
use MacCore\Settings\WordPressSettingsRepository;
use MacCore\Utils\UtilsLoader;

if ( ! \defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class Kernel
{
    /**
     * Prevent double bootstrapping.
     */
    private static bool $booted = false;

    /**
     * Bootstrap all plugin services.
     *
     * mac-core.php calls this at the start of `plugins_loaded` (priority 0), after
     * every active plugin file has loaded.
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
            if ($service instanceof Service) {
                $service->register();
            }
        }
    }

    /**
     * List of services to load.
     *
     * @return array<int,Service>
     */
    private function get_services(): array
    {
        $schema   = new SettingsSchema();
        $settings = new WordPressSettingsRepository( $schema );
        $licensing = new LicensingService();

		$services = [
			new SettingsController( $settings, $schema ),
			new AdminPage( $settings, $schema, $licensing ),
			new PluginListingLinks(),
			$licensing,
			new UtilsLoader( $settings ),

            // Core.
            new AddDeveloperBranding( $settings ),
            new ControlComments( $settings ),
            new DisableAdminBar( $settings ),
            new DisableAutoUpdates( $settings ),
            new DisableNativePosts( $settings ),
            new DisableSiteHealth( $settings ),
            new SetExcerptLength( $settings ),
            new RemoveDashboardClutter( $settings ),
            new AddLastLoginColumn( $settings ),

            // Media.
            new AddCustomImageSizes( $settings ),
            new AllowFontMimeTypes( $settings ),
            new DisableImageCompression( $settings ),
            new DisallowVideoMimeTypes( $settings ),
            new RemoveDefaultImageSizes( $settings ),
        ];

        /**
         * Filter the runtime service list for MAC Core.
         *
         * Future add-ons should append instantiated service objects here. The kernel
         * boots at the start of `plugins_loaded`, so add this filter when the add-on's
         * plugin file loads.
         *
         * @param array<int,Service> $services Service instances.
         */
        $services = \apply_filters( 'mac_core_services', $services );

        return \is_array( $services ) ? $services : [];
    }
}
