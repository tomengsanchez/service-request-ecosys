<?php
// File: includes/models/class-srp-request-model.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Request_Model {

    private $history_model;

    public function __construct() {
        $this->history_model = new SRP_History_Model(); // Assuming HistoryModel exists
    }

    /**
     * Create a new service request.
     * @param array $data Associative array of request data.
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    public function create_request( array $data ) {
        $current_user = wp_get_current_user();
        $is_logged_in = $current_user->exists();
        $post_author_id = $is_logged_in ? $current_user->ID : 0;

        $post_data = [
            'post_title'   => sanitize_text_field( $data['subject'] ), // Initial title
            'post_content' => sanitize_textarea_field( $data['description'] ),
            'post_status'  => 'publish',
            'post_type'    => SRP_POST_TYPE,
            'post_author'  => $post_author_id,
        ];
        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Update title to be more specific
        $final_title = sprintf(__('Service Request #%d: %s', 'service-request-plugin'), $post_id, $data['subject']);
        wp_update_post(['ID' => $post_id, 'post_title' => $final_title]);

        // Save meta fields
        update_post_meta( $post_id, '_srp_request_subject', sanitize_text_field( $data['subject'] ) );
        update_post_meta( $post_id, '_srp_requester_name', sanitize_text_field( $data['name'] ) );
        update_post_meta( $post_id, '_srp_requester_email', sanitize_email( $data['email'] ) );
        update_post_meta( $post_id, '_srp_requester_phone', sanitize_text_field( $data['phone'] ) );
        update_post_meta( $post_id, '_srp_affected_system', sanitize_text_field( $data['affected_system'] ) );
        update_post_meta( $post_id, '_srp_error_message', sanitize_textarea_field( $data['error_message'] ) );
        update_post_meta( $post_id, '_srp_asset_id', sanitize_text_field( $data['asset_id'] ) );

        // Set taxonomies
        if ( !empty($data['service_category_id']) ) {
            wp_set_object_terms( $post_id, absint( $data['service_category_id'] ), 'srp_service_category' );
        }
        if ( !empty($data['priority_id']) ) {
            wp_set_object_terms( $post_id, absint( $data['priority_id'] ), 'srp_priority_level' );
        }

        // Set initial status
        $initial_status_slug = get_option('srp_initial_status_slug', 'new');
        $initial_status_term = get_term_by('slug', $initial_status_slug, 'srp_request_status');
        if ($initial_status_term) {
            wp_set_object_terms( $post_id, $initial_status_term->term_id, 'srp_request_status' );
        }

        // Add history entry
        $submitter_id_for_history = $is_logged_in ? $current_user->ID : 0;
        $this->history_model->add_entry( $post_id, sprintf(__('Request submitted with subject: "%s"', 'service-request-plugin'), $data['subject']), $submitter_id_for_history );

        return $post_id;
    }

    /**
     * Get details for a specific service request.
     * @param int $post_id
     * @return array|null
     */
    public function get_request_details( $post_id ) {
        $post = get_post( $post_id );
        if ( !$post || $post->post_type !== SRP_POST_TYPE ) {
            return null;
        }

        return [
            'id' => $post->ID,
            'subject' => get_post_meta( $post->ID, '_srp_request_subject', true ),
            'description' => $post->post_content,
            'requester_name' => get_post_meta( $post->ID, '_srp_requester_name', true ),
            'requester_email' => get_post_meta( $post->ID, '_srp_requester_email', true ),
            'requester_phone' => get_post_meta( $post->ID, '_srp_requester_phone', true ),
            'affected_system' => get_post_meta( $post->ID, '_srp_affected_system', true ),
            'error_message' => get_post_meta( $post->ID, '_srp_error_message', true ),
            'asset_id' => get_post_meta( $post->ID, '_srp_asset_id', true ),
            'author_id' => $post->post_author,
            // Add taxonomy terms if needed
        ];
    }

    /**
     * Update a service request's meta data and core post fields.
     * This is a simplified version; a full version would handle all fields and history logging.
     */
    public function update_request_meta( $post_id, $data_array, $current_user_id ) {
        $history_entries = [];

        // --- Handle Post Author (Requester WP User) ---
        if ( isset( $data_array['srp_post_author_override'] ) ) {
            $original_author_id = get_post_field('post_author', $post_id);
            $new_author_id = absint( $data_array['srp_post_author_override'] );
            if ($new_author_id !== $original_author_id) {
                wp_update_post( ['ID' => $post_id, 'post_author' => $new_author_id] ); // Unhook save_post if causing recursion
                
                $new_author_obj = get_userdata($new_author_id);
                $original_author_obj = get_userdata($original_author_id);
                $new_author_name = $new_author_obj ? $new_author_obj->display_name : 'None';
                $original_author_name = $original_author_obj ? $original_author_obj->display_name : 'None';
                $history_entries[] = sprintf(__('Requester (WP User) changed from %s to %s.', 'service-request-plugin'), $original_author_name, $new_author_name);
            }
        }
        
        // --- Handle Subject (which also updates post_title) ---
        if ( isset( $data_array['srp_request_subject'] ) ) {
            $new_subject = sanitize_text_field( $data_array['srp_request_subject'] );
            $old_subject = get_post_meta( $post_id, '_srp_request_subject', true );
            if ($new_subject !== $old_subject) {
                update_post_meta( $post_id, '_srp_request_subject', $new_subject );
                $history_entries[] = sprintf(__('Subject changed from "%s" to "%s".', 'service-request-plugin'), $old_subject, $new_subject);
            }
            $post_title = !empty($new_subject) ? $new_subject : sprintf(__('Service Request #%d', 'service-request-plugin'), $post_id);
            if (get_the_title($post_id) !== $post_title) {
                 wp_update_post( ['ID' => $post_id, 'post_title' => $post_title] ); // Unhook save_post if causing recursion
            }
        }

        // --- Handle Post Content (Detailed Description) ---
        if ( isset( $data_array['srp_request_description_editor'] ) ) {
            $new_content = wp_kses_post( $data_array['srp_request_description_editor'] );
            $old_content = get_post_field('post_content', $post_id);
            if ($new_content !== $old_content) {
                wp_update_post( ['ID' => $post_id, 'post_content' => $new_content] ); // Unhook save_post if causing recursion
                $history_entries[] = __('Detailed description updated.', 'service-request-plugin');
            }
        }
        
        // --- Handle Other Meta Fields ---
        $meta_fields_to_save = [
            'srp_requester_name'  => ['sanitize_callback' => 'sanitize_text_field', 'label' => __('Contact Name', 'service-request-plugin')],
            'srp_requester_email' => ['sanitize_callback' => 'sanitize_email', 'label' => __('Contact Email', 'service-request-plugin')],
            'srp_requester_phone' => ['sanitize_callback' => 'sanitize_text_field', 'label' => __('Contact Phone', 'service-request-plugin')],
            'srp_affected_system' => ['sanitize_callback' => 'sanitize_text_field', 'label' => __('Affected System', 'service-request-plugin')],
            'srp_error_message'   => ['sanitize_callback' => 'sanitize_textarea_field', 'label' => __('Error Message', 'service-request-plugin')],
            'srp_asset_id'        => ['sanitize_callback' => 'sanitize_text_field', 'label' => __('Asset ID', 'service-request-plugin')],
        ];

        foreach ( $meta_fields_to_save as $field_key => $field_config ) {
            if ( isset( $data_array[$field_key] ) ) {
                $new_value = call_user_func( $field_config['sanitize_callback'], $data_array[$field_key] );
                $old_value = get_post_meta( $post_id, '_' . $field_key, true );
                if ($new_value !== $old_value) {
                    update_post_meta( $post_id, '_' . $field_key, $new_value );
                    $history_entries[] = sprintf(__('%s changed from "%s" to "%s".', 'service-request-plugin'), $field_config['label'], $old_value, $new_value);
                }
            }
        }
        
        // Add all collected history entries
        if (!empty($history_entries)) {
            foreach ($history_entries as $entry_text) {
                $this->history_model->add_entry($post_id, $entry_text, $current_user_id);
            }
        }
        // Note: Taxonomy changes history should ideally be logged via 'set_object_terms' hook
    }
}
