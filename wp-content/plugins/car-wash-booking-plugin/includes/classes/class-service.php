<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CWB_Service {

    /**
     * Get add-on services for a package.
     *
     * @param int $package_id Package ID.
     * @return array Array of add-on services.
     */
    public static function get_addons_by_package( $package_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cwb_services';
        $package_addon_table = $wpdb->prefix . 'cwb_package_addons';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.id, s.name, s.price, s.duration
                 FROM {$table_name} s
                 JOIN {$package_addon_table} pa ON s.id = pa.service_id
                 WHERE pa.package_id = %d",
                $package_id
            ),
            ARRAY_A
        );
    }

    // You can add more methods here later

}