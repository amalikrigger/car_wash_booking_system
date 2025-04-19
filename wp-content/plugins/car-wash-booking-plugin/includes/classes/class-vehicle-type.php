<?php
/**
 * Vehicle Type Class for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * CWB_Vehicle_Type Class
 * 
 * Handles vehicle type data retrieval and management
 */
class CWB_Vehicle_Type {
    /**
     * Get all vehicle types
     * 
     * @return array|null Array of vehicle types or null on failure
     */
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_vehicle_types';
        
        $results = $wpdb->get_results( "SELECT id, name, icon FROM {$table_name}", ARRAY_A );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Vehicle_Type::get_all: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }

    /**
     * Get vehicle types by location
     * 
     * @param int $location_id The ID of the location
     * @return array|null Array of vehicle types or null on failure/invalid input
     */
    public static function get_by_location( $location_id ) {
        // Validate input
        if ( ! is_numeric( $location_id ) || $location_id <= 0 ) {
            error_log( 'Invalid location ID in CWB_Vehicle_Type::get_by_location: ' . $location_id );
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_vehicle_types';
        $location_vehicle_table = $wpdb->prefix . 'cwb_location_vehicle_types';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT vt.id, vt.name, vt.icon FROM {$table_name} vt
                 JOIN {$location_vehicle_table} lvt ON vt.id = lvt.vehicle_type_id
                 WHERE lvt.location_id = %d",
                $location_id
            ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Vehicle_Type::get_by_location: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }
    
    /**
     * Get a single vehicle type by ID
     *
     * @param int $vehicle_type_id The ID of the vehicle type to retrieve
     * @return array|null Vehicle type data or null if not found/error
     */
    public static function get_by_id( $vehicle_type_id ) {
        // Validate input
        if ( ! is_numeric( $vehicle_type_id ) || $vehicle_type_id <= 0 ) {
            error_log( 'Invalid vehicle type ID in CWB_Vehicle_Type::get_by_id: ' . $vehicle_type_id );
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_vehicle_types';
        
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $vehicle_type_id ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Vehicle_Type::get_by_id: ' . $wpdb->last_error );
            return null;
        }
        
        return $result;
    }
}
