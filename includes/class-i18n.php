<?php
/**
 * Define the internationalization functionality.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 */
class I18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'content-scheduler-pro',
            false,
            dirname(plugin_basename(CSP_PLUGIN_FILE)) . '/languages/'
        );
    }
}