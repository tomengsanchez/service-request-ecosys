<?php
// File: includes/models/class-srp-history-model.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_History_Model {

    /**
     * Add a history entry to a service request.
     * @param int    $post_id Post ID.
     * @param string $entry_text The text for the history entry.
     * @param int    $user_id Optional. User ID performing the action. Defaults to current user.
     */
    public function add_entry( $post_id, $entry_text, $user_id = null ) {
        if ( ! $post_id || empty( $entry_text ) ) {
            return;
        }

        $history = get_post_meta( $post_id, '_srp_history_log', true );
        if ( ! is_array( $history ) ) {
            $history = [];
        }

        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $user_info = get_userdata( $user_id );
        $user_display_name = $user_info ? $user_info->display_name : __( 'System', 'service-request-plugin' );
        
        if ( $user_id === 0 ) { // Guest submission
            $guest_name = get_post_meta($post_id, '_srp_requester_name', true);
            $user_display_name = !empty($guest_name) ? esc_html($guest_name) . ' (Guest)' : __('Guest User', 'service-request-plugin');
        }

        $new_entry = [
            'timestamp' => current_time( 'timestamp' ),
            'user_id'   => $user_id,
            'user_name' => $user_display_name,
            'entry'     => sanitize_text_field( $entry_text ),
        ];

        $history[] = $new_entry;
        update_post_meta( $post_id, '_srp_history_log', $history );
    }

    /**
     * Get history log for a service request.
     * @param int $post_id
     * @return array
     */
    public function get_history( $post_id ) {
        $history = get_post_meta( $post_id, '_srp_history_log', true );
        if ( ! is_array( $history ) ) {
            return [];
        }
        return array_reverse( $history ); // Show newest first
    }
}
