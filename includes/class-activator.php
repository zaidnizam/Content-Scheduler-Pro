<?php
/**
 * Fired during plugin activation.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class Activator {

    /**
     * Activation hook callback.
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Register custom post type.
        $post_types = new PostTypes();
        $post_types->register_post_types();

        // Flush rewrite rules.
        flush_rewrite_rules();

        // Set the activation time.
        if (!get_option('csp_activation_time')) {
            update_option('csp_activation_time', time());
        }

        // Set the plugin version.
        update_option('csp_version', CSP_VERSION);
    }
}