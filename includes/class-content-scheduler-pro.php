<?php
/**
 * The core plugin class.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since 1.0.0
 */
class ContentSchedulerPro {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once CSP_PLUGIN_DIR . 'includes/class-loader.php';
        
        // The class responsible for defining internationalization functionality.
        require_once CSP_PLUGIN_DIR . 'includes/class-i18n.php';
        
        // The class responsible for registering custom post type.
        require_once CSP_PLUGIN_DIR . 'includes/class-post-types.php';
        
        // The class responsible for managing meta boxes.
        require_once CSP_PLUGIN_DIR . 'includes/class-meta-boxes.php';
        
        // The class responsible for defining shortcode functionality.
        require_once CSP_PLUGIN_DIR . 'includes/class-shortcode.php';
        
        // The class responsible for defining all admin functionality.
        require_once CSP_PLUGIN_DIR . 'includes/class-admin.php';
        
        // The class responsible for handling rating notices.
        require_once CSP_PLUGIN_DIR . 'includes/class-rating.php';
        
        // The class responsible for plugin settings.
        require_once CSP_PLUGIN_DIR . 'includes/class-settings.php';

        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since  1.0.0
     * @access private
     */
    private function set_locale() {
        $plugin_i18n = new I18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_admin_hooks() {
        $post_types = new PostTypes();
        $this->loader->add_action('init', $post_types, 'register_post_types');

        $meta_boxes = new MetaBoxes();
        $this->loader->add_action('add_meta_boxes', $meta_boxes, 'add_meta_boxes');
        $this->loader->add_action('save_post', $meta_boxes, 'save_meta_boxes', 10, 2);

        $admin = new Admin();
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_filter('manage_scheduled_content_posts_columns', $admin, 'set_custom_columns');
        $this->loader->add_action('manage_scheduled_content_posts_custom_column', $admin, 'display_custom_column', 10, 2);
        $this->loader->add_filter('post_row_actions', $admin, 'modify_row_actions', 10, 2);
        
        // Initialize rating notice
        new Rating();
        
        // Initialize settings
        new Settings();
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_public_hooks() {
        $shortcode = new Shortcode();
        $this->loader->add_shortcode('content_scheduler_pro', $shortcode, 'render_shortcode');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}