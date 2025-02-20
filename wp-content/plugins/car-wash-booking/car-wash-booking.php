<?php
/*
Plugin Name: Car Wash Booking System
Description: A custom booking system for car wash services.
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
register_activation_hook(__FILE__, 'hwb_create_tables');

function hwb_create_tables() {
    include_once plugin_dir_path(__FILE__) . 'includes/database.php';
    hwb_initialize_database();
}

// Enqueue styles and scripts.
add_action('wp_enqueue_scripts', 'hwb_enqueue_assets');
function hwb_enqueue_assets() {
    wp_enqueue_style('hwb-style', plugin_dir_url(__FILE__) . 'assets/test.css');
    wp_enqueue_script('hwb-script', plugin_dir_url(__FILE__) . 'assets/main.js', array('jquery'), null, true);
}
?>
