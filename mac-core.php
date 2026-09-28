<?php
/**
 * MAC Core
 *
 * @package           mac-core
 * @author            Circea
 * @copyright         2026 Circea
 *
 * @wordpress-plugin
 * Plugin Name:       MAC Core
 * Plugin URI:        https://circea.co
 * Description:       Company standard core functionality plugin for WordPress projects.
 * Version:           1.1.2
 * Author:            Circea
 * Author URI:        https://circea.co
 * Update URI:        https://github.com/thecirceaco/mac-core
 * Requires PHP:      8.3
 * Requires at least: 6.9
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/inc/constants.php';
require_once __DIR__ . '/inc/autoload.php';

// Boot at the start of plugins_loaded, once every active plugin file has loaded, so
// add-ons that WordPress loads after MAC Core can still add services and settings
// sections when their files load.
if ( did_action( 'plugins_loaded' ) ) {
	\MacCore\Kernel::boot();
} else {
	add_action( 'plugins_loaded', [ \MacCore\Kernel::class, 'boot' ], 0 );
}
