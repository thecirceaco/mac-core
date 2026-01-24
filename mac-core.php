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
 * Version:           0.1.0
 * Author:            Circea
 * Author URI:        https://circea.co
 * Update URI:        https://github.com/thecirceaco/mac-core
 * Requires PHP:      8.0
 * Requires at least: 6.0
 * License:           GNU General Public License v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/includes/autoload.php';

\MacCore\Kernel::boot();
