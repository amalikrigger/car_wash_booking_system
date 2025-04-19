<?php
/**
 * Location functions for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all locations with their field configurations
 *
 * @param bool $force_refresh Whether to force a refresh of the cached data
 * @return array|WP_Error Locations data or WP_Error on failure
 */
function cwb_get_locations_with_configs( $force_refresh = false ) {
    if ( $force_refresh ) {
        cwb_clear_locations_cache();
    }
    
    $cached_data = get_transient( 'cwb_locations_data' );

    if ( $cached_data === false ) {
        $location_fields_configs = [];
        $locations = CWB_Location::get_all();
        
        if ( null === $locations ) {
            return new WP_Error( 'location_error', 'Failed to retrieve locations' );
        }

        foreach ( $locations as $location ) {
            $config = CWB_Location::get_fields_config( $location['id'] );
            if ( null !== $config ) {
                $location_fields_configs[$location['id']] = $config;
            }
        }

        $cached_data = [
            'locations' => $locations,
            'location_fields_configs' => $location_fields_configs
        ];

        set_transient( 'cwb_locations_data', $cached_data, DAY_IN_SECONDS );
    }

    return $cached_data;
}

/**
 * Clear the locations cache
 */
function cwb_clear_locations_cache() {
    delete_transient( 'cwb_locations_data' );
}

/**
 * Get a single location with its field configuration
 *
 * @param int $location_id The ID of the location to retrieve
 * @return array|WP_Error Location data with configuration or WP_Error on failure
 */
function cwb_get_location_with_config( $location_id ) {
    if ( ! is_numeric( $location_id ) || $location_id <= 0 ) {
        return new WP_Error( 'invalid_id', 'Invalid location ID' );
    }
    
    $location = CWB_Location::get_by_id( $location_id );
    
    if ( null === $location ) {
        return new WP_Error( 'location_not_found', 'Location not found' );
    }
    
    $fields_config = CWB_Location::get_fields_config( $location_id );
    
    return [
        'location' => $location,
        'fields_config' => $fields_config
    ];
}

/**
 * Update location data and clear cache
 *
 * @param array $location_data The location data to update
 * @return bool|WP_Error True on success or WP_Error on failure
 */
function cwb_update_location( $location_data ) {
    // Validate input
    if ( ! is_array( $location_data ) || empty( $location_data ) ) {
        return new WP_Error( 'invalid_data', 'Invalid location data provided' );
    }
    
    if ( ! isset( $location_data['id'] ) || ! is_numeric( $location_data['id'] ) || $location_data['id'] <= 0 ) {
        return new WP_Error( 'invalid_id', 'Invalid location ID' );
    }
    
    // Update location in database
    global $wpdb;
    $table_name = $wpdb->prefix . 'cwb_locations';
    
    $update_data = array_intersect_key( $location_data, [
        'name' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'status' => '',
    ]);
    
    $update_result = $wpdb->update(
        $table_name,
        $update_data,
        [ 'id' => $location_data['id'] ]
    );
    
    if ( false === $update_result ) {
        return new WP_Error( 'update_failed', 'Failed to update location: ' . $wpdb->last_error );
    }
    
    // Clear cache
    cwb_clear_locations_cache();
    
    return true;
}
