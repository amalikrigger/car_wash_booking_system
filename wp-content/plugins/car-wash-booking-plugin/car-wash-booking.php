<?php
/*
Plugin Name: Car Wash Booking Plugin
Description: A custom booking plugin for car wash services.
Version: 1.0
Author: GCOAT
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function activate_car_wash_booking_system() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-cwb-activator.php';
    CWB_Activator::activate();
}

function deactivate_car_wash_booking_system() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-cwb-deactivator.php';
    CWB_Deactivator::deactivate();
}
register_activation_hook(__FILE__, array( 'CWB_Activator', 'activate' ));
register_deactivation_hook(__FILE__, array( 'CWB_Deactivator', 'deactivate' ));

require_once plugin_dir_path( __FILE__ ) . 'includes/class-cwb.php';

function run_car_wash_booking_system() {
    $plugin = new CWB_Plugin();
    $plugin->run();
}

run_car_wash_booking_system();