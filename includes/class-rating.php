<?php
/**
 * Handle plugin rating notices.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for managing rating notices.
 *
 * @since 1.0.0
 */
class Rating {

    /**
     * Initialize the rating functionality.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Only load for admin users
        if (!is_admin()) {
            return;
        }

        // Add hooks for rating notices
        add_action('admin_notices', [$this, 'display_rating_notice']);
        add_action('wp_ajax_csp_dismiss_rating', [$this, 'dismiss_rating_notice']);
    }

    /**
     * Check if the rating notice should be displayed.
     *
     * @since 1.0.0
     * @return bool Whether the notice should be displayed.
     */
    private function should_display_notice() {
        // Don't show on plugin activation
        if (isset($_GET['activate']) || isset($_GET['activate-multi'])) {
            return false;
        }

        // Get current screen
        $screen = get_current_screen();
        
        // Only show on these pages
        $show_on_screens = [
            'dashboard',
            'plugins',
            'edit-scheduled_content',
            'scheduled_content',
        ];

        if (!$screen || !in_array($screen->id, $show_on_screens, true)) {
            return false;
        }

        // Check if user dismissed the notice permanently
        $dismissed = get_option('csp_rating_dismissed');
        if ($dismissed === 'permanent') {
            return false;
        }

        // Check if user dismissed temporarily
        $remind_later = get_option('csp_rating_remind_later');
        if ($remind_later && time() < $remind_later) {
            return false;
        }

        // Check how long the plugin has been active
        $activation_time = get_option('csp_activation_time');
        if (!$activation_time) {
            // If activation time is not set (unlikely), set it now
            $activation_time = time();
            update_option('csp_activation_time', $activation_time);
            return false;
        }

        // Show notice after 7 days of activation (604800 seconds)
        $wait_time = 604800;
        
        // Also make sure at least 3 scheduled contents have been created
        $content_count = wp_count_posts('scheduled_content');
        $has_enough_content = $content_count && 
                            isset($content_count->publish) && 
                            $content_count->publish >= 3;

        return (time() - $activation_time >= $wait_time) && $has_enough_content;
    }

    /**
     * Display the rating notice.
     *
     * @since 1.0.0
     */
    public function display_rating_notice() {
        if (!$this->should_display_notice()) {
            return;
        }

        // Get number of published scheduled contents
        $content_count = wp_count_posts('scheduled_content');
        $content_count = isset($content_count->publish) ? $content_count->publish : 0;
        
        ?>
        <div class="notice notice-info csp-rating-notice is-dismissible" id="csp-rating-notice">
            <div class="csp-rating-notice-content">
                <h3><?php esc_html_e('Enjoying Content Scheduler Pro?', 'content-scheduler-pro'); ?></h3>
                <p>
                    <?php 
                    printf(
                        /* translators: %d: number of scheduled content items created */
                        esc_html__('You\'ve created %d scheduled content items with Content Scheduler Pro! If you find this plugin useful, would you mind taking a moment to rate it? It really helps to support the plugin and helps others discover it too!', 'content-scheduler-pro'),
                        $content_count
                    ); 
                    ?>
                </p>
                <div class="csp-rating-actions">
                    <a href="https://wordpress.org/support/plugin/content-scheduler-pro/reviews/#new-post" class="button button-primary" target="_blank" rel="noopener noreferrer" id="csp-rate-now">
                        <?php esc_html_e('Rate Now', 'content-scheduler-pro'); ?>
                    </a>
                    <button class="button button-secondary" id="csp-remind-later">
                        <?php esc_html_e('Remind Me Later', 'content-scheduler-pro'); ?>
                    </button>
                    <button class="button button-link" id="csp-dismiss-permanently">
                        <?php esc_html_e('I\'ve Already Rated It', 'content-scheduler-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Handle dismiss permanently
                $('#csp-dismiss-permanently').on('click', function() {
                    dismissRatingNotice('permanent');
                });

                // Handle remind later
                $('#csp-remind-later').on('click', function() {
                    dismissRatingNotice('later');
                });

                // Handle already rated (same as dismiss permanently)
                $('#csp-rate-now').on('click', function() {
                    dismissRatingNotice('permanent');
                });

                // Handle WordPress dismiss button click
                $(document).on('click', '#csp-rating-notice .notice-dismiss', function() {
                    dismissRatingNotice('later');
                });

                // Function to dismiss notice via AJAX
                function dismissRatingNotice(type) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'csp_dismiss_rating',
                            type: type,
                            nonce: '<?php echo wp_create_nonce('csp_rating_nonce'); ?>'
                        },
                        success: function() {
                            $('#csp-rating-notice').slideUp();
                        }
                    });
                }
            });
        </script>
        <style>
            .csp-rating-notice {
                padding: 15px;
                border-left-color: #2271b1;
            }
            .csp-rating-notice-content h3 {
                margin-top: 0;
                margin-bottom: 10px;
            }
            .csp-rating-actions {
                margin-top: 15px;
                display: flex;
                gap: 8px;
                align-items: center;
                flex-wrap: wrap;
            }
            @media screen and (max-width: 782px) {
                .csp-rating-actions {
                    flex-direction: column;
                    align-items: flex-start;
                }
                .csp-rating-actions .button {
                    margin-bottom: 10px;
                }
            }
        </style>
        <?php
    }

    /**
     * Handle AJAX request to dismiss the rating notice.
     *
     * @since 1.0.0
     */
    public function dismiss_rating_notice() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'csp_rating_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $dismiss_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'later';

        if ($dismiss_type === 'permanent') {
            // Permanently dismiss
            update_option('csp_rating_dismissed', 'permanent');
        } else {
            // Remind later (30 days)
            $remind_time = time() + (30 * DAY_IN_SECONDS);
            update_option('csp_rating_remind_later', $remind_time);
        }

        wp_send_json_success();
    }
}