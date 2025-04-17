<?php
/**
 * Core plugin class for Car Wash Booking System.
 */
class CWB_Plugin {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'car-wash-booking-system'; // Plugin slug
        $this->version = '1.0.0'; // Or get from plugin headers

        $this->load_dependencies();
        $this->loader = new CWB_Loader();
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
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-formatting.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-availability.php';
        include_once plugin_dir_path( __FILE__ ) . 'functions/functions-debug.php';
        // Include database setup
        include_once plugin_dir_path( __FILE__ ) . 'database/class-database-setup.php';
    }

    private function define_admin_hooks() {

    }

    private function define_public_hooks() {
        $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets' );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'cwbs-plugin-style', plugin_dir_url( __FILE__ ) . 'public/assets/css/styles.css' );
        wp_enqueue_style( 'cwbs-plugin-colors', plugin_dir_url( __FILE__ ) . 'public/assets/css/colors.css' );
        wp_enqueue_script( 'cwbs-booking-script', plugin_dir_url( __FILE__ ) . 'public/assets/js/cwb-booking.js', array( 'jquery' ), $this->version, true );
    }

    private function init_controllers() {
        new CWB_Shortcodes();
        new CWB_API_Endpoints();
    }

    public function run() {
        $this->loader->run(); // Run the loader to register hooks
    }

}