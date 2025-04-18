<?php
/**
 * Core plugin class for Car Wash Booking System.
 */
class CWB_Plugin {

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $public;

    public function __construct() {
        $this->plugin_name = 'car-wash-booking-system';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->loader = new CWB_Loader();
        $this->public = new CWB_Public( $this->get_plugin_name(), $this->get_version() );
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_controllers();
    }

    private function load_dependencies() {
        // Include all necessary files
        include_once plugin_dir_path( __FILE__ ) . 'class-cwb-loader.php';
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
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-locations.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-formatting.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-availability.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-debug.php';
        // Include database setup
        include_once plugin_dir_path( __FILE__ ) . 'database/class-database-setup.php';
        // Include public class
        include_once plugin_dir_path( __FILE__ ) . '../public/class-cwb-public.php';
    }

    private function define_admin_hooks() {
        // Admin hooks will be defined here later
    }

    private function define_public_hooks() {
        $this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_assets' );
    }

    private function init_controllers() {
        new CWB_Shortcodes();
        new CWB_API_Endpoints();
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}