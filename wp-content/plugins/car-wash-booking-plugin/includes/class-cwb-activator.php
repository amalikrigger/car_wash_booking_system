<?php

/**
 * Fired during plugin activation.
 *
 * @link       https://your-plugin-website.com (Replace with your plugin website)
 * @since      1.0.0
 *
 * @package    Car_Wash_Booking_System
 * @subpackage Car_Wash_Booking_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Car_Wash_Booking_System
 * @subpackage Car_Wash_Booking_System/includes
 * @author     GCOAT (Replace with your name/author info)
 */
class CWB_Activator {

    /**
     * Plugin activation logic.
     *
     * This method is executed when the plugin is activated. It handles
     * database table creation and default data population.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Include database setup class (if not already included elsewhere)
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'database/class-database-setup.php';

        // Call the database initialization function
        CWB_Database_Setup::initialize_database(); // Use the class and method from class-database-setup.php
    }

}