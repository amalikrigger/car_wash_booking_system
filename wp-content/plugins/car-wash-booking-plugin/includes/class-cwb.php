<?php
/**
 * Core plugin class for Car Wash Booking System.
 */
class CWB_Plugin {

    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'car-wash-booking-system'; // Plugin slug
        $this->version = '1.0.0'; // Or get from plugin headers

        $this->load_dependencies();
        $this->init_controllers();
    }

    private function load_dependencies() {
        // Include all necessary files
        include_once plugin_dir_path( __FILE__ ) . 'shortcodes/class-shortcodes.php';
        include_once plugin_dir_path( __FILE__ ) . 'api/class-api-endpoints.php';
        include_once plugin_dir_path( __FILE__ ) . 'class-cwb-activator.php';
        include_once plugin_dir_path( __FILE__ ) . 'class-cwb-deactivator.php';
        // Include model classes
        include_once plugin_dir_path( __FILE__ ) . 'classes/class-location.php';
        include_once plugin_dir_path( __FILE__ ) . 'classes/class-vehicle-type.php';
        include_once plugin_dir_path( __FILE__ ) . 'classes/class-package.php';
        include_once plugin_dir_path( __FILE__ ) . 'classes/class-service.php';
        include_once plugin_dir_path( __FILE__ ) . 'classes/class-booking.php';
        // Include controllers
        include_once plugin_dir_path( __FILE__ ) . 'controllers/class-booking-widget-controller.php';
        // Include functions
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-formatting.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-availability.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-debug.php';
        // Include database setup
        include_once plugin_dir_path( __FILE__ ) . 'database/class-database-setup.php';
    }

    private function init_controllers() {
        new CWB_Shortcodes();
        new CWB_API_Endpoints();
    }

    public function run() {
        // Any plugin-wide initialization or actions can go here
    }
}