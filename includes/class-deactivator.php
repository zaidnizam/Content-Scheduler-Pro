<?php
/**
 * Fired during plugin deactivation.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 */
class Deactivator {

    /**
     * Deactivation hook callback.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules.
        flush_rewrite_rules();
    }
}