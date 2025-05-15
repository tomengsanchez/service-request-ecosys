<?php
// File: includes/controllers/class-srp-admin-controller.php

if ( ! defined( 'WPINC' ) ) {
    die;
}

class SRP_Admin_Controller {

    private $request_model;
    private $history_model;
    // Taxonomy model might be needed for dropdowns in meta boxes if not handled by WP default UI for taxonomies

    public function __construct() {
        $this->request_model = new SRP_Request_Model();
        $this->history_model = new SRP_History_Model();
    }

    /**
     * Add meta boxes to the Service Request CPT edit screen.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'srp_request_main_details_meta_box',
            __( 'Service Request Details', 'service-request-plugin' ),
            [ $this, 'render_main_details_meta_box_view' ],
            SRP_POST_TYPE, 'normal', 'high'
        );
        add_meta_box(
            'srp_history_meta_box',
            __( 'Request History', 'service-request-plugin' ),
            [ $this, 'render_history_meta_box_view' ],
            SRP_POST_TYPE, 'normal', 'default'
        );
    }

    /**
     * Render the main details meta box by including its view file.
     */
    public function render_main_details_meta_box_view( $post ) {
        // Get data needed for the view
        $request_data = $this->request_model->get_request_details( $post->ID );
        if (!$request_data) {
            $request_data = []; // Ensure it's an array for the view
        }
        $request_data['post'] = $post; // Pass the $post object for nonce, etc.
        $request_data['nonce_action'] = 'srp_save_meta_box_data';
        $request_data['nonce_name'] = 'srp_meta_box_nonce';
        
        extract($request_data); // Make array keys available as variables
        include SRP_VIEWS_PATH . 'admin/meta-box-details-view.php';
    }

    /**
     * Render the history meta box by including its view file.
     */
    public function render_history_meta_box_view( $post ) {
        $history_log = $this->history_model->get_history( $post->ID );
        
        extract(['history_log' => $history_log, 'post' => $post]);
        include SRP_VIEWS_PATH . 'admin/meta-box-history-view.php';
    }

    /**
     * Handle saving of meta box data.
     * Hooked to 'save_post_{post_type}'.
     */
    public function save_meta_data( $post_id ) {
        // Nonce check
        if ( ! isset( $_POST['srp_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['srp_meta_box_nonce'], 'srp_save_meta_box_data' ) ) {
            return;
        }
        // Autosave check
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Permission check
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        // Post type check (already implicitly handled by save_post_{post_type} hook but good for direct calls)
        if ( SRP_POST_TYPE !== get_post_type($post_id) ) {
            return;
        }

        // Pass all relevant $_POST data to the model for processing
        // The model will handle sanitization, validation, and history logging for each field.
        // Note: wp_update_post calls within the model for post_author, post_title, post_content
        // might re-trigger this save_post hook. It's crucial to unhook/rehook around them
        // or ensure the checks prevent infinite loops (e.g., only update if value changed).
        // This is simplified here; robust recursion prevention is important.
        
        // Temporarily remove this action to prevent recursion if wp_update_post is called in model
        remove_action( 'save_post_' . SRP_POST_TYPE, [ $this, 'save_meta_data' ], 10 );
        
        $this->request_model->update_request_meta( $post_id, $_POST, get_current_user_id() );
        
        // WordPress handles saving taxonomy terms from its metaboxes automatically.
        // History for taxonomy changes is best logged via 'set_object_terms' hook.
        // Example: add_action( 'set_object_terms', [ $this->history_model, 'log_term_changes' ], 10, 6 );

        // Re-hook the action
        add_action( 'save_post_' . SRP_POST_TYPE, [ $this, 'save_meta_data' ], 10, 1 );
    }

    // --- Admin Columns Methods (set_custom_edit_columns, render_custom_column_data, etc.) ---
    // These would be refactored similarly, calling model methods for data and keeping presentation minimal.
    // Example:
    public function set_custom_edit_columns($columns) {
        unset($columns['title']); 
        unset($columns['author']);

        $new_columns = [];
        $new_columns['cb'] = '<input type="checkbox" />';
        $new_columns['srp_subject_col'] = __('Subject (ID)', 'service-request-plugin');
        $new_columns['srp_requester_info_col'] = __('Requester', 'service-request-plugin');
        $new_columns['taxonomy-srp_service_category'] = __('Category', 'service-request-plugin');
        $new_columns['taxonomy-srp_priority_level'] = __('Priority', 'service-request-plugin');
        $new_columns['taxonomy-srp_request_status'] = __('Status', 'service-request-plugin');
        $new_columns['srp_asset_id_col'] = __('Asset ID', 'service-request-plugin');
        $new_columns['date'] = __('Submitted Date', 'service-request-plugin');
        return $new_columns;
    }

    public function render_custom_column_data($column, $post_id) {
        // Data fetching would ideally come from the model
        $request_details = $this->request_model->get_request_details($post_id); // Simplified
        $post_author_id = get_post_field('post_author', $post_id);


        switch ($column) {
            case 'srp_subject_col':
                $subject = $request_details ? $request_details['subject'] : get_the_title($post_id);
                $edit_link = get_edit_post_link($post_id);
                printf('<a class="row-title" href="%s"><strong>#%d: %s</strong></a>', esc_url($edit_link), $post_id, esc_html($subject));
                break;
            case 'srp_requester_info_col':
                 $author_name = get_the_author_meta('display_name', $post_author_id);
                 $author_email = get_the_author_meta('user_email', $post_author_id);

                if ($post_author_id && $author_name) {
                     echo esc_html( $author_name ) . '<br/><small>' . esc_html( $author_email ) . '</small>';
                } else {
                    $name = $request_details ? $request_details['requester_name'] : '';
                    $email = $request_details ? $request_details['requester_email'] : '';
                    echo esc_html( $name ) . '<br/><small>' . esc_html( $email ) . ' (Guest/Manual)</small>';
                }
                break;
            case 'srp_asset_id_col':
                echo esc_html( $request_details ? $request_details['asset_id'] : '' );
                break;
        }
    }
     public function make_columns_sortable( $columns ) {
        $columns['srp_subject_col'] = '_srp_request_subject';
        $columns['srp_requester_info_col'] = 'author';
        $columns['srp_asset_id_col'] = '_srp_asset_id';
        return $columns;
    }

    public function handle_custom_column_orderby( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        $orderby = $query->get( 'orderby' );
        if ( '_srp_request_subject' === $orderby ) {
            $query->set( 'meta_key', '_srp_request_subject' );
            $query->set( 'orderby', 'meta_value' );
        }
        if ( '_srp_asset_id' === $orderby ) {
            $query->set( 'meta_key', '_srp_asset_id' );
            $query->set( 'orderby', 'meta_value' );
        }
    }
}
