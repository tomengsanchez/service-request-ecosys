<?php
// File: includes/views/admin/meta-box-details-view.php
// Expects $post, $subject, $description, $requester_name, $requester_email, etc.
// and $nonce_action, $nonce_name

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Ensure variables are set to avoid notices, falling back to defaults from $post or empty
$current_subject = isset($subject) ? $subject : '';
$current_description = isset($description) ? $description : $post->post_content;
$current_author_id = isset($author_id) ? $author_id : $post->post_author;

$current_requester_name = isset($requester_name) ? $requester_name : '';
$current_requester_email = isset($requester_email) ? $requester_email : '';
$current_requester_phone = isset($requester_phone) ? $requester_phone : '';
$current_affected_system = isset($affected_system) ? $affected_system : '';
$current_asset_id = isset($asset_id) ? $asset_id : '';
$current_error_message = isset($error_message) ? $error_message : '';

wp_nonce_field( $nonce_action, $nonce_name );
?>
<table class="form-table">
    <tr>
        <th><label for="srp_request_subject"><?php _e( 'Subject / Issue Summary:', 'service-request-plugin' ); ?></label></th>
        <td><input type="text" id="srp_request_subject" name="srp_request_subject" value="<?php echo esc_attr( $current_subject ); ?>" class="large-text" required />
        <p class="description"><?php _e('This will be used as the main identifier for the request.', 'service-request-plugin'); ?></p></td>
    </tr>
    <tr>
        <th><label for="srp_post_author_override"><?php _e( 'Requester (WordPress User):', 'service-request-plugin' ); ?></label></th>
        <td>
            <?php
            wp_dropdown_users([
                'name' => 'srp_post_author_override',
                'id' => 'srp_post_author_override',
                'selected' => $current_author_id,
                'show_option_none' => __('(Guest or Manual Entry)', 'service-request-plugin'),
            ]);
            ?>
             <p class="description"><?php _e('Select a WordPress user as the requester. If guest, use fields below.', 'service-request-plugin'); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="srp_requester_name"><?php _e( 'Contact Name (if guest/manual):', 'service-request-plugin' ); ?></label></th>
        <td><input type="text" id="srp_requester_name" name="srp_requester_name" value="<?php echo esc_attr( $current_requester_name ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="srp_requester_email"><?php _e( 'Contact Email (if guest/manual):', 'service-request-plugin' ); ?></label></th>
        <td><input type="email" id="srp_requester_email" name="srp_requester_email" value="<?php echo esc_attr( $current_requester_email ); ?>" class="regular-text" /></td>
    </tr>
     <tr>
        <th><label for="srp_requester_phone"><?php _e( 'Contact Phone:', 'service-request-plugin' ); ?></label></th>
        <td><input type="tel" id="srp_requester_phone" name="srp_requester_phone" value="<?php echo esc_attr( $current_requester_phone ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="srp_request_description_editor"><?php _e( 'Detailed Description:', 'service-request-plugin' ); ?></label></th>
        <td>
            <textarea id="srp_request_description_editor" name="srp_request_description_editor" rows="8" class="large-text"><?php echo esc_textarea( $current_description ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="srp_affected_system"><?php _e( 'Affected System/Application:', 'service-request-plugin' ); ?></label></th>
        <td><input type="text" id="srp_affected_system" name="srp_affected_system" value="<?php echo esc_attr( $current_affected_system ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="srp_asset_id"><?php _e( 'Asset ID/Hostname:', 'service-request-plugin' ); ?></label></th>
        <td><input type="text" id="srp_asset_id" name="srp_asset_id" value="<?php echo esc_attr( $current_asset_id ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="srp_error_message"><?php _e( 'Error Message (if any):', 'service-request-plugin' ); ?></label></th>
        <td><textarea id="srp_error_message" name="srp_error_message" rows="5" class="large-text"><?php echo esc_textarea( $current_error_message ); ?></textarea></td>
    </tr>
</table>
