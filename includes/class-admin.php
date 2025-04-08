<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for handling admin functionality.
 *
 * @since 1.0.0
 */
class Admin {

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();

        // Only load on our plugin's pages.
        if (!$screen || 'scheduled_content' !== $screen->post_type) {
            return;
        }

        wp_enqueue_style(
            'content-scheduler-pro-admin',
            CSP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CSP_VERSION,
            'all'
        );

        // Enqueue Tailwind CSS.
        wp_enqueue_style(
            'content-scheduler-pro-tailwind',
            CSP_PLUGIN_URL . 'assets/css/tailwind.css',
            [],
            CSP_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();

        // Only load on our plugin's pages.
        if (!$screen || 'scheduled_content' !== $screen->post_type) {
            return;
        }

        wp_enqueue_script(
            'content-scheduler-pro-admin',
            CSP_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            CSP_VERSION,
            true
        );
        
        // Localize script with data for AJAX
        wp_localize_script(
            'content-scheduler-pro-admin',
            'cspAdminData',
            [
                'nonce'    => wp_create_nonce('csp_admin_nonce'),
                'ajaxurl'  => admin_url('admin-ajax.php'),
                'pluginUrl' => CSP_PLUGIN_URL
            ]
        );
    }

    /**
     * Set custom columns for the scheduled content list.
     *
     * @since 1.0.0
     * @param array $columns Array of columns.
     * @return array Modified array of columns.
     */
    public function set_custom_columns($columns) {
        $new_columns = [];

        // Insert columns after title.
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            if ('title' === $key) {
                $new_columns['shortcode'] = __('Shortcode', 'content-scheduler-pro');
                $new_columns['schedule']  = __('Schedule', 'content-scheduler-pro');
                $new_columns['status']    = __('Status', 'content-scheduler-pro');
            }
        }

        return $new_columns;
    }

    /**
     * Display custom column content.
     *
     * @since 1.0.0
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function display_custom_column($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                $shortcode = '[content_scheduler_pro id="' . $post_id . '"]';
                echo '<input type="text" readonly="readonly" value="' . esc_attr($shortcode) . '" class="csp-shortcode-input" onclick="this.select();" />';
                echo '<button type="button" class="button csp-copy-shortcode" data-clipboard-text="' . esc_attr($shortcode) . '">' . esc_html__('Copy', 'content-scheduler-pro') . '</button>';
                break;

            case 'schedule':
                $start_date = get_post_meta($post_id, '_csp_start_date', true);
                $start_time = get_post_meta($post_id, '_csp_start_time', true);
                $end_date   = get_post_meta($post_id, '_csp_end_date', true);
                $end_time   = get_post_meta($post_id, '_csp_end_time', true);

                if ($start_date && $start_time) {
                    $start_datetime = date_i18n(
                        get_option('date_format') . ' ' . get_option('time_format'),
                        strtotime($start_date . ' ' . $start_time)
                    );

                    echo '<strong>' . esc_html__('Start:', 'content-scheduler-pro') . '</strong> ' . esc_html($start_datetime) . '<br>';
                }

                if ($end_date && $end_time) {
                    $end_datetime = date_i18n(
                        get_option('date_format') . ' ' . get_option('time_format'),
                        strtotime($end_date . ' ' . $end_time)
                    );

                    echo '<strong>' . esc_html__('End:', 'content-scheduler-pro') . '</strong> ' . esc_html($end_datetime);
                }
                break;

            case 'status':          
                // Get date/time values
                $start_date = get_post_meta($post_id, '_csp_start_date', true);
                $start_time = get_post_meta($post_id, '_csp_start_time', true);
                $end_date   = get_post_meta($post_id, '_csp_end_date', true);
                $end_time   = get_post_meta($post_id, '_csp_end_time', true);
                
                // Instead of trying to use timezone for comparisons, let's use UTC timestamps
                // Simpler and more reliable
                $current_time = time(); // UTC timestamp
                
                $start_timestamp = strtotime($start_date . ' ' . $start_time);
                $end_timestamp = strtotime($end_date . ' ' . $end_time);

                if ($current_time < $start_timestamp) {
                    echo '<span class="csp-status csp-status-scheduled">' . esc_html__('Scheduled', 'content-scheduler-pro') . '</span>';
                } elseif ($current_time >= $start_timestamp && $current_time < $end_timestamp) {
                    echo '<span class="csp-status csp-status-active">' . esc_html__('Active', 'content-scheduler-pro') . '</span>';
                } else {
                    echo '<span class="csp-status csp-status-expired">' . esc_html__('Expired', 'content-scheduler-pro') . '</span>';
                }
                break;
        }
    }

    /**
     * Modify row actions for the scheduled content list.
     *
     * @since 1.0.0
     * @param array    $actions Row actions.
     * @param \WP_Post $post    Post object.
     * @return array Modified row actions.
     */
    public function modify_row_actions($actions, $post) {
        if ('scheduled_content' === $post->post_type) {
            // Add a preview action that shows the content in a modal.
            $actions['csp_preview'] = sprintf(
                '<a href="#" class="csp-preview-link" data-id="%d">%s</a>',
                $post->ID,
                esc_html__('Preview Content', 'content-scheduler-pro')
            );
        }

        return $actions;
    }
    
    /**
     * AJAX handler for previewing content.
     *
     * @since 1.0.0
     */
    public function ajax_preview_content() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csp_admin_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'content-scheduler-pro')]);
            return;
        }

        // Get post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid content ID.', 'content-scheduler-pro')]);
            return;
        }

        // Get post
        $post = get_post($post_id);
        if (!$post || 'scheduled_content' !== $post->post_type) {
            wp_send_json_error(['message' => __('Content not found.', 'content-scheduler-pro')]);
            return;
        }

        // Get scheduling data for informational display
        $start_date = get_post_meta($post_id, '_csp_start_date', true);
        $start_time = get_post_meta($post_id, '_csp_start_time', true);
        $end_date   = get_post_meta($post_id, '_csp_end_date', true);
        $end_time   = get_post_meta($post_id, '_csp_end_time', true);

        // Format dates for display
        $start_formatted = date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($start_date . ' ' . $start_time)
        );
        
        $end_formatted = date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($end_date . ' ' . $end_time)
        );

        // Prepare content with scheduling info
        $content = '<h2>' . esc_html($post->post_title) . '</h2>';
        $content .= '<div class="csp-preview-schedule">';
        $content .= '<p><strong>' . esc_html__('Active from:', 'content-scheduler-pro') . '</strong> ' . esc_html($start_formatted) . '</p>';
        $content .= '<p><strong>' . esc_html__('Active until:', 'content-scheduler-pro') . '</strong> ' . esc_html($end_formatted) . '</p>';
        $content .= '</div>';
        $content .= '<div class="csp-preview-content-wrapper">';
        $content .= apply_filters('the_content', $post->post_content);
        $content .= '</div>';

        wp_send_json_success([
            'content' => $content,
        ]);
    }
}