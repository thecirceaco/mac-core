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

// Plugin slug.
define( 'MAC_CORE_SLUG', 'mac-core' );

// Plugin paths.
define( 'MAC_CORE_PATH', __DIR__ . '/' );
define( 'MAC_CORE_SRC_PATH', MAC_CORE_PATH . 'src/' );
define( 'MAC_CORE_ASSETS_PATH', MAC_CORE_PATH . 'assets/' );

// Plugin URLs.
define( 'MAC_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'MAC_CORE_ASSETS_URL', MAC_CORE_URL . 'assets/' );
