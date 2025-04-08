<?php
/**
 * Handle meta boxes for the plugin.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for handling meta boxes.
 *
 * @since 1.0.0
 */
class MetaBoxes {

    /**
     * Settings instance.
     *
     * @since 1.0.0
     * @var Settings|null
     */
    private $settings = null;
    
    /**
     * Get the settings instance.
     *
     * @since 1.0.0
     * @return Settings Settings instance.
     */
    private function get_settings() {
        if (null === $this->settings) {
            $this->settings = new Settings();
        }
        
        return $this->settings;
    }

    /**
     * Add meta boxes to the admin edit screen.
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'csp_scheduling_meta_box',
            __('Scheduling Options', 'content-scheduler-pro'),
            [$this, 'render_scheduling_meta_box'],
            'scheduled_content',
            'normal',
            'high'
        );

        add_meta_box(
            'csp_shortcode_meta_box',
            __('Shortcode', 'content-scheduler-pro'),
            [$this, 'render_shortcode_meta_box'],
            'scheduled_content',
            'side',
            'high'
        );
    }

    /**
     * Render the scheduling meta box.
     *
     * @since 1.0.0
     * @param \WP_Post $post The post object.
     */
    public function render_scheduling_meta_box($post) {
        // Add nonce for security and authentication.
        wp_nonce_field('csp_scheduling_nonce_action', 'csp_scheduling_nonce');

        // Retrieve existing values from the database.
        $start_date = get_post_meta($post->ID, '_csp_start_date', true);
        $start_time = get_post_meta($post->ID, '_csp_start_time', true);
        $end_date   = get_post_meta($post->ID, '_csp_end_date', true);
        $end_time   = get_post_meta($post->ID, '_csp_end_time', true);

        // Set default values if empty.
        if (empty($start_date)) {
            $start_date = date('Y-m-d');
        }
        if (empty($start_time)) {
            $start_time = '00:00';
        }
        if (empty($end_date)) {
            $end_date = date('Y-m-d', strtotime('+1 week'));
        }
        if (empty($end_time)) {
            $end_time = '23:59';
        }

        // Get timezone
        $timezone = $this->get_settings()->get_timezone();
        
        // Create DateTime objects with timezone
        $timezone_object = null;
        try {
            $timezone_object = new \DateTimeZone($timezone);
        } catch (\Exception $e) {
            // If timezone is invalid, fall back to UTC
            $timezone_object = new \DateTimeZone('UTC');
        }
        $current_datetime = new \DateTime('now', $timezone_object);
        
        // Parse start and end times with timezone
        $start_datetime = \DateTime::createFromFormat('Y-m-d H:i', $start_date . ' ' . $start_time, $timezone_object);
        $end_datetime = \DateTime::createFromFormat('Y-m-d H:i', $end_date . ' ' . $end_time, $timezone_object);
        
        // Convert to timestamp for comparison
        $current_time = $current_datetime->getTimestamp();
        $start_timestamp = $start_datetime ? $start_datetime->getTimestamp() : 0;
        $end_timestamp = $end_datetime ? $end_datetime->getTimestamp() : 0;

        if ($current_time < $start_timestamp) {
            $status = '<span class="csp-status csp-status-scheduled">' . __('Scheduled', 'content-scheduler-pro') . '</span>';
        } elseif ($current_time >= $start_timestamp && $current_time < $end_timestamp) {
            $status = '<span class="csp-status csp-status-active">' . __('Active', 'content-scheduler-pro') . '</span>';
        } else {
            $status = '<span class="csp-status csp-status-expired">' . __('Expired', 'content-scheduler-pro') . '</span>';
        }

        // Display the meta box HTML.
        ?>
        <div class="csp-scheduling-container">
            <div class="csp-status-container">
                <p><?php _e('Current Status:', 'content-scheduler-pro'); ?> <?php echo $status; ?></p>
            </div>

            <table class="form-table csp-scheduling-table">
                <tr>
                    <th scope="row">
                        <label for="csp_start_date"><?php _e('Start Date', 'content-scheduler-pro'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="csp_start_date" name="csp_start_date" value="<?php echo esc_attr($start_date); ?>" class="csp-date-input" />
                    </td>
                    <th scope="row">
                        <label for="csp_start_time"><?php _e('Start Time', 'content-scheduler-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="csp_start_time" name="csp_start_time" value="<?php echo esc_attr($start_time); ?>" class="csp-time-input" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="csp_end_date"><?php _e('End Date', 'content-scheduler-pro'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="csp_end_date" name="csp_end_date" value="<?php echo esc_attr($end_date); ?>" class="csp-date-input" />
                    </td>
                    <th scope="row">
                        <label for="csp_end_time"><?php _e('End Time', 'content-scheduler-pro'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="csp_end_time" name="csp_end_time" value="<?php echo esc_attr($end_time); ?>" class="csp-time-input" />
                    </td>
                </tr>
            </table>
            <p class="description">
                <?php 
                $timezone = $this->get_settings()->get_timezone();
                printf(
                    /* translators: %s: timezone name */
                    __('Set the start and end dates/times for when this content should be displayed. Using timezone: %s', 'content-scheduler-pro'),
                    '<strong>' . esc_html($timezone) . '</strong>'
                ); 
                ?>
                (<a href="<?php echo esc_url(admin_url('edit.php?post_type=scheduled_content&page=csp_settings')); ?>"><?php _e('Change', 'content-scheduler-pro'); ?></a>)
            </p>
        </div>
        <?php
    }

    /**
     * Render the shortcode meta box.
     *
     * @since 1.0.0
     * @param \WP_Post $post The post object.
     */
    public function render_shortcode_meta_box($post) {
        $shortcode = '[content_scheduler_pro id="' . $post->ID . '"]';
        ?>
        <div class="csp-shortcode-container">
            <p><?php _e('Use this shortcode to display the scheduled content:', 'content-scheduler-pro'); ?></p>
            <div class="csp-shortcode-field">
                <input type="text" readonly="readonly" value="<?php echo esc_attr($shortcode); ?>" class="csp-shortcode-input" onclick="this.select();" />
                <button type="button" class="button csp-copy-shortcode" data-clipboard-text="<?php echo esc_attr($shortcode); ?>">
                    <?php _e('Copy', 'content-scheduler-pro'); ?>
                </button>
            </div>
            <p class="description">
                <?php _e('Place this shortcode in any post, page, or widget where you want the scheduled content to appear.', 'content-scheduler-pro'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save the meta box data.
     *
     * @since 1.0.0
     * @param int      $post_id The post ID.
     * @param \WP_Post $post    The post object.
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if user has permissions to save data.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if not an autosave.
        if (wp_is_post_autosave($post_id)) {
            return;
        }

        // Check if not a revision.
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Verify nonce.
        if (!isset($_POST['csp_scheduling_nonce']) || !wp_verify_nonce($_POST['csp_scheduling_nonce'], 'csp_scheduling_nonce_action')) {
            return;
        }

        // Update the meta fields.
        if (isset($_POST['csp_start_date'])) {
            update_post_meta($post_id, '_csp_start_date', sanitize_text_field($_POST['csp_start_date']));
        }

        if (isset($_POST['csp_start_time'])) {
            update_post_meta($post_id, '_csp_start_time', sanitize_text_field($_POST['csp_start_time']));
        }

        if (isset($_POST['csp_end_date'])) {
            update_post_meta($post_id, '_csp_end_date', sanitize_text_field($_POST['csp_end_date']));
        }

        if (isset($_POST['csp_end_time'])) {
            update_post_meta($post_id, '_csp_end_time', sanitize_text_field($_POST['csp_end_time']));
        }
    }
}