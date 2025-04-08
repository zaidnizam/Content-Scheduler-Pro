<?php
/**
 * Content Scheduler Pro
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/gpl-2.0.txt>.
 *
 * @package   ContentSchedulerPro
 * @author    hacxk
 * @copyright 2025 hacxk
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Content Scheduler Pro
 * Plugin URI:        https://wordpress.org/plugins/content-scheduler-pro/
 * Description:       Schedule content to appear and disappear automatically at set times.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            hacxk
 * Text Domain:       content-scheduler-pro
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('CSP_VERSION', '1.0.0');
define('CSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CSP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CSP_PLUGIN_FILE', __FILE__);
define('CSP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_content_scheduler_pro() {
    require_once CSP_PLUGIN_DIR . 'includes/class-activator.php';
    ContentSchedulerPro\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_content_scheduler_pro() {
    require_once CSP_PLUGIN_DIR . 'includes/class-deactivator.php';
    ContentSchedulerPro\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_content_scheduler_pro');
register_deactivation_hook(__FILE__, 'deactivate_content_scheduler_pro');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once CSP_PLUGIN_DIR . 'includes/class-content-scheduler-pro.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_content_scheduler_pro() {
    $plugin = new ContentSchedulerPro\ContentSchedulerPro();
    $plugin->run();
}

run_content_scheduler_pro();
