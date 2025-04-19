<?php
/**
 * Package Class for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * CWB_Package Class
 * 
 * Handles package data retrieval and management
 */
class CWB_Package {
    /**
     * Get packages by vehicle type
     * 
     * @param int $vehicle_type_id The ID of the vehicle type
     * @return array|null Array of packages or null on failure/invalid input
     */
    public static function get_by_vehicle_type( $vehicle_type_id ) {
        // Validate input
        if ( ! is_numeric( $vehicle_type_id ) || $vehicle_type_id <= 0 ) {
            error_log( 'Invalid vehicle type ID in CWB_Package::get_by_vehicle_type: ' . $vehicle_type_id );
            return null;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_packages';
        $vehicle_package_table = $wpdb->prefix . 'cwb_vehicle_packages';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.id, p.name, p.price, p.duration
                 FROM {$table_name} p
                 JOIN {$vehicle_package_table} vp ON p.id = vp.package_id
                 WHERE vp.vehicle_type_id = %d",
                $vehicle_type_id
            ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Package::get_by_vehicle_type: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }

    /**
     * Get services included in a package
     * 
     * @param int $package_id The ID of the package
     * @return array|null Array of services or null on failure/invalid input
     */
    public static function get_services( $package_id ) {
        // Validate input
        if ( ! is_numeric( $package_id ) || $package_id <= 0 ) {
            error_log( 'Invalid package ID in CWB_Package::get_services: ' . $package_id );
            return null;
        }

        global $wpdb;
        $service_table = $wpdb->prefix . 'cwb_services';
        $package_service_table = $wpdb->prefix . 'cwb_package_services';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.name
                 FROM {$service_table} s
                 JOIN {$package_service_table} ps ON s.id = ps.service_id
                 WHERE ps.package_id = %d",
                $package_id
            ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Package::get_services: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }
    
    /**
     * Get a single package by ID
     *
     * @param int $package_id The ID of the package to retrieve
     * @return array|null Package data or null if not found/error
     */
    public static function get_by_id( $package_id ) {
        // Validate input
        if ( ! is_numeric( $package_id ) || $package_id <= 0 ) {
            error_log( 'Invalid package ID in CWB_Package::get_by_id: ' . $package_id );
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_packages';
        
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $package_id ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Package::get_by_id: ' . $wpdb->last_error );
            return null;
        }
        
        return $result;
    }
}
