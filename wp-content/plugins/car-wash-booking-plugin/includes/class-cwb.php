<?php
/**
 * The main plugin class for Car Wash Booking System
 *
 * This class defines all core functionality of the plugin.
 *
 * @package Car_Wash_Booking
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class CWB_Plugin {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since  1.0.0
     * @access protected
     * @var    CWB_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The public-facing functionality of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    CWB_Public    $public    Handles public-facing functionality.
     */
    protected $public;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks.
     *
     * @since 1.0.0
     */
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

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * - CWB_Loader - Orchestrates the hooks of the plugin.
     * - Model and controller classes.
     * - Helper functions.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function load_dependencies() {
        // Core classes
        $this->require_file( 'class-cwb-loader.php' );
        $this->require_file( 'shortcodes/class-shortcodes.php' );
        $this->require_file( 'api/class-api-endpoints.php' );
        $this->require_file( 'class-cwb-activator.php' );
        $this->require_file( 'class-cwb-deactivator.php' );
        
        // Model classes
        $this->require_file( 'classes/class-location.php' );
        $this->require_file( 'classes/class-vehicle-type.php' );
        $this->require_file( 'classes/class-package.php' );
        $this->require_file( 'classes/class-service.php' );
        $this->require_file( 'classes/class-booking.php' );
        
        // Controllers
        $this->require_file( 'controllers/class-booking-widget-controller.php' );
        
        // Helper functions
        $this->require_file( 'functions/functions-locations.php' );
        $this->require_file( 'functions/functions-formatting.php' );
        $this->require_file( 'functions/functions-availability.php' );
        $this->require_file( 'functions/functions-debug.php' );
        
        // Database setup
        $this->require_file( 'database/class-database-setup.php' );
        
        // Public class
        $this->require_file( '../public/class-cwb-public.php' );
    }

    /**
     * Safely require a file with error handling
     *
     * @since  1.0.0
     * @access private
     * @param  string $file_path Path to the file relative to the includes directory
     * @return void
     */
    private function require_file( $file_path ) {
        $full_path = plugin_dir_path( __FILE__ ) . $file_path;
        
        if ( file_exists( $full_path ) ) {
            require_once $full_path;
        } else {
            // Log error for missing file
            error_log( "Car Wash Booking System Error: Required file not found: {$full_path}" );
        }
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function define_admin_hooks() {
        // Admin hooks will be defined here later
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function define_public_hooks() {
        $this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_assets' );
    }

    /**
     * Initialize all controllers
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function init_controllers() {
        new CWB_Shortcodes();
        new CWB_API_Endpoints();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @return string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @return string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
