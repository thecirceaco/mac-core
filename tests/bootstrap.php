<?php
/**
 * PHPUnit bootstrap for MAC Core.
 *
 * @package mac-core
 */

declare(strict_types=1);

require_once __DIR__ . '/stubs/wordpress.php';

$composer_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( is_readable( $composer_autoload ) ) {
	require_once $composer_autoload;
}

require_once dirname( __DIR__ ) . '/src/Utils/GetPostTerms.php';
require_once dirname( __DIR__ ) . '/src/Utils/FormatDatetime.php';
