<?php
// File: includes/views/admin/meta-box-history-view.php
// Expects $history_log (array of history entries) and $post (WP_Post object)

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="srp-history-log-wrapper">
    <?php if ( ! empty( $history_log ) ) : ?>
        <ul>
            <?php foreach ( $history_log as $entry ) : ?>
                <li class="srp-history-entry">
                    <span class="srp-history-timestamp">
                        <?php
                        // Ensure timestamp is numeric before formatting
                        $timestamp = is_numeric($entry['timestamp']) ? $entry['timestamp'] : strtotime($entry['timestamp']);
                        if ($timestamp):
                            echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
                        else:
                            echo esc_html_e('Invalid date', 'service-request-plugin');
                        endif;
                        ?>
                    </span> -
                    <strong class="srp-history-user"><?php echo esc_html( $entry['user_name'] ); ?></strong>:
                    <span class="srp-history-action"><?php echo esc_html( $entry['entry'] ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p style="padding: 10px 12px; margin:0;"><?php esc_html_e( 'No history recorded for this request yet.', 'service-request-plugin' ); ?></p>
    <?php endif; ?>
</div>