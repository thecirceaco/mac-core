<?php
declare(strict_types=1);

namespace MacCore\Utils;

/**
 * Plugin status helpers.
 */
final class GetPluginStatus
{
    /** @var array<string,bool> */
    private static array $cache = [];

    /**
     * Generic plugin active check.
     */
    public static function isPluginActive(string $pluginFile): bool
    {
        $pluginFile = ltrim($pluginFile, '/');
        if ($pluginFile === '') {
            return false;
        }

        if (isset(self::$cache[$pluginFile])) {
            return self::$cache[$pluginFile];
        }

        if (! function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $active = false;

        if (function_exists('is_plugin_active') && is_plugin_active($pluginFile)) {
            $active = true;
        } elseif (is_multisite() && function_exists('is_plugin_active_for_network')) {
            $active = is_plugin_active_for_network($pluginFile);
        } else {
            $siteActive = (array) get_option('active_plugins', []);
            $netActive  = (array) get_site_option('active_sitewide_plugins', []);
            $active     = in_array($pluginFile, $siteActive, true) || isset($netActive[$pluginFile]);
        }

        return self::$cache[$pluginFile] = $active;
    }

    /* -------------------------
     * Explicit helpers
     * ------------------------- */

    public static function isAdminColumnsActive(): bool
    {
        return self::isPluginActive('admin-columns-pro/admin-columns-pro.php');
    }

    public static function isAcfActive(): bool
    {
        return self::isPluginActive('advanced-custom-fields-pro/acf.php');
    }

    public static function isAiowpMigrationActive(): bool
    {
        return self::isPluginActive('all-in-one-wp-migration/all-in-one-wp-migration.php')
            || self::isPluginActive('all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php');
    }

    public static function isAutomaticCssActive(): bool
    {
        return self::isPluginActive('automaticcss-plugin/automaticcss-plugin.php');
    }

    public static function isCommandUiActive(): bool
    {
        return self::isPluginActive('commandui/commandui.php');
    }

    public static function isEtchActive(): bool
    {
        return self::isPluginActive('etch/etch.php') || class_exists('\Etch\Plugin');
    }

    public static function isFramesActive(): bool
    {
        return self::isPluginActive('frames-plugin/frames-plugin.php');
    }

    public static function isMetaBoxActive(): bool
    {
        return self::isPluginActive('meta-box/meta-box.php')
            || self::isPluginActive('meta-box-aio/meta-box-aio.php');
    }

    public static function isMotionPageActive(): bool
    {
        return self::isPluginActive('motionpage/motionpage.php');
    }

    public static function isPatchstackActive(): bool
    {
        return self::isPluginActive('patchstack/patchstack.php');
    }

    public static function isPerfmattersActive(): bool
    {
        return self::isPluginActive('perfmatters/perfmatters.php');
    }

    public static function isRankMathActive(): bool
    {
        return self::isPluginActive('seo-by-rank-math/rank-math.php')
            || self::isPluginActive('seo-by-rank-math-pro/rank-math-pro.php');
    }

    public static function isShortPixelActive(): bool
    {
        return self::isPluginActive('shortpixel-image-optimizer/shortpixel-plugin.php');
    }

    public static function isSureCartActive(): bool
    {
        return self::isPluginActive('surecart/surecart.php');
    }

    public static function isSureMembersActive(): bool
    {
        return self::isPluginActive('suremembers/suremembers.php');
    }

    public static function isWpGridBuilderActive(): bool
    {
        return self::isPluginActive('wp-grid-builder/wp-grid-builder.php');
    }

    public static function isWsFormActive(): bool
    {
        return self::isPluginActive('ws-form/ws-form.php')
            || self::isPluginActive('ws-form-pro/ws-form.php');
    }

    public static function isSureContactActive(): bool
    {
        return self::isPluginActive('surecontact/surecontact.php');
    }

    public static function isPrestoPlayerActive(): bool
    {
        return self::isPluginActive('presto-player-pro/presto-player-pro.php');
    }

    public static function isSureTriggersActive(): bool
    {
        return self::isPluginActive('suretriggers/suretriggers.php');
    }

    public static function isOttokitActive(): bool
    {
        return self::isPluginActive('suretriggers/suretriggers.php');
    }

    public static function isFluentSmtpActive(): bool
    {
        return self::isPluginActive('fluent-smtp/fluent-smtp.php');
    }

    public static function isPostmarkActive(): bool
    {
        return self::isPluginActive('postmark-approved-wordpress-plugin/postmark-approved-wordpress-plugin.php');
    }

    public static function isSureMailsActive(): bool
    {
        return self::isPluginActive('suremails/suremails.php');
    }
}
