<?php
/**
 * Location Class for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * CWB_Location Class
 * 
 * Handles location data retrieval and management
 */
class CWB_Location {
    /**
     * Get all locations from the database
     * 
     * @return array|null Array of locations or null on failure
     */
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_locations';
        
        $results = $wpdb->get_results( "SELECT id, name, address FROM $table_name", ARRAY_A );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Location::get_all: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }

    /**
     * Get field configuration for a specific location
     * 
     * @param int $location_id The ID of the location
     * @return array|null Array of field configurations or null on failure/invalid input
     */
    public static function get_fields_config( $location_id ) {
        // Validate location_id
        if ( ! is_numeric( $location_id ) || $location_id <= 0 ) {
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_location_fields_config';
        
        $results = $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM $table_name WHERE location_id = %d", $location_id ), 
            ARRAY_A 
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Location::get_fields_config: ' . $wpdb->last_error );
            return null;
        }
        
        return $results;
    }
    
    /**
     * Get a single location by ID
     *
     * @param int $location_id The ID of the location to retrieve
     * @return array|null Location data or null if not found/error
     */
    public static function get_by_id( $location_id ) {
        if ( ! is_numeric( $location_id ) || $location_id <= 0 ) {
            return null;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_locations';
        
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $location_id ),
            ARRAY_A
        );
        
        if ( $wpdb->last_error ) {
            error_log( 'Database error in CWB_Location::get_by_id: ' . $wpdb->last_error );
            return null;
        }
        
        return $result;
    }
}
