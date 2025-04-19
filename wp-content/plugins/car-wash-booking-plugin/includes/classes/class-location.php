<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CWB_Location {
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_locations';
        return $wpdb->get_results( "SELECT id, name, address FROM $table_name", ARRAY_A );
    }

    public static function get_fields_config( $location_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_location_fields_config';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE location_id = %d", $location_id ), ARRAY_A );
    }
}