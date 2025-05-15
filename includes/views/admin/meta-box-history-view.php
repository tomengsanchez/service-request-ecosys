<?php
// File: includes/views/admin/meta-box-history-view.php
// Expects $history_log (array of history entries)

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( empty( $history_log ) ) {
    echo '<p>' . __( 'No history recorded yet.', 'service-request-plugin' ) . '</p>';
} else {
    echo '<ul style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; padding: 10px;">';
    // $history_log is already reversed in the model if needed, or reverse here
    // $history_log = array_reverse($history_log); 

    foreach ( $history_log as $entry ) {
        $timestamp = isset($entry['timestamp']) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry['timestamp'] ) : __('Unknown time', 'service-request-plugin');
        $user_name = isset($entry['user_name']) ? esc_html( $entry['user_name'] ) : __('Unknown user', 'service-request-plugin');
        $log_text  = isset($entry['entry']) ? esc_html( $entry['entry'] ) : __('No details', 'service-request-plugin');
        
        echo '<li style="border-bottom: 1px dotted #ccc; padding-bottom: 5px; margin-bottom: 5px;">';
        echo '<strong>' . $timestamp . '</strong> - <em>' . $user_name . '</em>:<br />';
        echo nl2br( $log_text );
        echo '</li>';
    }
    echo '</ul>';
}
?>
