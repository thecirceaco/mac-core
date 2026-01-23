<?php
/**
 * Plugin status helper.
 *
 * Detection strategy:
 * - Uses WordPress core helpers when available
 *   (is_plugin_active, is_plugin_active_for_network)
 * - Falls back to reading active plugin options
 *
 * Expected input:
 * - Plugin file path relative to wp-content/plugins
 *
 * Common plugin paths used in mac projects (REFERENCE):
 * - advanced-custom-fields-pro/acf.php
 * - bricks/brickslabs.php
 * - shortpixel-image-optimiser/wp-shortpixel.php
 * - wp-grid-builder/wp-grid-builder.php
 * - ws-form-pro/ws-form.php
 * - rank-math/rank-math.php
 * - perfmatters/perfmatters.php
 * - etch/etch.php
 *
 * Example usage:
 *   GetPluginStatus::isPluginActive('etch/etch.php');
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Support;

final class GetPluginStatus
{
    /** @var array<string,bool> */
    private static array $cache = [];

    public static function isPluginActive(string $pluginFile): bool
    {
        $pluginFile = \ltrim($pluginFile, '/');
        if ($pluginFile === '') {
            return false;
        }

        if (isset(self::$cache[$pluginFile])) {
            return self::$cache[$pluginFile];
        }

        if (! \function_exists('is_plugin_active')) {
            @\include_once \ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $active = false;

        if (\function_exists('is_plugin_active') && \is_plugin_active($pluginFile)) {
            $active = true;
        } elseif (
            \is_multisite()
            && \function_exists('is_plugin_active_for_network')
            && \is_plugin_active_for_network($pluginFile)
        ) {
            $active = true;
        } else {
            // Fallback via options.
            $siteActive = (array) \get_option('active_plugins', []);
            $netActive  = \is_multisite()
                ? (array) \get_site_option('active_sitewide_plugins', [])
                : [];

            $active = \in_array($pluginFile, $siteActive, true)
                || isset($netActive[$pluginFile]);
        }

        return self::$cache[$pluginFile] = $active;
    }
}
