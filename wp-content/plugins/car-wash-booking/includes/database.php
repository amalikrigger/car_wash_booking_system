<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Insert a test booking into the database
 */
function cwbs_insert_booking( $data ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_bookings';

    $result = $wpdb->insert(
        $table_name,
        [
            'location_id' => $data['location_id'],
            'vehicle_type_id' => $data['vehicle_type_id'],
            'package_id' => $data['package_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'street' => $data['street'],
            'zip_code' => $data['zip_code'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'vehicle_make_model' => $data['vehicle_make_model'],
            'message' => $data['message'],
            'gratuity' => $data['gratuity'],
            'payment_type' => $data['payment_type'],
            'privacy_policy_accepted' => $data['privacy_policy_accepted'],
            'terms_accepted' => $data['terms_accepted'],
            'total_price' => $data['total_price'],
        ],
        [
            '%d', '%d', '%d', '%s', '%s', // Integers and strings
            '%s', '%s', '%s', '%s', '%s', '%s', // PII fields
            '%s', '%s', '%s', '%s', '%f', // Other fields
            '%s', '%d', '%d', '%f' // Boolean and float
        ]
    );

    return $result ? $wpdb->insert_id : false;
}

/**
 * Get locations for booking
 */
function cwbs_get_locations() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_locations';
    $results = $wpdb->get_results( "SELECT id, name FROM $table_name", ARRAY_A );

    return $results;
}

/**
 * Get vehicle types for booking
 */
function cwbs_get_vehicle_types() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_vehicle_types';
    $results = $wpdb->get_results( "SELECT id, name FROM $table_name", ARRAY_A );

    return $results;
}

/**
 * Get packages for booking
 */
function cwbs_get_packages() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_packages';
    $results = $wpdb->get_results( "SELECT id, name FROM $table_name", ARRAY_A );

    return $results;
}