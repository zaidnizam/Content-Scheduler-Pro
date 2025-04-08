<?php
/**
 * Content Scheduler Pro
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