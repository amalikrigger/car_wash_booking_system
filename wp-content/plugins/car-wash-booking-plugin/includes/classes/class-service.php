<?php
/**
 * Service Class for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * CWB_Service Class
 * 
 * Handles service data retrieval and management
 */
class CWB_Service {
    /**
     * Get add-on services available for a specific package
     * 
     * @param int $package_id The ID of the package
     * @return array|null Array of add-on services or null on failure/invalid input
     */
    public static function get_addons_by_package( $package_id ) {
        // Validate input
        if ( ! is_numeric( $package_id ) || $package_id <= 0 ) {
            error_log( 'Invalid package ID in CWB_Service::get_addons_by_package: ' . $package_id );
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_services';
        $package_addon_table = $wpdb->prefix . 'cwb_package_addons';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.id, s.name, s.price, s.duration
                 FROM {$table_name} s
                 JOIN {$package_addon_table} pa ON s.id = pa.service_id
                 WHERE pa.package_id = %d",
                $package_id
            ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Service::get_addons_by_package: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }

    /**
     * Get a single service by ID
     *
     * @param int $service_id The ID of the service to retrieve
     * @return array|null Service data or null if not found/error
     */
    public static function get_by_id( $service_id ) {
        // Validate input
        if ( ! is_numeric( $service_id ) || $service_id <= 0 ) {
            error_log( 'Invalid service ID in CWB_Service::get_by_id: ' . $service_id );
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_services';
        
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $service_id ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Service::get_by_id: ' . $wpdb->last_error );
            return null;
        }
        
        return $result;
    }

    /**
     * Get all services
     *
     * @return array|null Array of all services or null on failure
     */
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_services';
        
        $results = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Service::get_all: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }
}
