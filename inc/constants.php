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
if ( ! defined( 'MAC_CORE_VERSION' ) ) {
	define( 'MAC_CORE_VERSION', '1.3.0' );
}

// SureCart licensing public token.
if ( ! defined( 'MAC_CORE_SURECART_PUBLIC_TOKEN' ) ) {
	define( 'MAC_CORE_SURECART_PUBLIC_TOKEN', 'pt_T7wtCcQ2DNPZvadkHjy1uXbw' );
}

// Core option and admin slugs.
if ( ! defined( 'MAC_CORE_ADMIN_SLUG' ) ) {
	define( 'MAC_CORE_ADMIN_SLUG', 'mac-core' );
}

if ( ! defined( 'MAC_CORE_SETTINGS_OPTION' ) ) {
	define( 'MAC_CORE_SETTINGS_OPTION', 'mac_core_settings' );
}

// Plugin root path.
if ( ! defined( 'MAC_CORE_PATH' ) ) {
	define( 'MAC_CORE_PATH', dirname( __DIR__ ) . '/' );
}

// Source & assets paths.
if ( ! defined( 'MAC_CORE_SRC_PATH' ) ) {
	define( 'MAC_CORE_SRC_PATH', MAC_CORE_PATH . 'src/' );
}

if ( ! defined( 'MAC_CORE_ASSETS_PATH' ) ) {
	define( 'MAC_CORE_ASSETS_PATH', MAC_CORE_PATH . 'assets/' );
}

// URLs.
if ( ! defined( 'MAC_CORE_URL' ) ) {
	define( 'MAC_CORE_URL', plugin_dir_url( MAC_CORE_PATH . 'mac-core.php' ) );
}

if ( ! defined( 'MAC_CORE_ASSETS_URL' ) ) {
	define( 'MAC_CORE_ASSETS_URL', MAC_CORE_URL . 'assets/' );
}
