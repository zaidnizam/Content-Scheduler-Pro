<?php
/**
 * Handle plugin settings.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for managing plugin settings.
 *
 * @since 1.0.0
 */
class Settings {

    /**
     * Option name for settings.
     *
     * @since 1.0.0
     * @var string
     */
    private $option_name = 'csp_settings';

    /**
     * Settings data.
     *
     * @since 1.0.0
     * @var array
     */
    private $settings = [];

    /**
     * Initialize the class.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Add settings page
        add_action('admin_menu', [$this, 'add_settings_page']);
        
        // Add help tab
        add_action('admin_head', [$this, 'add_help_tab']);
        
        // Load settings
        $this->settings = get_option($this->option_name, $this->get_default_settings());
    }

    /**
     * Get default settings.
     *
     * @since 1.0.0
     * @return array Default settings.
     */
    private function get_default_settings() {
        return [
            'timezone_type' => 'wordpress',  // 'wordpress' or 'custom'
            'timezone'      => '', // Custom timezone
        ];
    }

    /**
     * Get a setting value.
     *
     * @since 1.0.0
     * @param string $key     Setting key.
     * @param mixed  $default Default value if setting doesn't exist.
     * @return mixed Setting value.
     */
    public function get_setting($key, $default = '') {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    /**
     * Get the timezone to use for scheduling.
     *
     * @since 1.0.0
     * @return string Timezone string.
     */
    public function get_timezone() {
        $timezone_type = $this->get_setting('timezone_type', 'wordpress');
        
        if ($timezone_type === 'wordpress') {
            // Use WordPress timezone
            $timezone_string = get_option('timezone_string');
            
            if (empty($timezone_string)) {
                // If not set, use UTC offset
                $utc_offset = get_option('gmt_offset', 0);
                
                // Convert to timezone string that's compatible with DateTimeZone
                if ($utc_offset === 0) {
                    return 'UTC';
                }
                
                // Format UTC offset properly for DateTimeZone
                $offset_hours = (int) $utc_offset;
                $offset_minutes = abs(($utc_offset - $offset_hours) * 60);
                $offset_format = sprintf('%+03d:%02d', $offset_hours, $offset_minutes);
                
                $timezone_str = 'UTC' . $offset_format;
                
                // Verify if the timezone string is valid, otherwise fall back to UTC
                if (!$this->is_valid_timezone($timezone_str)) {
                    return 'UTC';
                }
                
                return $timezone_str;
            }
            
            return $timezone_string;
        } else {
            // Use custom timezone
            $custom_timezone = $this->get_setting('timezone', '');
            
            if (empty($custom_timezone) || !$this->is_valid_timezone($custom_timezone)) {
                return 'UTC';
            }
            
            return $custom_timezone;
        }
    }

    /**
     * Register plugin settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        register_setting(
            'csp_settings_group',
            $this->option_name,
            [$this, 'validate_settings']
        );

        add_settings_section(
            'csp_timezone_section',
            __('Timezone Settings', 'content-scheduler-pro'),
            [$this, 'render_timezone_section'],
            'csp_settings'
        );

        add_settings_field(
            'timezone_type',
            __('Timezone Source', 'content-scheduler-pro'),
            [$this, 'render_timezone_type_field'],
            'csp_settings',
            'csp_timezone_section'
        );

        add_settings_field(
            'timezone',
            __('Custom Timezone', 'content-scheduler-pro'),
            [$this, 'render_timezone_field'],
            'csp_settings',
            'csp_timezone_section'
        );
    }

    /**
     * Validate settings.
     *
     * @since 1.0.0
     * @param array $input Input settings.
     * @return array Validated settings.
     */
    public function validate_settings($input) {
        $validated = [];
        
        // Timezone type (wordpress or custom)
        $validated['timezone_type'] = isset($input['timezone_type']) && in_array($input['timezone_type'], ['wordpress', 'custom'])
            ? $input['timezone_type']
            : 'wordpress';
        
        // Custom timezone
        $validated['timezone'] = isset($input['timezone']) ? sanitize_text_field($input['timezone']) : '';
        
        return $validated;
    }

    /**
     * Add settings page.
     *
     * @since 1.0.0
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=scheduled_content',
            __('Settings', 'content-scheduler-pro'),
            __('Settings', 'content-scheduler-pro'),
            'manage_options',
            'csp_settings',
            [$this, 'render_settings_page']
        );
        
        // Add How to Use page
        add_submenu_page(
            'edit.php?post_type=scheduled_content', 
            __('How to Use', 'content-scheduler-pro'),
            __('How to Use', 'content-scheduler-pro'),
            'edit_posts',
            'csp_how_to_use',
            [$this, 'render_how_to_use_page']
        );
    }

    /**
     * Render settings page.
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap csp-settings-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('csp_settings_group');
                do_settings_sections('csp_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render how to use page.
     *
     * @since 1.0.0
     */
    public function render_how_to_use_page() {
        ?>
        <div class="wrap csp-how-to-use-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="csp-how-to-use-content">
                <div class="csp-how-to-use-section">
                    <h2><?php _e('Welcome to Content Scheduler Pro! 👋', 'content-scheduler-pro'); ?></h2>
                    <p><?php _e('This guide will walk you through scheduling content to appear and disappear automatically on your WordPress site. It\'s super easy!', 'content-scheduler-pro'); ?></p>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Step 1: Create Your Content', 'content-scheduler-pro'); ?></h3>
                    <div class="csp-instruction-wrapper">
                        <div class="csp-instruction-image">
                            <img src="<?php echo CSP_PLUGIN_URL; ?>assets/images/create-content.png" alt="Create Content" />
                        </div>
                        <div class="csp-instruction-text">
                            <p><?php _e('Start by clicking "Add New" under the Content Scheduler menu.', 'content-scheduler-pro'); ?></p>
                            <p><?php _e('Give your content a title and use the WordPress editor to create your content just like you would with a regular post. Add text, images, buttons - whatever you need!', 'content-scheduler-pro'); ?></p>
                            <p><strong><?php _e('Pro Tip:', 'content-scheduler-pro'); ?></strong> <?php _e('Make your scheduled content stand out by adding bright colors or eye-catching images.', 'content-scheduler-pro'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Step 2: Set Your Schedule', 'content-scheduler-pro'); ?></h3>
                    <div class="csp-instruction-wrapper">
                        <div class="csp-instruction-text">
                            <p><?php _e('Below the content editor, you\'ll find the "Scheduling Options" box.', 'content-scheduler-pro'); ?></p>
                            <p><?php _e('Set when your content should start appearing and when it should disappear:', 'content-scheduler-pro'); ?></p>
                            <ul>
                                <li><?php _e('Start Date & Time: When your content will begin showing', 'content-scheduler-pro'); ?></li>
                                <li><?php _e('End Date & Time: When your content will stop showing', 'content-scheduler-pro'); ?></li>
                            </ul>
                            <p><strong><?php _e('Remember:', 'content-scheduler-pro'); ?></strong> <?php _e('The time is based on the timezone in your settings.', 'content-scheduler-pro'); ?></p>
                        </div>
                        <div class="csp-instruction-image">
                            <img src="<?php echo CSP_PLUGIN_URL; ?>assets/images/schedule-settings.png" alt="Schedule Settings" />
                        </div>
                    </div>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Step 3: Get Your Shortcode', 'content-scheduler-pro'); ?></h3>
                    <div class="csp-instruction-wrapper">
                        <div class="csp-instruction-image">
                            <img src="<?php echo CSP_PLUGIN_URL; ?>assets/images/shortcode-box.png" alt="Shortcode Box" />
                        </div>
                        <div class="csp-instruction-text">
                            <p><?php _e('On the right side of the screen, you\'ll see a "Shortcode" box with your unique shortcode.', 'content-scheduler-pro'); ?></p>
                            <p><?php _e('Click the "Copy" button to copy it to your clipboard. It will look something like:', 'content-scheduler-pro'); ?></p>
                            <p><code>[content_scheduler_pro id="123"]</code></p>
                            <p><?php _e('Don\'t forget to save your content by clicking the "Publish" button!', 'content-scheduler-pro'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Step 4: Place Your Shortcode', 'content-scheduler-pro'); ?></h3>
                    <div class="csp-instruction-wrapper">
                        <div class="csp-instruction-text">
                            <p><?php _e('Now that you have your shortcode, you can place it anywhere on your site:', 'content-scheduler-pro'); ?></p>
                            <ul>
                                <li><?php _e('In a post or page', 'content-scheduler-pro'); ?></li>
                                <li><?php _e('In a widget area', 'content-scheduler-pro'); ?></li>
                                <li><?php _e('In your theme\'s template files (for developers)', 'content-scheduler-pro'); ?></li>
                            </ul>
                            <p><?php _e('Just paste your shortcode where you want the scheduled content to appear.', 'content-scheduler-pro'); ?></p>
                        </div>
                        <div class="csp-instruction-image">
                            <img src="<?php echo CSP_PLUGIN_URL; ?>assets/images/place-shortcode.png" alt="Place Shortcode" />
                        </div>
                    </div>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Step 5: Manage Your Scheduled Content', 'content-scheduler-pro'); ?></h3>
                    <p><?php _e('You can view all your scheduled content by going to Content Scheduler → All Scheduled Contents.', 'content-scheduler-pro'); ?></p>
                    <p><?php _e('From here, you can:', 'content-scheduler-pro'); ?></p>
                    <ul>
                        <li><?php _e('See which content is active, scheduled, or expired', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Edit existing content or schedule', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Preview how your content will look', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Quick-copy shortcodes', 'content-scheduler-pro'); ?></li>
                    </ul>
                </div>
                
                <div class="csp-how-to-use-section">
                    <h3><?php _e('Questions & Troubleshooting', 'content-scheduler-pro'); ?></h3>
                    <p><strong><?php _e('My content isn\'t showing up!', 'content-scheduler-pro'); ?></strong></p>
                    <ul>
                        <li><?php _e('Check if the current time is between your start and end times', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Make sure your timezone settings are correct', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Verify that you published your content (not just saved as draft)', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Try clearing your cache if your site uses caching', 'content-scheduler-pro'); ?></li>
                    </ul>
                    
                    <p><strong><?php _e('The shortcode just shows as text on my page', 'content-scheduler-pro'); ?></strong></p>
                    <ul>
                        <li><?php _e('Make sure the plugin is activated', 'content-scheduler-pro'); ?></li>
                        <li><?php _e('Try using a different block or widget to add the shortcode', 'content-scheduler-pro'); ?></li>
                    </ul>
                </div>
                
                <div class="csp-how-to-use-section csp-how-to-use-footer">
                    <p><?php _e('That\'s it! Now you can schedule content to appear and disappear automatically across your site.', 'content-scheduler-pro'); ?></p>
                    <p><?php _e('If you love using Content Scheduler Pro, please consider leaving us a review!', 'content-scheduler-pro'); ?></p>
                    <p>
                        <a href="https://wordpress.org/support/plugin/content-scheduler-pro/reviews/#new-post" class="button button-primary" target="_blank">
                            <?php _e('Leave a Review', 'content-scheduler-pro'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
            .csp-how-to-use-wrap {
                max-width: 1200px;
                margin: 20px auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .csp-how-to-use-content {
                padding: 20px 30px 30px;
            }
            
            .csp-how-to-use-section {
                margin-bottom: 30px;
                border-bottom: 1px solid #f0f0f0;
                padding-bottom: 25px;
            }
            
            .csp-how-to-use-section:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
            
            .csp-how-to-use-section h2 {
                font-size: 26px;
                margin-top: 0;
                color: #2271b1;
            }
            
            .csp-how-to-use-section h3 {
                font-size: 20px;
                margin-top: 0;
                margin-bottom: 20px;
                color: #2271b1;
            }
            
            .csp-instruction-wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 30px;
                align-items: center;
            }
            
            .csp-instruction-text {
                flex: 1;
                min-width: 300px;
            }
            
            .csp-instruction-image {
                flex: 1;
                min-width: 300px;
                text-align: center;
            }
            
            .csp-instruction-image img {
                max-width: 100%;
                height: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .csp-how-to-use-footer {
                text-align: center;
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-top: 40px;
            }
            
            @media screen and (max-width: 782px) {
                .csp-instruction-wrapper {
                    flex-direction: column;
                }
                
                .csp-instruction-image {
                    order: 1;
                }
                
                .csp-instruction-text {
                    order: 2;
                }
            }
        </style>
        <?php
    }

    /**
     * Render timezone section.
     *
     * @since 1.0.0
     */
    public function render_timezone_section() {
        echo '<p>' . esc_html__('Configure the timezone settings for scheduled content.', 'content-scheduler-pro') . '</p>';
    }

    /**
     * Render timezone type field.
     *
     * @since 1.0.0
     */
    public function render_timezone_type_field() {
        $timezone_type = $this->get_setting('timezone_type', 'wordpress');
        ?>
        <div class="csp-timezone-type-field">
            <label>
                <input type="radio" name="<?php echo esc_attr($this->option_name); ?>[timezone_type]" value="wordpress" <?php checked($timezone_type, 'wordpress'); ?> />
                <?php _e('Use WordPress Timezone Setting', 'content-scheduler-pro'); ?>
                <span class="description">
                    <?php 
                    $wp_timezone = get_option('timezone_string');
                    if (empty($wp_timezone)) {
                        $utc_offset = get_option('gmt_offset', 0);
                        $wp_timezone = sprintf(__('UTC %+d', 'content-scheduler-pro'), $utc_offset);
                    }
                    printf(
                        /* translators: %s: WordPress timezone setting */
                        __('(Currently: %s)', 'content-scheduler-pro'),
                        esc_html($wp_timezone)
                    ); 
                    ?>
                </span>
            </label>
            <br>
            <label>
                <input type="radio" name="<?php echo esc_attr($this->option_name); ?>[timezone_type]" value="custom" <?php checked($timezone_type, 'custom'); ?> />
                <?php _e('Use Custom Timezone', 'content-scheduler-pro'); ?>
            </label>
        </div>
        <p class="description">
            <?php _e('Select whether to use the WordPress timezone setting or a custom timezone for scheduled content.', 'content-scheduler-pro'); ?>
        </p>
        <?php
    }

    /**
     * Render timezone field.
     *
     * @since 1.0.0
     */
    public function render_timezone_field() {
        $timezone = $this->get_setting('timezone', '');
        $timezone_options = $this->get_timezone_options();
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[timezone]" id="csp-timezone-select" class="regular-text">
            <option value=""><?php _e('Select a timezone', 'content-scheduler-pro'); ?></option>
            <?php foreach ($timezone_options as $group => $zones) : ?>
                <optgroup label="<?php echo esc_attr($group); ?>">
                    <?php foreach ($zones as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($timezone, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select a timezone to use for scheduling content. This will only be used if "Use Custom Timezone" is selected above.', 'content-scheduler-pro'); ?>
        </p>
        <script>
            jQuery(document).ready(function($) {
                // Show/hide timezone dropdown based on timezone type
                function toggleTimezoneField() {
                    const timezoneType = $('input[name="<?php echo esc_attr($this->option_name); ?>[timezone_type]"]:checked').val();
                    const timezoneField = $('#csp-timezone-select').closest('tr');
                    
                    if (timezoneType === 'custom') {
                        timezoneField.show();
                    } else {
                        timezoneField.hide();
                    }
                }
                
                // Initial toggle
                toggleTimezoneField();
                
                // Toggle on change
                $('input[name="<?php echo esc_attr($this->option_name); ?>[timezone_type]"]').on('change', toggleTimezoneField);
            });
        </script>
        <?php
    }

    /**
     * Get timezone options.
     *
     * @since 1.0.0
     * @return array Timezone options.
     */
    private function get_timezone_options() {
        $zones = [];
        
        // UTC
        $zones['UTC'] = [
            'UTC' => __('UTC (Coordinated Universal Time)', 'content-scheduler-pro'),
        ];
        
        // Africa
        $africa = [];
        $africa['Africa/Abidjan'] = 'Abidjan';
        $africa['Africa/Accra'] = 'Accra';
        $africa['Africa/Addis_Ababa'] = 'Addis Ababa';
        $africa['Africa/Algiers'] = 'Algiers';
        $africa['Africa/Cairo'] = 'Cairo';
        $africa['Africa/Casablanca'] = 'Casablanca';
        $africa['Africa/Johannesburg'] = 'Johannesburg';
        $africa['Africa/Lagos'] = 'Lagos';
        $africa['Africa/Nairobi'] = 'Nairobi';
        $africa['Africa/Tunis'] = 'Tunis';
        $zones[__('Africa', 'content-scheduler-pro')] = $africa;
        
        // America
        $america = [];
        $america['America/Anchorage'] = 'Anchorage';
        $america['America/Argentina/Buenos_Aires'] = 'Buenos Aires';
        $america['America/Bogota'] = 'Bogota';
        $america['America/Chicago'] = 'Chicago';
        $america['America/Denver'] = 'Denver';
        $america['America/Halifax'] = 'Halifax';
        $america['America/Lima'] = 'Lima';
        $america['America/Los_Angeles'] = 'Los Angeles';
        $america['America/Mexico_City'] = 'Mexico City';
        $america['America/New_York'] = 'New York';
        $america['America/Phoenix'] = 'Phoenix';
        $america['America/Santiago'] = 'Santiago';
        $america['America/Sao_Paulo'] = 'Sao Paulo';
        $america['America/Toronto'] = 'Toronto';
        $america['America/Vancouver'] = 'Vancouver';
        $zones[__('America', 'content-scheduler-pro')] = $america;
        
        // Asia
        $asia = [];
        $asia['Asia/Baghdad'] = 'Baghdad';
        $asia['Asia/Bangkok'] = 'Bangkok';
        $asia['Asia/Dhaka'] = 'Dhaka';
        $asia['Asia/Dubai'] = 'Dubai';
        $asia['Asia/Hong_Kong'] = 'Hong Kong';
        $asia['Asia/Istanbul'] = 'Istanbul';
        $asia['Asia/Jakarta'] = 'Jakarta';
        $asia['Asia/Jerusalem'] = 'Jerusalem';
        $asia['Asia/Karachi'] = 'Karachi';
        $asia['Asia/Kolkata'] = 'Kolkata';
        $asia['Asia/Manila'] = 'Manila';
        $asia['Asia/Seoul'] = 'Seoul';
        $asia['Asia/Shanghai'] = 'Shanghai';
        $asia['Asia/Singapore'] = 'Singapore';
        $asia['Asia/Tokyo'] = 'Tokyo';
        $zones[__('Asia', 'content-scheduler-pro')] = $asia;
        
        // Australia & Pacific
        $australia = [];
        $australia['Australia/Adelaide'] = 'Adelaide';
        $australia['Australia/Brisbane'] = 'Brisbane';
        $australia['Australia/Melbourne'] = 'Melbourne';
        $australia['Australia/Perth'] = 'Perth';
        $australia['Australia/Sydney'] = 'Sydney';
        $australia['Pacific/Auckland'] = 'Auckland';
        $australia['Pacific/Fiji'] = 'Fiji';
        $australia['Pacific/Guam'] = 'Guam';
        $australia['Pacific/Honolulu'] = 'Honolulu';
        $australia['Pacific/Tahiti'] = 'Tahiti';
        $zones[__('Australia & Pacific', 'content-scheduler-pro')] = $australia;
        
        // Europe
        $europe = [];
        $europe['Europe/Amsterdam'] = 'Amsterdam';
        $europe['Europe/Athens'] = 'Athens';
        $europe['Europe/Berlin'] = 'Berlin';
        $europe['Europe/Brussels'] = 'Brussels';
        $europe['Europe/Dublin'] = 'Dublin';
        $europe['Europe/Helsinki'] = 'Helsinki';
        $europe['Europe/Lisbon'] = 'Lisbon';
        $europe['Europe/London'] = 'London';
        $europe['Europe/Madrid'] = 'Madrid';
        $europe['Europe/Moscow'] = 'Moscow';
        $europe['Europe/Paris'] = 'Paris';
        $europe['Europe/Prague'] = 'Prague';
        $europe['Europe/Rome'] = 'Rome';
        $europe['Europe/Stockholm'] = 'Stockholm';
        $europe['Europe/Vienna'] = 'Vienna';
        $europe['Europe/Warsaw'] = 'Warsaw';
        $europe['Europe/Zurich'] = 'Zurich';
        $zones[__('Europe', 'content-scheduler-pro')] = $europe;
        
        return $zones;
    }

    /**
     * Check if a timezone string is valid.
     *
     * @since 1.0.0
     * @param string $timezone Timezone string to check.
     * @return bool Whether the timezone is valid.
     */
    public function is_valid_timezone($timezone) {
        try {
            new \DateTimeZone($timezone);
            return true;
        } catch (\Exception $e) {
            // Log the error if WP_DEBUG is enabled
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Content Scheduler Pro: Invalid timezone - ' . $timezone . ' - ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Add help tab to settings page.
     *
     * @since 1.0.0
     */
    public function add_help_tab() {
        $screen = get_current_screen();

        // Only add to our settings page
        if (!$screen || 'scheduled_content_page_csp_settings' !== $screen->id) {
            return;
        }

        $screen->add_help_tab([
            'id'      => 'csp_settings_help',
            'title'   => __('Timezone Help', 'content-scheduler-pro'),
            'content' => '
                <h2>' . __('Timezone Settings Help', 'content-scheduler-pro') . '</h2>
                <p>' . __('The timezone setting affects when your scheduled content appears and disappears.', 'content-scheduler-pro') . '</p>
                <ul>
                    <li>' . __('<strong>WordPress Timezone:</strong> Uses the timezone set in Settings → General. If you manage all your site date/time settings there, this is the simplest option.', 'content-scheduler-pro') . '</li>
                    <li>' . __('<strong>Custom Timezone:</strong> Allows you to select a specific timezone just for scheduled content, which can be different from your main WordPress timezone.', 'content-scheduler-pro') . '</li>
                </ul>
                <p>' . __('If your content is not appearing when expected, double-check that your timezone settings match your expectations.', 'content-scheduler-pro') . '</p>
            ',
        ]);
    }
}