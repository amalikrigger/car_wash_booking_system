<?php
/**
 * Plugin Deactivation Handler
 *
 * This class handles all the functionality that needs to run when
 * the plugin is deactivated.
 *
 * @package Car_Wash_Booking
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CWB_Deactivator
 *
 * Handles plugin deactivation tasks
 * 
 * @since 1.0.0
 */
class CWB_Deactivator {
    
    /**
     * Run deactivation tasks
     *
     * Performs cleanup tasks when the plugin is deactivated
     * 
     * @since 1.0.0
     * @return void
     */
    public static function deactivate() {
        // Clear any scheduled events
        wp_clear_scheduled_hook( 'cwb_daily_maintenance' );
        
        // Clear cached data
        delete_transient( 'cwb_locations_data' );
        
        // Flush rewrite rules to remove plugin endpoints
        flush_rewrite_rules();
    }
}
