<?php
/**
 * Handle shortcode functionality for the plugin.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for handling shortcodes.
 *
 * @since 1.0.0
 */
class Shortcode {

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
     * Render the shortcode.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(
            [
                'id' => 0,
            ],
            $atts,
            'content_scheduler_pro'
        );

        $post_id = absint($atts['id']);

        // If no post ID provided, return empty.
        if (empty($post_id)) {
            return '';
        }

        // Check if the post exists and is published.
        $post = get_post($post_id);
        if (!$post || 'scheduled_content' !== $post->post_type || 'publish' !== $post->post_status) {
            return '';
        }

        // Get scheduling data.
        $start_date = get_post_meta($post_id, '_csp_start_date', true);
        $start_time = get_post_meta($post_id, '_csp_start_time', true);
        $end_date   = get_post_meta($post_id, '_csp_end_date', true);
        $end_time   = get_post_meta($post_id, '_csp_end_time', true);

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

        // Check if current time is within the scheduled period.
        if ($current_time < $start_timestamp || $current_time >= $end_timestamp) {
            return '';
        }

        // Apply filters to allow third-party extensions.
        $content = apply_filters('csp_before_content', '', $post_id);
        $content .= apply_filters('the_content', $post->post_content);
        $content = apply_filters('csp_after_content', $content, $post_id);

        // Wrap the content in a div with a class for styling.
        return sprintf(
            '<div class="content-scheduler-pro-content" data-csp-id="%1$d">%2$s</div>',
            $post_id,
            $content
        );
    }
}