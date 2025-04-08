<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Get option to check if we should remove all data.
$remove_all_data = get_option('csp_remove_data_on_uninstall', false);

if ($remove_all_data) {
    // Delete all scheduled content posts.
    $scheduled_contents = get_posts([
        'post_type'      => 'scheduled_content',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    foreach ($scheduled_contents as $post_id) {
        wp_delete_post($post_id, true);
    }

    // Delete plugin options.
    delete_option('csp_version');
    delete_option('csp_activation_time');
    delete_option('csp_remove_data_on_uninstall');

    // Clear any cached data that might be stored.
    wp_cache_flush();
}