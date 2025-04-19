<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CWB_Vehicle_Type {
    public static function get_all() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_vehicle_types';
        return $wpdb->get_results( "SELECT id, name, icon FROM $table_name", ARRAY_A );
    }

    public static function get_by_location( $location_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_vehicle_types';
        $location_vehicle_table = $wpdb->prefix . 'cwb_location_vehicle_types';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT vt.id, vt.name, vt.icon FROM {$table_name} vt
                 JOIN {$location_vehicle_table} lvt ON vt.id = lvt.vehicle_type_id
                 WHERE lvt.location_id = %d",
                $location_id
            ),
            ARRAY_A
        );
    }
}