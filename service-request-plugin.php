<?php
/**
 * Plugin Name:       Service Request Management (IT Ticketing - MVC)
 * Plugin URI:        https://example.com/plugins/service-request/
 * Description:       Allows users to submit IT-related service requests and admins to manage them with status and history, using an MVC approach.
 * Version:           1.3.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       service-request-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define constants for the plugin
define( 'SRP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SRP_PLUGIN_FILE', __FILE__ );
define( 'SRP_POST_TYPE', 'service_request' );
define( 'SRP_VIEWS_PATH', SRP_PLUGIN_PATH . 'includes/views/' );


// PSR-4 Style Autoloader (Simplified)
// This will automatically load classes from the includes/controllers, includes/models, and includes/helpers directories.
// Class names should follow the pattern SRP_Directory_ClassName (e.g., SRP_Controller_AdminController or SRP_Model_RequestModel)
// The autoloader will convert this to class-srp-directory-classname.php (e.g. class-srp-controller-admincontroller.php)
spl_autoload_register(function ($class_name) {
    // Check if the class belongs to our plugin
    if (strpos($class_name, 'SRP_') === 0) {
        // Remove the prefix
        $relative_class_name = str_replace('SRP_', '', $class_name);
        
        // Split into parts (e.g., Controller_AdminController becomes ['Controller', 'AdminController'])
        $parts = explode('_', $relative_class_name);
        
        // Determine the directory (controllers, models, helpers)
        $directory = strtolower($parts[0]) . 's'; // e.g., 'controller' becomes 'controllers'
        
        // Construct the rest of the filename
        // e.g., AdminController becomes admin-controller
        $file_name_parts = array_slice($parts, 1);
        $file_name_slug = strtolower(implode('-', $file_name_parts));

        // Construct the full path
        // Filename format: class-srp-mainpart-subpart.php (e.g., class-srp-admin-controller.php)
        // Or for models: class-srp-request-model.php
        $file_path_base = SRP_PLUGIN_PATH . 'includes/' . $directory . '/class-srp-';

        if (count($parts) > 1) { // e.g. SRP_Admin_Controller, SRP_Request_Model
            $filename = 'class-srp-' . strtolower(implode('-', $parts)) . '.php'; // class-srp-admin-controller.php
            // A more direct mapping if classes are SRP_AdminController, SRP_RequestModel
            // $filename_direct = 'class-srp-' . strtolower(preg_replace('/(?<!^)([A-Z])/', '-$1', $relative_class_name)) . '.php';

            // Try a more direct mapping first based on SRP_ClassName structure
            $direct_class_path_parts = explode('_', $relative_class_name); // e.g. [Request, Model]
            $direct_class_filename = 'class-srp-' . strtolower(implode('-', $direct_class_path_parts)) . '.php'; // class-srp-request-model.php
            
            $potential_paths = [
                SRP_PLUGIN_PATH . 'includes/controllers/' . $direct_class_filename,
                SRP_PLUGIN_PATH . 'includes/models/' . $direct_class_filename,
                SRP_PLUGIN_PATH . 'includes/helpers/' . $direct_class_filename,
            ];

        } else { // Should not happen with SRP_Directory_ClassName pattern
            return;
        }


        foreach ($potential_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }
});


/**
 * Initialize the plugin and its components.
 */
function srp_initialize_plugin() {
    // Instantiate controllers
    // Ensure class names match the autoloader logic (e.g., SRP_Plugin_Controller)
    $plugin_controller = new SRP_Plugin_Controller(); // Will look for includes/controllers/class-srp-plugin-controller.php
    $admin_controller = new SRP_Admin_Controller();   // Will look for includes/controllers/class-srp-admin-controller.php
    $frontend_controller = new SRP_Frontend_Controller(); // Will look for includes/controllers/class-srp-frontend-controller.php

    // Register hooks for plugin controller (activation, deactivation, init for CPTs/taxonomies)
    register_activation_hook( SRP_PLUGIN_FILE, [ $plugin_controller, 'activate' ] );
    register_deactivation_hook( SRP_PLUGIN_FILE, [ $plugin_controller, 'deactivate' ] );
    // For static uninstall method:
    // Make sure the class SRP_Plugin_Controller is loaded or explicitly require it before this line if not using plugins_loaded
    // require_once SRP_PLUGIN_PATH . 'includes/controllers/class-srp-plugin-controller.php';
    // register_uninstall_hook( SRP_PLUGIN_FILE, ['SRP_Plugin_Controller', 'uninstall'] );


    add_action( 'init', [ $plugin_controller, 'register_post_types_and_taxonomies' ] );
    add_action( 'init', [ $plugin_controller, 'load_textdomain' ] );


    // Register hooks for admin controller
    if ( is_admin() ) {
        add_action( 'add_meta_boxes', [ $admin_controller, 'add_meta_boxes' ] );
        add_action( 'save_post_' . SRP_POST_TYPE, [ $admin_controller, 'save_meta_data' ], 10, 1 );

        add_filter( 'manage_' . SRP_POST_TYPE . '_posts_columns', [ $admin_controller, 'set_custom_edit_columns' ] );
        add_action( 'manage_' . SRP_POST_TYPE . '_posts_custom_column', [ $admin_controller, 'render_custom_column_data' ], 10, 2 );
        add_filter( 'manage_edit-' . SRP_POST_TYPE . '_sortable_columns', [ $admin_controller, 'make_columns_sortable' ] );
        add_action( 'pre_get_posts', [ $admin_controller, 'handle_custom_column_orderby' ] );
    }

    // Register hooks for frontend controller (shortcodes)
    add_shortcode( 'service_request_form', [ $frontend_controller, 'render_service_request_form' ] );
    add_action( 'template_redirect', [ $frontend_controller, 'handle_form_submission' ] );

}
// Ensure controllers are loaded before 'plugins_loaded' if their methods are called directly by hooks like uninstall
// However, for instance methods, 'plugins_loaded' is fine.
add_action( 'plugins_loaded', 'srp_initialize_plugin' );

?>
