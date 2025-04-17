<?php
/**
 * Uninstall Car Wash Booking System
 *
 * @package Car_Wash_Booking_System
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Define database table names (consistent with your database setup)
global $wpdb;
$tables_to_drop = [
    "{$wpdb->prefix}cwb_booking_statuses",
    "{$wpdb->prefix}cwb_payment_statuses",
    "{$wpdb->prefix}cwb_payment_methods",
    "{$wpdb->prefix}cwb_locations",
    "{$wpdb->prefix}cwb_location_fields_config",
    "{$wpdb->prefix}cwb_vehicle_types",
    "{$wpdb->prefix}cwb_location_vehicle_types",
    "{$wpdb->prefix}cwb_packages",
    "{$wpdb->prefix}cwb_package_pricing",
    "{$wpdb->prefix}cwb_services",
    "{$wpdb->prefix}cwb_service_pricing",
    "{$wpdb->prefix}cwb_package_services",
    "{$wpdb->prefix}cwb_package_addons",
    "{$wpdb->prefix}cwb_vehicle_packages",
    "{$wpdb->prefix}cwb_resources",
    "{$wpdb->prefix}cwb_resource_availability",
    "{$wpdb->prefix}cwb_customers",
    "{$wpdb->prefix}cwb_customer_types",
    "{$wpdb->prefix}cwb_bookings",
    "{$wpdb->prefix}cwb_booking_services",
    "{$wpdb->prefix}cwb_booking_addons",
    "{$wpdb->prefix}cwb_payments",
    "{$wpdb->prefix}cwb_booking_history",
    "{$wpdb->prefix}cwb_booking_sources",
    "{$wpdb->prefix}cwb_marketing_campaigns",
    "{$wpdb->prefix}cwb_weekday_time_ranges",
    "{$wpdb->prefix}cwb_date_time_ranges",
    "{$wpdb->prefix}cwb_excluded_dates",
    "{$wpdb->prefix}cwb_settings",
];

// Drop database tables
foreach ( $tables_to_drop as $table_name ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}
