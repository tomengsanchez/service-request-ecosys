<?php
// File: includes/helpers/class-srp-notification-helper.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Notification_Helper {

    private $taxonomy_model;

    public function __construct() {
        $this->taxonomy_model = new SRP_Taxonomy_Model();
    }

    public function send_admin_notification( $post_id ) {
        $admin_email = get_option('srp_admin_notification_email', get_option('admin_email'));
        $subject_meta = get_post_meta($post_id, '_srp_request_subject', true);
        $title = !empty($subject_meta) ? $subject_meta : get_the_title($post_id);

        $email_subject = sprintf( __( 'New IT Service Request Submitted: #%d - %s', 'service-request-plugin' ), $post_id, $title );
        
        $requester_name = get_post_meta( $post_id, '_srp_requester_name', true );
        $requester_email = get_post_meta( $post_id, '_srp_requester_email', true );
        $edit_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

        $priority = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_priority_level');
        $category = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_service_category');
        $status   = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_request_status');

        $message  = sprintf( __( 'A new IT service request "%s" has been submitted.', 'service-request-plugin' ), $title ) . "\n\n";
        $message .= sprintf( __( 'Request ID: %d', 'service-request-plugin' ), $post_id ) . "\n";
        $message .= sprintf( __( 'Submitted by: %s (%s)', 'service-request-plugin' ), $requester_name, $requester_email ) . "\n";
        $message .= sprintf( __( 'Category: %s', 'service-request-plugin' ), $category ) . "\n";
        $message .= sprintf( __( 'Priority: %s', 'service-request-plugin' ), $priority ) . "\n";
        $message .= sprintf( __( 'Status: %s', 'service-request-plugin' ), $status ) . "\n";
        $message .= "\n" . __( 'You can view and manage it here: ', 'service-request-plugin' ) . $edit_link;

        wp_mail( $admin_email, $email_subject, $message );
    }

    public function send_user_confirmation( $post_id, $user_email, $user_name ) {
        $subject_meta = get_post_meta($post_id, '_srp_request_subject', true);
        $title = !empty($subject_meta) ? $subject_meta : get_the_title($post_id);

        $email_subject = sprintf(__( 'Your IT Service Request (#%d) Has Been Received: %s', 'service-request-plugin' ), $post_id, $title);
        
        $priority = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_priority_level');
        $category = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_service_category');
        $status   = $this->taxonomy_model->get_term_names_for_post($post_id, 'srp_request_status');

        $message  = sprintf( __( 'Dear %s,', 'service-request-plugin' ), $user_name ) . "\n\n";
        $message .= sprintf( __( 'Thank you for submitting your IT service request: "%s".', 'service-request-plugin' ), $title ) . "\n";
        $message .= sprintf( __( 'Your Request ID is: %d', 'service-request-plugin' ), $post_id ) . "\n";
        $message .= sprintf( __( 'Category: %s', 'service-request-plugin' ), $category ) . "\n";
        $message .= sprintf( __( 'Priority: %s', 'service-request-plugin' ), $priority ) . "\n";
        $message .= sprintf( __( 'Current Status: %s', 'service-request-plugin' ), $status ) . "\n\n";
        $message .= __( 'We will process it shortly and get back to you if further information is needed.', 'service-request-plugin' ) . "\n";

        wp_mail( $user_email, $email_subject, $message );
    }
}
