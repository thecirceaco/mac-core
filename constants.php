<?php
/**
 * mac-core constants.
 *
 * Centralized plugin constants.
 * This file must not contain logic.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin version.
define( 'MAC_CORE_VERSION', '0.1.0' );

// Plugin slug.
define( 'MAC_CORE_SLUG', 'mac-core' );

// Main plugin file.
define( 'MAC_CORE_PLUGIN_FILE', dirname( __DIR__ ) . '/mac-core.php' );

// Plugin paths.
define( 'MAC_CORE_PATH', plugin_dir_path( MAC_CORE_PLUGIN_FILE ) );
define( 'MAC_CORE_SRC_PATH', MAC_CORE_PATH . 'src/' );
define( 'MAC_CORE_ASSETS_PATH', MAC_CORE_PATH . 'assets/' );

// Plugin URLs.
define( 'MAC_CORE_URL', plugin_dir_url( MAC_CORE_PLUGIN_FILE ) );
define( 'MAC_CORE_ASSETS_URL', MAC_CORE_URL . 'assets/' );
