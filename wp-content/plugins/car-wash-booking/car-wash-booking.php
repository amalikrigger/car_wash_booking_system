<?php
/*
Plugin Name: Car Wash Booking System
Description: A custom booking system for car wash services.
Version: 1.0
Author: GCOAT
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/database.php';

// Hook for activation to create tables
register_activation_hook(__FILE__, 'cwbs_create_tables');

/**
 * Function to create all required database tables
 */
function cwbs_create_tables() {
    global $wpdb;

    // Get charset and collation for the database
    $charset_collate = $wpdb->get_charset_collate();

    // Define all the tables
    $tables = [];

    // Table: wp_cwb_locations
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        street VARCHAR(255),
        zip_code VARCHAR(20),
        city VARCHAR(255),
        state VARCHAR(255),
        country VARCHAR(255),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8)
    ) $charset_collate;";

    // Table: wp_cwb_vehicle_types
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon_url TEXT
    ) $charset_collate;";

    // Table: wp_cwb_services
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT
    ) $charset_collate;";

    // Table: wp_cwb_packages
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT
    ) $charset_collate;";

    // Table: wp_cwb_add_ons
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_add_ons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2),
        package_id INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_service_locations
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_service_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL,
        location_id INT NOT NULL,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services(id) ON DELETE CASCADE,
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_service_vehicle_types
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_service_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL,
        vehicle_type_id INT NOT NULL,
        net_price DECIMAL(10, 2) NOT NULL,
        duration INT NOT NULL,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_package_locations
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_package_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        location_id INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE,
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_package_vehicle_types
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_package_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        vehicle_type_id INT NOT NULL,
        net_price DECIMAL(10, 2) NOT NULL,
        duration INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_location_vehicle_types
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_location_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT NOT NULL,
        vehicle_type_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_package_services
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_package_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        service_id INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Table: wp_cwb_bookings
    $tables[] = "CREATE TABLE {$wpdb->prefix}cwb_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT NOT NULL,
        vehicle_type_id INT NOT NULL,
        package_id INT NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        first_name VARCHAR(255),
        last_name VARCHAR(255),
        street VARCHAR(255),
        zip_code VARCHAR(20),
        city VARCHAR(255),
        state VARCHAR(255),
        country VARCHAR(255),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        email VARCHAR(255),
        phone_number VARCHAR(20),
        vehicle_make_model VARCHAR(255),
        message TEXT,
        gratuity DECIMAL(10, 2),
        payment_type VARCHAR(50),
        privacy_policy_accepted BOOLEAN,
        terms_accepted BOOLEAN,
        total_price DECIMAL(10, 2),
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Execute each table creation query using dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $table_sql) {
        dbDelta($table_sql);
    }
}
