<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CWB_Package {
    public static function get_by_vehicle_type( $vehicle_type_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_packages';
        $vehicle_package_table = $wpdb->prefix . 'cwb_vehicle_packages';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.id, p.name, p.price, p.duration
                 FROM {$table_name} p
                 JOIN {$vehicle_package_table} vp ON p.id = vp.package_id
                 WHERE vp.vehicle_type_id = %d",
                $vehicle_type_id
            ),
            ARRAY_A
        );
    }

    public static function get_services( $package_id ) {
        global $wpdb;
        $service_table = $wpdb->prefix . 'cwb_services';
        $package_service_table = $wpdb->prefix . 'cwb_package_services';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.name
                 FROM {$service_table} s
                 JOIN {$package_service_table} ps ON s.id = ps.service_id
                 WHERE ps.package_id = %d",
                $package_id
            ),
            ARRAY_A
        );
    }
}