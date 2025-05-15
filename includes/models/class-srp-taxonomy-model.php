<?php
// File: includes/models/class-srp-taxonomy-model.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Taxonomy_Model {

    public function register_all_taxonomies( $post_type_slug ) {
        $this->register_service_category_taxonomy( $post_type_slug );
        $this->register_priority_level_taxonomy( $post_type_slug );
        $this->register_request_status_taxonomy( $post_type_slug );
    }

    private function register_service_category_taxonomy( $post_type_slug ) {
        $labels = [
            'name'              => _x( 'Service Categories', 'taxonomy general name', 'service-request-plugin' ),
            'singular_name'     => _x( 'Service Category', 'taxonomy singular name', 'service-request-plugin' ),
             // Add other labels as needed
        ];
        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'service-category'],
        ];
        register_taxonomy( 'srp_service_category', $post_type_slug, $args );
    }

    private function register_priority_level_taxonomy( $post_type_slug ) {
        $labels = [
            'name'              => _x( 'Priority Levels', 'taxonomy general name', 'service-request-plugin' ),
            'singular_name'     => _x( 'Priority Level', 'taxonomy singular name', 'service-request-plugin' ),
            // Add other labels as needed
        ];
        $args = [
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'priority-level'],
        ];
        register_taxonomy( 'srp_priority_level', $post_type_slug, $args );
    }

    private function register_request_status_taxonomy( $post_type_slug ) {
        $labels = [
            'name'              => _x( 'Statuses', 'taxonomy general name', 'service-request-plugin' ),
            'singular_name'     => _x( 'Status', 'taxonomy singular name', 'service-request-plugin' ),
            // Add other labels from original srp_register_taxonomies
        ];
        $args = [
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'request-status'],
        ];
        register_taxonomy( 'srp_request_status', $post_type_slug, $args );
    }

    public function populate_initial_terms() {
        $categories = [
            'Hardware Issue' => 'hardware-issue', 'Software Issue' => 'software-issue',
            'Network Problem' => 'network-problem', 'Access Request' => 'access-request',
            'General Inquiry' => 'general-inquiry',
        ];
        foreach ($categories as $name => $slug) {
            if (!term_exists($slug, 'srp_service_category')) {
                wp_insert_term($name, 'srp_service_category', ['slug' => $slug]);
            }
        }

        $priorities = [
            'Low' => 'low', 'Medium' => 'medium', 'High' => 'high', 'Critical' => 'critical',
        ];
        foreach ($priorities as $name => $slug) {
            if (!term_exists($slug, 'srp_priority_level')) {
                wp_insert_term($name, 'srp_priority_level', ['slug' => $slug]);
            }
        }

        $statuses = [
            'New' => 'new', 'Acknowledged' => 'acknowledged', 'Ongoing' => 'ongoing',
            'Resolved' => 'resolved', 'Not Resolved' => 'not-resolved', 'Closed' => 'closed',
        ];
        foreach ($statuses as $name => $slug) {
            if (!term_exists($slug, 'srp_request_status')) {
                wp_insert_term($name, 'srp_request_status', ['slug' => $slug]);
            }
        }
    }

    public function get_terms_for_dropdown( $taxonomy_slug, $hide_empty = false ) {
        return get_terms(['taxonomy' => $taxonomy_slug, 'hide_empty' => $hide_empty]);
    }

    public function get_term_names_for_post( $post_id, $taxonomy_slug ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy_slug, ['fields' => 'names'] );
        return !is_wp_error($terms) && !empty($terms) ? implode(', ', $terms) : __('N/A', 'service-request-plugin');
    }
}
