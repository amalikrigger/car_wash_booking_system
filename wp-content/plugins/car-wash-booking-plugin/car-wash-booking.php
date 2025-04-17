<?php
/*
Plugin Name: Car Wash Booking Plugin
Description: A custom booking plugin for car wash services.
Version: 1.0
Author: GCOAT
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-cwb.php';

// Register activation hook.
register_activation_hook(__FILE__, array( 'CWB_Activator', 'activate' ));
register_deactivation_hook(__FILE__, array( 'CWB_Deactivator', 'deactivate' ));

// Enqueue styles and scripts.
add_action('wp_enqueue_scripts', 'cwb_enqueue_assets');
function cwb_enqueue_assets() {
}
/**
 * Run the plugin.
 */
function run_car_wash_booking_system() {
    $plugin = new CWB_Plugin();
    $plugin->run();
}
run_car_wash_booking_system();