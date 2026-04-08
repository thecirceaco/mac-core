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
define( 'MAC_CORE_VERSION', '0.5.1' );

// SureCart licensing public token.
define( 'MAC_CORE_SURECART_PUBLIC_TOKEN', 'pt_gkYCpfc8FGWpJ1dFTfnNxC9D' );

// Plugin root path.
define( 'MAC_CORE_PATH', dirname( __DIR__ ) . '/' );

// Source & assets paths.
define( 'MAC_CORE_SRC_PATH', MAC_CORE_PATH . 'src/' );
define( 'MAC_CORE_ASSETS_PATH', MAC_CORE_PATH . 'assets/' );

// URLs.
define( 'MAC_CORE_URL', plugin_dir_url( MAC_CORE_PATH . 'mac-core.php' ) );
define( 'MAC_CORE_ASSETS_URL', MAC_CORE_URL . 'assets/' );
