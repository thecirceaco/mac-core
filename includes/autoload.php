<?php
/**
 * mac-core autoloader.
 *
 * Registers a PSR-4–style autoloader for the MacCore namespace.
 *
 * @package mac-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;

\spl_autoload_register(
    static function ( string $class ): void {
        $prefix   = 'MacCore\\';
        $base_dir = MAC_CORE_SRC_PATH;

        // Bail if the class does not use the MacCore namespace.
        if ( \strncmp( $prefix, $class, \strlen( $prefix ) ) !== 0 ) {
            return;
        }

        // Remove namespace prefix.
        $relative_class = \substr( $class, \strlen( $prefix ) );

        // Replace namespace separators with directory separators.
        $file = $base_dir . \str_replace( '\\', '/', $relative_class ) . '.php';

        if ( \is_readable( $file ) ) {
            require $file;
        }
    }
);
