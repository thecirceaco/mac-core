<?php
/**
 * @package     mac-core
 * @author      Mihai Circea <mihai@circea.co>
 * @copyright   2026 circea.co
 * @license     GPL-2.0-or-later
 * @version     0.1.0
 * @since       0.1.0
 * @link        https://github.com/thecirceaco/mac-core
 *
 * @wordpress-plugin
 * Plugin Name:        mac-core
 * Plugin URI:         https://github.com/thecirceaco/mac-core
 * Description:        Company standard core functionality plugin for WordPress projects.
 * Version:            0.1.0
 * Requires at least:  6.0
 * Requires PHP:       8.0
 * Author:             Mihai Circea
 * Author URI:         https://circea.co
 * License:            GNU General Public License v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/includes/autoload.php';

(new \MacCore\Kernel())->boot();
