<?php
/**
 * Plugin status helpers.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin status helpers.
 */
final class GetPluginStatus
{
	/**
	 * Supported plugin keys and their main plugin files.
	 *
	 * @var array<string,array<int,string>>
	 */
	private const PLUGINS = [
		'acp'           => [ 'admin-columns-pro/admin-columns-pro.php' ],
		'acf'           => [ 'advanced-custom-fields/acf.php', 'advanced-custom-fields-pro/acf.php' ],
		'aiowpm'        => [ 'all-in-one-wp-migration/all-in-one-wp-migration.php', 'all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php' ],
		'acss'          => [ 'automaticcss-plugin/automaticcss-plugin.php' ],
		'cui'           => [ 'commandui/commandui.php' ],
		'etch'          => [ 'etch/etch.php' ],
		'frames'        => [ 'frames-plugin/frames-plugin.php' ],
		'meta-box'      => [ 'meta-box/meta-box.php', 'meta-box-aio/meta-box-aio.php' ],
		'motionpage'    => [ 'motionpage/motionpage.php' ],
		'patchstack'    => [ 'patchstack/patchstack.php' ],
		'perfmatters'   => [ 'perfmatters/perfmatters.php' ],
		'rank-math'     => [ 'seo-by-rank-math/rank-math.php', 'seo-by-rank-math-pro/rank-math-pro.php' ],
		'spio'          => [ 'shortpixel-image-optimizer/shortpixel-plugin.php' ],
		'surecart'      => [ 'surecart/surecart.php' ],
		'suremembers'   => [ 'suremembers/suremembers.php' ],
		'wpgb'          => [ 'wp-grid-builder/wp-grid-builder.php' ],
		'wsf'           => [ 'ws-form/ws-form.php', 'ws-form-pro/ws-form.php' ],
		'surecontact'   => [ 'surecontact/surecontact.php' ],
		'presto-player' => [ 'presto-player-pro/presto-player-pro.php' ],
		'suretriggers'  => [ 'suretriggers/suretriggers.php' ],
		'ottokit'       => [ 'ottokit/ottokit.php', 'suretriggers/suretriggers.php' ],
		'fluent-smtp'   => [ 'fluent-smtp/fluent-smtp.php' ],
		'postmark'      => [ 'postmark-approved-wordpress-plugin/postmark-approved-wordpress-plugin.php' ],
		'suremails'     => [ 'suremails/suremails.php' ],
	];

	/** @var array<string,bool> */
	private static array $cache = [];

	/**
	 * Check whether one supported plugin key is active.
	 */
	public static function get( string $plugin ): bool
	{
		$plugin = \sanitize_key( $plugin );

		if ( $plugin === '' || ! isset( self::PLUGINS[ $plugin ] ) ) {
			return false;
		}

		if ( isset( self::$cache[ $plugin ] ) ) {
			return self::$cache[ $plugin ];
		}

		if ( $plugin === 'etch' && \class_exists( '\Etch\Plugin' ) ) {
			return self::$cache[ $plugin ] = true;
		}

		foreach ( self::PLUGINS[ $plugin ] as $plugin_file ) {
			if ( self::is_plugin_file_active( $plugin_file ) ) {
				return self::$cache[ $plugin ] = true;
			}
		}

		return self::$cache[ $plugin ] = false;
	}

	private static function is_plugin_file_active( string $plugin_file ): bool
	{
		$plugin_file = \ltrim( $plugin_file, '/' );

		if ( $plugin_file === '' ) {
			return false;
		}

		if ( ! \function_exists( 'is_plugin_active' ) ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active = false;

		if ( \function_exists( 'is_plugin_active' ) && \is_plugin_active( $plugin_file ) ) {
			$active = true;
		} elseif ( \is_multisite() && \function_exists( 'is_plugin_active_for_network' ) ) {
			$active = \is_plugin_active_for_network( $plugin_file );
		} else {
			$site_active = (array) \get_option( 'active_plugins', [] );
			$net_active  = (array) \get_site_option( 'active_sitewide_plugins', [] );
			$active      = \in_array( $plugin_file, $site_active, true ) || isset( $net_active[ $plugin_file ] );
		}

		return $active;
	}
}
