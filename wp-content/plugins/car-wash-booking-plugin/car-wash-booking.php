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

// Include dependencies.
include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/database.php';
include_once plugin_dir_path(__FILE__) . 'includes/api.php';

// Register activation hook.
register_activation_hook(__FILE__, 'cwb_create_tables');

function cwb_create_tables() {
    include_once plugin_dir_path(__FILE__) . 'includes/database.php';
    cwb_initialize_database();
}

// Enqueue styles and scripts.
add_action('wp_enqueue_scripts', 'cwb_enqueue_assets');
function cwb_enqueue_assets() {
}
?>