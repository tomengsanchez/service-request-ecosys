<?php
// File: includes/controllers/class-srp-plugin-controller.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Plugin_Controller {

    private $taxonomy_model;

    public function __construct() {
        $this->taxonomy_model = new SRP_Taxonomy_Model();
    }

    /**
     * Plugin activation tasks.
     */
    public function activate() {
        $this->register_post_types_and_taxonomies(); // Ensure they are registered on activation
        $this->taxonomy_model->populate_initial_terms();
        flush_rewrite_rules();

        add_option('srp_admin_notification_email', get_option('admin_email'));
        add_option('srp_default_priority', 'medium');
        add_option('srp_default_service_category', 'general_inquiry');
        add_option('srp_initial_status_slug', 'new');
    }

    /**
     * Plugin deactivation tasks.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall tasks (static method for register_uninstall_hook).
     */
    public static function uninstall() {
        delete_option('srp_admin_notification_email');
        delete_option('srp_default_priority');
        delete_option('srp_default_service_category');
        delete_option('srp_initial_status_slug');
        // Add logic to delete CPT posts and terms if desired.
    }

    /**
     * Load plugin textdomain for internationalization.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'service-request-plugin', false, dirname( plugin_basename( SRP_PLUGIN_FILE ) ) . '/languages/' );
    }

    /**
     * Register Custom Post Type and Taxonomies.
     * Hooked to 'init'.
     */
    public function register_post_types_and_taxonomies() {
        $this->register_service_request_cpt();
        $this->taxonomy_model->register_all_taxonomies( SRP_POST_TYPE );
    }

    /**
     * Register the "Service Request" Custom Post Type.
     */
    private function register_service_request_cpt() {
        $labels = [
            'name'               => _x( 'Service Requests', 'post type general name', 'service-request-plugin' ),
            'singular_name'      => _x( 'Service Request', 'post type singular name', 'service-request-plugin' ),
            'add_new'            => _x( 'Add New', 'service request', 'service-request-plugin' ),
            'add_new_item'       => __( 'Add New Service Request', 'service-request-plugin' ),
            'edit_item'          => __( 'Edit Service Request', 'service-request-plugin' ),
            'new_item'           => __( 'New Service Request', 'service-request-plugin' ),
            'view_item'          => __( 'View Service Request', 'service-request-plugin' ),
            'search_items'       => __( 'Search Service Requests', 'service-request-plugin' ),
            'not_found'          => __( 'No service requests found', 'service-request-plugin' ),
            'not_found_in_trash' => __( 'No service requests found in Trash', 'service-request-plugin' ),
            'menu_name'          => __( 'Service Requests', 'service-request-plugin' ),
            'all_items'          => __( 'All Service Requests', 'service-request-plugin'),
        ];
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'service-requests'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => ['author'], // Removed 'title' and 'editor'
            'menu_icon'          => 'dashicons-clipboard',
            // Taxonomies will be linked when they are registered.
        ];
        register_post_type( SRP_POST_TYPE, $args );
    }
}
