<?php
// File: includes/controllers/class-srp-frontend-controller.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Frontend_Controller {

    private $request_model;
    private $taxonomy_model;
    private $notification_helper;

    public function __construct() {
        $this->request_model = new SRP_Request_Model();
        $this->taxonomy_model = new SRP_Taxonomy_Model();
        $this->notification_helper = new SRP_Notification_Helper(); // Assuming NotificationHelper exists
    }

    /**
     * Render the service request form via shortcode.
     */
    public function render_service_request_form( $atts ) {
        ob_start();

        $current_user = wp_get_current_user();
        $is_logged_in = $current_user->exists();
        
        $form_data = [
            'is_logged_in' => $is_logged_in,
            'user_name_val' => $is_logged_in ? $current_user->display_name : '',
            'user_email_val' => $is_logged_in ? $current_user->user_email : '',
            'service_categories' => $this->taxonomy_model->get_terms_for_dropdown('srp_service_category'),
            'priority_levels' => $this->taxonomy_model->get_terms_for_dropdown('srp_priority_level'),
            'default_priority_slug' => get_option('srp_default_priority', 'medium'),
            'action_url' => esc_url( get_permalink() ),
            'nonce_action' => 'srp_submit_request_action',
            'nonce_name' => 'srp_form_nonce',
        ];
        
        // Include the view file, passing data to it
        // The view file will have access to $form_data array
        extract($form_data); // Make array keys available as variables in the included file
        include SRP_VIEWS_PATH . 'frontend/service-request-form-view.php';

        return ob_get_clean();
    }

    /**
     * Handle the frontend form submission.
     * Hooked to 'template_redirect' or an earlier action.
     */
    public function handle_form_submission() {
        if ( !isset( $_POST['srp_submit_request'] ) || !isset($_POST['srp_form_nonce']) || !wp_verify_nonce( $_POST['srp_form_nonce'], 'srp_submit_request_action' ) ) {
            return; // Not our form submission or nonce failed
        }

        $current_user = wp_get_current_user();
        $is_logged_in = $current_user->exists();

        // Sanitize and prepare data
        $data = [
            'subject'        => sanitize_text_field( $_POST['srp_request_subject_form'] ),
            'description'    => sanitize_textarea_field( $_POST['srp_request_description_form'] ),
            'name'           => $is_logged_in ? $current_user->display_name : sanitize_text_field( $_POST['srp_requester_name_form'] ),
            'email'          => $is_logged_in ? $current_user->user_email : sanitize_email( $_POST['srp_requester_email_form'] ),
            'phone'          => sanitize_text_field( $_POST['srp_requester_phone_form'] ),
            'service_category_id' => isset($_POST['srp_service_category_form']) ? absint( $_POST['srp_service_category_form'] ) : 0,
            'priority_slug'  => isset($_POST['srp_priority_level_form']) ? sanitize_key( $_POST['srp_priority_level_form'] ) : get_option('srp_default_priority', 'medium'),
            'affected_system'=> sanitize_text_field( $_POST['srp_affected_system_form'] ),
            'error_message'  => sanitize_textarea_field( $_POST['srp_error_message_form'] ),
            'asset_id'       => sanitize_text_field( $_POST['srp_asset_id_form'] ),
        ];
        
        $priority_term = get_term_by('slug', $data['priority_slug'], 'srp_priority_level');
        $data['priority_id'] = $priority_term ? $priority_term->term_id : 0;


        // Basic validation (can be expanded)
        if ( empty($data['subject']) || empty($data['description']) || empty($data['name']) || !is_email($data['email']) || empty($data['service_category_id']) || empty($data['priority_id']) ) {
            // Store an error message in a transient or session to display on the form page
            set_transient('srp_form_error', __('Please fill in all required fields correctly.', 'service-request-plugin'), 45);
            return;
        }

        $post_id = $this->request_model->create_request( $data );

        if ( !is_wp_error( $post_id ) ) {
            $this->notification_helper->send_admin_notification( $post_id );
            $this->notification_helper->send_user_confirmation( $post_id, $data['email'], $data['name'] );
            set_transient('srp_form_success', sprintf(__( 'Your service request has been submitted successfully! Your Request ID is #%d.', 'service-request-plugin' ), $post_id), 45);
            // Redirect to avoid form resubmission, perhaps to the same page with a success query arg
             wp_redirect( esc_url_raw( add_query_arg( 'srp_success', $post_id, remove_query_arg('srp_error') ) ) ); // Redirect to same page with success message
             exit;

        } else {
            set_transient('srp_form_error', __( 'There was an error submitting your request: ', 'service-request-plugin' ) . $post_id->get_error_message(), 45);
        }
    }
}
