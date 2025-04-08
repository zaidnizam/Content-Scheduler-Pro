<?php
/**
 * Register custom post types for the plugin.
 *
 * @package ContentSchedulerPro
 * @since   1.0.0
 */

namespace ContentSchedulerPro;

/**
 * Class for registering custom post types.
 *
 * @since 1.0.0
 */
class PostTypes {

    /**
     * Register custom post types.
     *
     * @since 1.0.0
     */
    public function register_post_types() {
        $this->register_scheduled_content_post_type();
    }

    /**
     * Register the Scheduled Content custom post type.
     *
     * @since 1.0.0
     */
    private function register_scheduled_content_post_type() {
        $labels = [
            'name'                  => _x('Scheduled Contents', 'Post type general name', 'content-scheduler-pro'),
            'singular_name'         => _x('Scheduled Content', 'Post type singular name', 'content-scheduler-pro'),
            'menu_name'             => _x('Content Scheduler', 'Admin Menu text', 'content-scheduler-pro'),
            'name_admin_bar'        => _x('Scheduled Content', 'Add New on Toolbar', 'content-scheduler-pro'),
            'add_new'               => __('Add New', 'content-scheduler-pro'),
            'add_new_item'          => __('Add New Scheduled Content', 'content-scheduler-pro'),
            'new_item'              => __('New Scheduled Content', 'content-scheduler-pro'),
            'edit_item'             => __('Edit Scheduled Content', 'content-scheduler-pro'),
            'view_item'             => __('View Scheduled Content', 'content-scheduler-pro'),
            'all_items'             => __('All Scheduled Contents', 'content-scheduler-pro'),
            'search_items'          => __('Search Scheduled Contents', 'content-scheduler-pro'),
            'parent_item_colon'     => __('Parent Scheduled Contents:', 'content-scheduler-pro'),
            'not_found'             => __('No scheduled contents found.', 'content-scheduler-pro'),
            'not_found_in_trash'    => __('No scheduled contents found in Trash.', 'content-scheduler-pro'),
            'featured_image'        => _x('Scheduled Content Cover Image', 'Overrides the "Featured Image" phrase', 'content-scheduler-pro'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'content-scheduler-pro'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'content-scheduler-pro'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'content-scheduler-pro'),
            'archives'              => _x('Scheduled Content archives', 'The post type archive label used in nav menus', 'content-scheduler-pro'),
            'insert_into_item'      => _x('Insert into scheduled content', 'Overrides the "Insert into post" phrase', 'content-scheduler-pro'),
            'uploaded_to_this_item' => _x('Uploaded to this scheduled content', 'Overrides the "Uploaded to this post" phrase', 'content-scheduler-pro'),
            'filter_items_list'     => _x('Filter scheduled contents list', 'Screen reader text for the filter links heading on the post type listing screen', 'content-scheduler-pro'),
            'items_list_navigation' => _x('Scheduled Contents list navigation', 'Screen reader text for the pagination heading on the post type listing screen', 'content-scheduler-pro'),
            'items_list'            => _x('Scheduled Contents list', 'Screen reader text for the items list heading on the post type listing screen', 'content-scheduler-pro'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => ['title', 'editor', 'author', 'revisions'],
            'show_in_rest'       => true,
        ];

        register_post_type('scheduled_content', $args);
    }
}