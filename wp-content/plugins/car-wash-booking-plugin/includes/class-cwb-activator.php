<?php
/**
 * Plugin Activation Handler
 *
 * This class handles all the functionality that needs to run when
 * the plugin is activated.
 *
 * @package Car_Wash_Booking
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CWB_Activator
 *
 * Handles plugin activation tasks like database setup
 * 
 * @since 1.0.0
 */
class CWB_Activator {
    
    /**
     * Run activation tasks
     *
     * Sets up the database tables required by the plugin
     * 
     * @since 1.0.0
     * @return void
     */
    public static function activate() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database/class-database-setup.php';
        CWB_Database_Setup::initialize_database();
        
        // Set version in options
        update_option( 'cwb_version', '1.0.0' );
        
        // Flush rewrite rules to enable any custom endpoints
        flush_rewrite_rules();
    }
}
