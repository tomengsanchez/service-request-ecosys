<?php
// File: includes/views/frontend/service-request-form-view.php
// This view expects variables like $is_logged_in, $user_name_val, $service_categories, etc. to be available (e.g., via extract() in controller)

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Display success/error messages from transients
if ( $success_message = get_transient( 'srp_form_success' ) ) {
    echo '<div class="srp-success">' . esc_html( $success_message ) . '</div>';
    delete_transient( 'srp_form_success' );
}
if ( $error_message = get_transient( 'srp_form_error' ) ) {
    echo '<div class="srp-error">' . esc_html( $error_message ) . '</div>';
    delete_transient( 'srp_form_error' );
}
?>
<form id="serviceRequestForm" method="POST" action="<?php echo esc_url( $action_url ); ?>">
    <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
    <p>
        <label for="srp_requester_name_form"><?php _e( 'Your Name:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <input type="text" id="srp_requester_name_form" name="srp_requester_name_form" value="<?php echo esc_attr($user_name_val); ?>" <?php if ($is_logged_in) echo 'readonly'; ?> required aria-required="true" />
    </p>
    <p>
        <label for="srp_requester_email_form"><?php _e( 'Your Email:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <input type="email" id="srp_requester_email_form" name="srp_requester_email_form" value="<?php echo esc_attr($user_email_val); ?>" <?php if ($is_logged_in) echo 'readonly'; ?> required aria-required="true" />
    </p>
     <p>
        <label for="srp_requester_phone_form"><?php _e( 'Your Phone:', 'service-request-plugin' ); ?></label>
        <input type="tel" id="srp_requester_phone_form" name="srp_requester_phone_form" />
    </p>
    <p>
        <label for="srp_request_subject_form"><?php _e( 'Subject / Issue Summary:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <input type="text" id="srp_request_subject_form" name="srp_request_subject_form" required aria-required="true" />
    </p>
    <p>
        <label for="srp_service_category_form"><?php _e( 'Service Category:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <select id="srp_service_category_form" name="srp_service_category_form" required aria-required="true">
            <option value=""><?php _e( '-- Select Category --', 'service-request-plugin' ); ?></option>
            <?php if (!is_wp_error($service_categories) && !empty($service_categories)): ?>
                <?php foreach ( $service_categories as $category ) : ?>
                    <option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
    <p>
        <label for="srp_priority_level_form"><?php _e( 'Priority Level:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <select id="srp_priority_level_form" name="srp_priority_level_form" required aria-required="true">
            <option value=""><?php _e( '-- Select Priority --', 'service-request-plugin' ); ?></option>
             <?php if (!is_wp_error($priority_levels) && !empty($priority_levels)): ?>
                <?php foreach ( $priority_levels as $priority_opt ) : ?>
                    <option value="<?php echo esc_attr( $priority_opt->slug ); ?>" <?php selected( $default_priority_slug, $priority_opt->slug ); ?>>
                        <?php echo esc_html( $priority_opt->name ); ?>
                    </option>
                <?php endforeach; ?>
             <?php endif; ?>
        </select>
    </p>
    <p>
        <label for="srp_affected_system_form"><?php _e( 'Affected System/Application:', 'service-request-plugin' ); ?></label>
        <input type="text" id="srp_affected_system_form" name="srp_affected_system_form" />
    </p>
    <p>
        <label for="srp_asset_id_form"><?php _e( 'Asset ID/Hostname (if applicable):', 'service-request-plugin' ); ?></label>
        <input type="text" id="srp_asset_id_form" name="srp_asset_id_form" />
    </p>
    <p>
        <label for="srp_request_description_form"><?php _e( 'Detailed Description of Issue/Request:', 'service-request-plugin' ); ?> <span class="srp-required-field">*</span></label>
        <textarea id="srp_request_description_form" name="srp_request_description_form" rows="6" required aria-required="true"></textarea>
    </p>
    <p>
        <label for="srp_error_message_form"><?php _e( 'Error Message (copy & paste if any):', 'service-request-plugin' ); ?></label>
        <textarea id="srp_error_message_form" name="srp_error_message_form" rows="4"></textarea>
    </p>
    <p class="srp-submit-wrap">
        <input type="submit" name="srp_submit_request" class="srp-submit-button" value="<?php _e( 'Submit Request', 'service-request-plugin' ); ?>" />
        <span class="srp-spinner"></span>
    </p>
</form>